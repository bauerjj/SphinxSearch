<?php

class WidgetMain extends Widgets implements SplObserver {

    private $Sanitized = ''; //
    private $Queries = array(); //keep track of query offset
    private $Name = 'MainSearch'; //also used in the widget hitbox

    public function __construct($SphinxClient, $Settings) {
        parent::__construct($SphinxClient, $Settings);
    }

    public function Update(SplSubject $Subject) {
        $Status = $Subject->getStatus(); //retrieve status array
        $Results = $Status['Results'];
        $Sender = $Status['Sender'];

        if (isset($Results[$this->Name])) {
            if ($this->Settings['Admin']->MainSearchEnable) {
                if (isset($Results[$this->Name]['matches'])) {
                    $Sanitized = $this->ValidateInputs(); //get offset
                    $Matches = $this->GetSQLData($Sanitized['ResultFormat'], $Results[$this->Name]['matches']);
                    $Matches = $this->HighLightResults($Matches, $Results[$this->Name]['query'], $this->Settings['Admin']->BuildExcerptsTitleEnable, $this->Settings['Admin']->BuildExcerptsBodyEnable);
                    $Results[$this->Name]['matches'] = $Matches;
                }
                $Sender->SetData($this->Name, $Results[$this->Name]); //still set the data even if no total found
                if (!isset($Results[$this->Name]['total_found']))
                    $Total = 0;
                else
                    $Total = $Results[$this->Name]['total_found'];

                if($Total > $this->Settings['Admin']->MaxMatches)
                    $Total = $this->Settings['Admin']->MaxMatches; //total_found is applied BEFORE setLimits and maxmatches take affect. We want to limit
                    //pagination results to the max matches that sphinx will return...or else some pages will have a blank result set
                $this->BuildPager($Sender, $Total);
            }
        }
    }

    public function AddQuery($Sender, $Options = FALSE) {
        if ($Sender->ControllerName == 'searchcontroller' && $Options['Landing'] == FALSE) {
            $this->MainSearch();
            return $this->Queries;
        }
    }

    private function BuildPager($Sender, $Total) {
        $Sanitized = $this->ValidateInputs(); //get offset
        $GETString = '?' . Gdn_Url::QueryString() . '&tar=srch'; //use this to providea link back to search - be sure to append the '&tar=srch' to tell to load the main search page
        $GETString = str_replace('p=search&', 'search?', $GETString);
        //echo $GETString; die;
        $Limit = $this->Settings['Admin']->LimitResultsPage;
        $Offset = (($Sanitized['Offset'] - 1) * $Limit); //limit per page

        $Pos = strpos($GETString, '&pg='.$_GET['pg']);
        if (!$Pos == FALSE) {
            //$Url = substr($GETString, 0, $Pos); //strip the page number if it exists
            $Url = str_replace('&pg='.GetIncomingValue('pg'), '', $GETString); //strip the page number if it exists
            $Url = str_replace('&tar=srch', '', $Url); //don't want to load adv search page when clicking page numbers
        }
        else
            $Url = str_replace('&tar=srch', '', $GETString); //don't want to load adv search page when clicking page numbers

        $PagerFactory = new Gdn_PagerFactory();
        $Sender->Pager = $PagerFactory->GetPager('Pager', $Sender);
        $Sender->Pager->MoreCode = '>';
        $Sender->Pager->LessCode = '<';
        $Sender->Pager->ClientID = 'Pager';
        $Sender->Pager->Configure($Offset, $Limit, $Total, $Url . '&pg=%1$s');
        //echo $Url; die;

        $Sender->SetData('GETString', $GETString);
    }

    private function MainSearch() {
        $this->Sanitized = $this->ValidateInputs();
        $this->Search($this->Sanitized);
    }

    private function Search($Sanitized) {
        $this->SphinxClient->ResetFilters();
        $this->SphinxClient->ResetGroupBy();
        $SubQuery = '';


        $Limit = $this->Settings['Admin']->LimitResultsPage;
        $Offset = (($Sanitized['Offset'] - 1) * $Limit); //limit per page
        if ($Offset < 0)
            $Offset = 0;

        //Make sure results respect category permissions depending on user performing search
        $Permissions = Gdn::Session()->GetPermissions(); // Get user permissions
        $Permissions = $Permissions['Vanilla.Discussions.View']; // Only care about 'viewing' permissions
        $this->SphinxClient->SetFilter(SS_ATTR_CATPERMID, $Permissions);

        if (!empty($Sanitized['ForumList'])) {       //filter Forum categories
            $Categories = $Sanitized['ForumList'];
            if (!in_array(0, $Categories)) {  //If this is TRUE, than user selected to search in "All" categories...no filtering then requried
                if ($Sanitized['SearchChildren'])
                    $Categories = $this->GetCategories($Categories); //get children IDs
                $this->SphinxClient->SetFilter(SS_ATTR_CATID, $Categories); //no children required, just get whatever was posted
            }
        }
        if (!empty($Sanitized['TagList'])) {
            if (Gdn::Structure()->TableExists('TagDiscussion')) { //check to see if tagging plugin is enabled
                $TagIDs = $this->GetTagIDs($Sanitized['TagList']);
                if (sizeof($TagIDs) > 0) //maybe someone input an invalid tag string that does not match any in the database...
                    $this->SphinxClient->SetFilter('TagID', $TagIDs);
            }
        }
        if (!empty($Sanitized['MemberList'])) {      //filter by member
            // $String = $this->OperatorOrSearch($Sanitized['MemberList']);
            // $SubQuery .= $this->FieldSearch($String, array(SS_FIELD_USERNAME));
        }
        if (!empty($Sanitized['WithReplies'])) {      //only return threads that have comments
            $this->SphinxClient->SetFilterRange(SS_ATTR_COUNTCOMENTS, 1, 1, TRUE); //exclude documents with exactly 0 comments (the first topic post counts as 1)
        }
        if (isset($Sanitized['Date']) && $Sanitized['Date'] != 'All') { //don't filter by date if the whole query string is gone
            $Time = $this->SetTime($Sanitized['Date']);
            $this->SphinxClient->SetFilterRange(SS_ATTR_DOCDATEINSERTED, $Time, now());
        }
        $Query = $this->SetRankAndQuery($Sanitized['Match'], $Sanitized['Query']); //depending on selected match, need to format the query string to comply with the extended syntax
        $this->SetSortMode($Sanitized['Order']);

        $this->SphinxClient->SetLimits($Offset, $this->Settings['Admin']->LimitResultsPage, $MaxMatches = 0); //limit the results pageination

        if ($Sanitized['Match'] != 'Extended') { //extended query do not add these
            if ($Sanitized['TitlesOnly'] == 1) {
                $Query = $this->SphinxClient->EscapeString($Query); //Escapes characters that are treated as special operators by the query language parser (i.e @title => /@/title). Returns an escaped string.
                $MainSearch = $this->FieldSearch($Query, array(SS_FIELD_TITLE));
            } else {
                $MainSearch = $this->FieldSearch($Query, array(SS_FIELD_TITLE, SS_FIELD_BODY)); //perform the search
            }
        }
        else
            $MainSearch = $Query;

        $Query = ' ' . $SubQuery . ' ' . $MainSearch;

        //echo $Query; die;

        $QueryIndex = $this->SphinxClient->AddQuery($Query.' ', SS_INDEX_DIST, $this->Name);
        $this->Queries[] = array(
            'Name' => $this->Name,
            'Index' => $QueryIndex,
        );
    }

    private function SetTime($Time) {
        switch ($Time) {
            case 'ThisWeek':
                return strtotime("-1 week");
                break;
            case 'ThisMonth':
                return strtotime("-1 month");
                break;
            case 'ThisYear':
                return strtotime("-1 year");
                break;
            default:
            case 'All':
                return now();
                break;
        }
    }

    private function GetTagIDs($TagList) {
        $SQL = Gdn::SQL();
        $IDs = $SQL->Select('TagID')->From('Tag')->WhereIn('Name', $TagList)->Get()->ResultArray();
        return ConsolidateArrayValuesByKey($IDs, 'TagID'); //make a single array of the IDs for sphinx to filter on
    }

    /**
     * GEts a list of child categories from a given single categoery ID
     * @param int $CategoryIDs
     * @return array
     */
    private function GetCategories($CategoryIDs) {
        $Return = $CategoryIDs; //holds all the CatIDs to eventually search in
        $CategoryData = CategoryModel::Categories();
        if (is_object($CategoryData))
            $CategoryData = (array) $CategoryData;
        else if (!is_array($CategoryData))
            $CategoryData = array();

        // Respect category permissions (remove categories that the user shouldn't see).
        $SafeCategoryData = array();
        foreach ($CategoryData as $CategoryID => $Category) {
            if ($Category['CategoryID'] < 0 || !$Category['PermsDiscussionsView'])
                continue;
            $SafeCategoryData[$CategoryID] = $Category;
        }

        foreach ($CategoryIDs as $SearchID) {
            foreach ($SafeCategoryData as $CatID => $CatInfo) {
                if ($CatInfo['ParentCategoryID'] == $SearchID || in_array($CatInfo['ParentCategoryID'], $Return))
                    $Return [] = $CatID;
            }
        }
        return $Return;
    }

    private function SetSortMode($Order) {
        switch ($Order) {
            case 'Relevance':  //relevance
                $this->SphinxClient->SetSortMode(SPH_SORT_RELEVANCE, '');
                break;
            case 'MostRecent': //most recent
                $this->SphinxClient->SetSortMode(SPH_SORT_ATTR_DESC, SS_ATTR_DOCDATEINSERTED);
                break;
            case 'MostViews': //most replies
                $this->SphinxClient->SetSortMode(SPH_SORT_ATTR_DESC, SS_ATTR_COUNTVIEWS);
                break;
            case 'MostReplies': //most views
                $this->SphinxClient->SetSortMode(SPH_SORT_ATTR_DESC, SS_ATTR_COUNTCOMENTS); //relevance only..no direct field
                break;
        }
    }

    private function SetRankAndQuery($Rank, $Query) {
        switch ($Rank) {
            case 'Any': //Any
            default:
                $this->SphinxClient->SetRankingMode(SPH_RANK_MATCHANY); //match any keyword
                $Return = $this->OperatorOrSearch($Query);
                break;
            case 'All': //All
                $this->SphinxClient->SetRankingMode(SPH_RANK_PROXIMITY); //requires perfect match
                $Return = '"' . $Query . '"'; //add quotes to designate a phrase match is required
                break;
            case 'Extended': //Extended
                $this->SphinxClient->SetRankingMode(SPH_RANK_PROXIMITY_BM25); //boolean operators
                $Return = $Query; //do not alter the query...allow the extended syntax to be inserted by user
                break;
        }
        return $Return;
    }

}