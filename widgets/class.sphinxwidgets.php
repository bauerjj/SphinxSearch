<?php

abstract class SphinxWidgets{

    protected $SphinxClient; //sphinxAPI

    protected $Settings;



    abstract function AddQuery($Options);

    public function __construct($SphinxClient) {
        $this->SphinxClient = $SphinxClient;


        $Setup = SphinxFactory::BuildSettings();
        $this->Settings = $Setup->GetAllSettings();
        $this->Connect();

    }

    private function Connect(){
        $this->SphinxClient->SetServer($this->Settings['Install']->Host, (int)$this->Settings['Install']->Port); //must start searchd in order to connect...or else conection will be refused
        $this->SphinxClient->SetMaxQueryTime($this->Settings['Admin']->MaxQueryTime); // Sets maximum search query time, in milliseconds. Default valus is 0 which means "do not limit".
        $this->SphinxClient->SetMatchMode(SPH_MATCH_EXTENDED2); //use this since using boolean operators
    }

    /**
     *
     * Highlights results based on the given string to match results to
     *
     * Cannot use the distributed index for  'BuildExcerpts'...must specify
     * one of its local parts. Since both of the local indexes use the same
     * tokenizing, exceptions, wordforms, N-grams etc, we can just select one
     *
     * @param array $Result
     * @param string $Query
     * @param inte $Limit
     * @param int $Offset
     * @return type
     */
    public function HighLightResults($Result, $Query, $Limit = 0, $Offset = 0) {
        if (!isset($Result['Records']))
            return FALSE;               //no records
        $Records = $Result['Records'];
        $CommentArray = array();  //comment body
        foreach ($Records as $Record) {
            $CommentArray[] = $Record->{SPHINX_FIELD_STR_COMMENTBODY};
        }
        $HighLightedComment = $this->SphinxClient->BuildExcerpts(
                $CommentArray, SPHINX_INDEX_MAIN, $Query, $options = $this->Settings->BuildExcerpts
        );
        if ($this->Settings->HighLightTitles) {
            $DiscussionArray = array(); //discussion titles
            foreach ($Records as $Record) {
                $DiscussionArray[] = $Record->{SPHINX_ATTR_STR_DISCUSSIONNAME};
            }
            $HighLightedDiscussion = $this->SphinxClient->BuildExcerpts(
                    $DiscussionArray, SPHINX_INDEX_MAIN, $Query, $options = array(
                'before_match' => $this->Settings->BuildExcerpts['before_match'],
                'after_match' => $this->Settings->BuildExcerpts['after_match'])
            ); //don't put any restrictions on anything else here except the opening/closing tags
        }

        $Offset = 0;
        foreach ($Records as $Record) {
            $Record->{SPHINX_FIELD_STR_COMMENTBODY} = $HighLightedComment[$Offset];
            if ($this->Settings->HighLightTitles)
                $Record->{SPHINX_ATTR_STR_DISCUSSIONNAME} = $HighLightedDiscussion[$Offset];
            $Offset++;
        }
        $Result['Records'] = $Records;

        //print_r($Result);
        return $Result;
    }

    /**
     *
     * @param string $text
     * @param int $NumMatches number of words to match in the $text
     *
     * Quorum matching operator introduces a kind of fuzzy matching.
     * It will only match those documents that pass a given threshold of given words.
     * The example above ("the world is a wonderful place"/3) will match all documents
     * that have at least 3 of the 6 specified words.
     */
    protected function QuorumSearch($Query, $NumMatches) {
        return '"' . $Query . '/' . $NumMatches . '"';
    }

    protected function PhraseSearch($Query) {
        return '"' . $Query . '"';
    }

    protected function FieldSearch($Query, $Fields = array(), $Multiple = FALSE) {
        if ($Multiple == FALSE)
            return '@(' . $Fields[0] . ') ' . $Query; //single field
        else {
            $Params = '';
            foreach ($Fields as $Field) {
                if ($Params == '')
                    $Params .= $Field;
                else
                    $Params .= ',' . $Field;
            }
            return '@(' . $Params . ') ' . $Query;
        }
    }

    protected function OperatorOrSearch($Query) {
        $Input = '';
        $Return = '';
        if (is_array($Query)) {
            foreach ($Query as $Word)
                $Input .= $Word . ' ';
            $Input = trim($Input);
            $Query = $Input;
        }
        $QueryString = explode(' ', $Query);
        foreach ($QueryString as $Word) {   //add the boolean OR operator (|)
            if (!is_string($Word) || $Word == '')
                continue;
            if ($Return != '')
                $Return .= ' | ' . $Word;
            else
                $Return = $Word;
        }
        return $Return;
    }

    protected function AllSearch($Query) {
        return '@* ' . $Query;
    }


    protected function _SearchUserName($Input) {
        $SearchField = 'UserName';

        $this->SphinxSearchModel->SphinxReset();
        $this->SphinxSearchModel->SphinxSetMatch(SPH_MATCH_EXTENDED2); //use this since using boolean operators
        $this->SphinxSearchModel->SetSelect('UserName'); //only need the username
        $this->SphinxSearchModel->SphinxSetSort(SPH_SORT_RELEVANCE);
        $this->SphinxSearchModel->SphinxSetGroupDistinct($SearchField); //only want one unique username
        $this->SphinxSearchModel->SphinxSetGrouping($SearchField, SPH_GROUPBY_ATTR);
        $this->SphinxSearchModel->SphinxSetLimits($Offset = 0, $Limit = 15); //limit to 15 autocompletes
        $Result = $this->SphinxSearchModel->SphinxSearch('@(UserName) ' . $Input, $index = 'vanilla'); //perform the search
        return $this->_JsonEncode($Result['Records'], 'username');
    }

    /**
     *
     * @param string $InputQuery the filtered input query.
     */
    protected function RelatedThreads($InputQuery, $ResultLimit, $Select = array(SPHINX_ATTR_STR_DISCUSSIONNAME, SPHINX_ATTR_UINT_DISCUSSIONID)) {
        $this->SphinxClient->ResetFilters();
        $this->SphinxClient->ResetGroupBy();

        $Final = '';
        foreach($Select as $Filter){
            $Final.= $Filter .',';
        }
        $Final = substr($Final,0,strlen($Final) -1); //remove trailing comma

        //$this->SphinxClient->SetSelect($Final); //grab discussion title and ID or other stuff
        $this->SphinxClient->SetSortMode(SPH_SORT_RELEVANCE);
        $this->SphinxClient->SetRankingMode(SPH_RANK_WORDCOUNT);
        $this->SphinxClient->SetLimits($Offset = 0, $Limit = $ResultLimit); //limit to 15 autocompletes
        $Query = $this->FieldSearch(
                $this->OperatorOrSearch($InputQuery), array(
            SPHINX_FIELD_STR_DISCUSSIONNAME
                ), $Multiples = FALSE);

        return $Query;
    }


    protected function PerformSearch($search, $index) {
        $result = $this->SphinxClient->Query($search, $index);   //here we go
        if ($result === false) {
            echo '<b style ="color: red">Query failed: ' .$this->SphinxClient->GetLastError() . '.\n';
        } else {
            if ($this->SphinxClient->GetLastWarning()) {
                echo '<b style ="color: red">WARNING: ' . $this->SphinxClient->GetLastWarning() . ' </b>';
            }
        }
        //Parse the returned results
        if ($result['total_found'] == 0) //check if no results were returned
            return array('records' => 0, 'NumRows' => $result['total_found']);
        else {
            $result_docs = array();
            foreach ($result['matches'] as $doc => $docinfo) {
                $result_docs[] = Gdn_Format::ArrayAsObject($docinfo['attrs']); //convert to object to be consistenst with returned MYSQL data
            }

            if (!isset($result['words'])) //using 'SPH_MATCH_ALL will' not have this defined
                return array('Records' => $result_docs, 'NumRows' => $result['total_found'],'Time' => $result['time'], 'Words' => false); //set it to false
            else
                return array('Records' => $result_docs, 'NumRows' => $result['total_found'], 'Time' => $result['time'],'Words' => $result['words']);
        }
    }

    /**
     * validate the POST from the main search fields
     *
     * @return array result of filtered inputs
     */
    protected function ValidateInputs() {
        $MemberList = array();
        $TagList = array();
        $ForumList = array();
        $Query = '';

        $QueryIn = trim(filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING)); //probably overkill
        $QueryIn = explode(' ', $QueryIn);
        foreach ($QueryIn as $Word) {    //get rid of any white spaces inbetween words
            if (!is_string($Word) || $Word == '')
                continue;
            if ($Query == '')
                $Query = $Word;
            else
                $Query .= ' ' . $Word;
        }

        $TitlesOnly = (filter_input(INPUT_GET, 'titles', FILTER_SANITIZE_NUMBER_INT) == 1 ? 1 : 0); //checkbox - bool
        $Match = filter_input(INPUT_GET, 'match', FILTER_SANITIZE_NUMBER_INT);
        $Child = (filter_input(INPUT_GET, 'child', FILTER_SANITIZE_NUMBER_INT) == 1 ? 1 : 0); //checkbox - bool
        if (isset($_GET['forums'])) {
            foreach ($_GET['forums'] as $Forum) {
                if (is_numeric($Forum)) //check if int
                    $ForumList[] = $Forum;
            }
        }
        $Date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING);
        $Order = filter_input(INPUT_GET, 'or', FILTER_SANITIZE_NUMBER_INT); //bool

        $Members = filter_input(INPUT_GET, 'mem', FILTER_SANITIZE_STRING); //list seperated by comma
        if (!empty($Members)) {
            $Members = explode(',', $Members);
            foreach ($Members as $Member) {
                if (!is_string($Member) || $Member == '' || !str_word_count($Member, 0))
                    continue;
                $MemberList[] = trim($Member);
            }
        }

        $Tags = filter_input(INPUT_GET, 'tag', FILTER_SANITIZE_STRING); //list seperated by comma
        if (!empty($Tags)) {
            $Tags = explode(',', $Tags);
            foreach ($Tags as $Tag) {
                if (!is_string($Tag) || $Tag == '' || !str_word_count($Tag, 0))
                    continue;
                $TagList[] = trim($Tag);
            }
        }
        $SanitizedFormat = filter_input(INPUT_GET, 'res', FILTER_SANITIZE_NUMBER_INT); // 0 = thread , 1 = post

        return array(
            'Query' => $Query,
            'TitlesOnly' => $TitlesOnly,
            'Match' => $Match,
            'ForumList' => $ForumList,
            'SearchChildren' => $Child,
            'Date' => $Date,
            'MemberList' => $MemberList,
            'Order' => $Order,
            'TagList' => $TagList,
            'Order' => $Order,
            'ResultFormat' => $SanitizedFormat,
        );
    }

    /**
     * Returns a JSON encoded string
     *
     * @param array $Result Sphinx result array
     * @param string $Field field to populate json return string from
     */
    protected function JsonEncode($Result, $Field) {
        $Return = '[';
        foreach ($Result as $Row) {
            if ($Return != '[')
                $Return .= ',';
            $Return .= json_encode(array('value' => GetValue($Field, $Row)));
        }
        $Return .= ']';
        return $Return;
    }


}