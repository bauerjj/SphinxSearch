<?php

class WidgetMain extends Widgets implements SplObserver {

    private $Sanitized = ''; //
    private $Queries = array(); //keep track of query offset

    public function __construct($SphinxClient, $Settings) {
        parent::__construct($SphinxClient, $Settings);
    }

    public function Update(SplSubject $Subject) {

    }

    public function AddQuery($Sender, $Options = FALSE) {
        if ($Sender->ControllerName == 'searchcontroller') {
            $this->MainSearch();
            return $this->Queries;
        }
        else
            return FALSE;
    }

    public function Handler($Sender) {
        //do nothing
    }

    private function MainSearch() {
        $this->Sanitized = $this->ValidateInputs();
        $this->Search($this->Sanitized);
    }

    private function Search($Sanitized) {
        $this->SphinxClient->ResetFilters();
        $this->SphinxClient->ResetGroupBy();
        $SubQuery = '';

        if (!empty($Sanitized['ForumList'])) {       //filster Forum categories
            if (!in_array(0, $Sanitized['ForumList']))  //If this is TRUE, than user selected to search in "All" categories...no filtering then requried
                $this->SphinxClient->SetFilter('CatID', $Sanitized['ForumList']);
        }
        if (!empty($Sanitized['MemberList'])) {      //filter by member
            $String = $this->OperatorOrSearch($Sanitized['MemberList']);
            $SubQuery .='@(UserName) ' . $String;
        }
        $Query = $this->SetRankAndQuery($Sanitized['Match'], $Sanitized['Query']); //depending on selected match, need to format the query string to comply with the extended syntax
        $this->SetSortMode($Sanitized['Order']);

//        if($Sanitized['Query'] =='*' || $Sanitized['Query'] == '')
        $this->SphinxClient->SetLimits($Offset = 0, $this->Settings['Admin']->LimitResultsPage, $MaxMatches = 0);
        if ($Sanitized['TitlesOnly'] == 1) {
            $this->SphinxClient->SetGroupDistinct('DiscussionName'); //only want one unique thread title
            $this->SphinxClient->SetGrouping('DiscussionName', SPH_GROUPBY_ATTR);
            $MainSearch = $this->_FieldSearch(
                    $Query, array(
                SPHINX_FIELD_STR_DISCUSSIONNAME), $Multiple = FALSE); //perform the search
        } else {

            //$this->SphinxClient->SphinxSetGroupDistinct('discussionname'); //only want one unique username
            //$this->SphinxClient->SphinxSetGrouping('discussionname', SPH_GROUPBY_ATTR);
            //$this->SphinxClient->SphinxSetFilter('TableID', array(1));
            $MainSearch = $this->FieldSearch(
                    $Query, array(
                SPHINX_FIELD_STR_DISCUSSIONNAME,
                SPHINX_FIELD_STR_COMMENTBODY), $Multiple = TRUE); //perform the search
            //echo $MainSearch; echo $SubQuery;
        }
        $Query = ' ' . $SubQuery . ' ' . $MainSearch;
        $QueryIndex = $this->SphinxClient->AddQuery($Query, $index = SPHINX_INDEX_DIST, 'Main Search');
        $this->Queries[] = array(
            'Name' => 'Main',
            'Index' => $QueryIndex,
            'Highlight' => TRUE,
            'IgnoreFirst' => FALSE,
        );
    }

    private function SetSortMode($Order) {
        switch ($Order) {
            case 0:  //relevance
                $this->SphinxClient->SetSortMode(SPH_SORT_RELEVANCE, '');
                break;
            case 1: //most recent
                $this->SphinxClient->SetSortMode(SPH_SORT_STR_DESC, 'CommentDateInserted');
                break;
            case 2: //most replies
                $this->SphinxClient->SetSortMode(SPH_SORT_STR_DESC, 'DiscussionCountComments');
                break;
            case 3: //most views
                $this->SphinxClient->SetSortMode(SPH_SORT_STR_DESC, 'DiscussionCountViews'); //relevance only..no direct field
                break;
        }
    }

    private function SetRankAndQuery($Rank, $Query) {
        switch ($Rank) {
            case 0: //Any
            default:
                $this->SphinxClient->SetRankingMode(SPH_RANK_MATCHANY); //match any keyword
                $Return = $this->OperatorOrSearch($Query);
                break;
            case 1: //All
                $this->SphinxClient->SetRankingMode(SPH_RANK_PROXIMITY); //requires perfect match
                $Return = '"' . $Query . '"'; //add quotes to designate a phrase match is required
                break;
            case 2: //Extended
                $this->SphinxClient->SetRankingMode(SPH_RANK_PROXIMITY_BM25); //boolean operators
                $Return = $Query; //do not alter the query...allow the extended syntax to be inserted by user
                break;
        }
        return $Return;
    }

}