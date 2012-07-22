<?php

/**
 * Gets the top xx amount of search keywords
 *
 * Uses the sphinx stats index
 *
 * Also puts the query search words into the mysql database
 */
class WidgetStats extends Widgets implements SplObserver {

    private $NameTKeywords = 'TopKeywords'; //name of the search
    private $NameTSearches = 'TopSearches';
    private $NameRSearches = 'RelatedSearches';
    private $Queries = array(); //keep track of query offset

    public function __construct($SphinxClient, $Settings) {
        parent::__construct($SphinxClient, $Settings);
    }

    public function Update(SplSubject $Subject) {
        $Status = $Subject->getStatus(); //retrieve status array
        $Results = $Status['Results'];
        $Sender = $Status['Sender'];
        //store stats ...perform this after the search since we grab the indivdual search words
        //that sphinx has search against. This saves us processing time of each and seperating other things
        if ($Sender->ControllerName == 'searchcontroller') {
            if (isset($Results['MainSearch']['words'])){
                $this->InsertStats($Results['MainSearch']['words']);
            }

            if (isset($Results[$this->NameTKeywords])) {
                $SingleKeywords = $this->SingleKeywords($Results[$this->NameTKeywords], $this->Settings['Admin']->LimitTopKeywords); //single keywords (apple, vanilla, cool)
                foreach ($SingleKeywords as $Row) {
                    $KeywordsArray [] = $Row->keywords;
                }
                $Module = new KeywordsCloudModule($KeywordsArray);
                $Sender->AddModule($Module);
            }

            if (isset($Results[$this->NameTSearches])) {
                $TopSearches = $this->FullSearches($Results[$this->NameTSearches], $this->Settings['Admin']->LimitTopSearches);
                $Module = new TopSearchesModule($TopSearches);
                $Sender->AddModule($Module);
            }

            if (isset($Results[$this->NameRSearches])) {
                $RelatedSearches = $this->FullSearches($Results[$this->NameRSearches], $this->Settings['Admin']->LimitRelatedSearches); // full search ("vanllia is cool")
                $Module = new RelatedSearchesModule($RelatedSearches);
                $Sender->AddModule($Module);
            }
        }
    }

    public function AddQuery($Sender, $Options = FALSE) {
        if ($Sender->ControllerName == 'searchcontroller') {
            if ($this->Settings['Admin']->LimitTopKeywords > 0 && ($Options['Landing'] == TRUE)) {
                //Get the top keywords
                $this->SphinxClient->ResetFilters();
                $this->SphinxClient->ResetGroupBy();
                $this->SphinxClient->SetFilter('mode', array(1)); //only related keywords
                //$this->SphinxClient->SetFilterRange(); //@todo put in times for date/time
                $this->SphinxClient->SetGroupBy('keywords_crc', SPH_GROUPBY_ATTR, '@count desc');

                $Query = ''; //blank query

                $QueryIndex = $this->SphinxClient->AddQuery($Query, $Index = SS_INDEX_STATS, 'Top Keywords');

                $this->Queries[] = array(
                    'Name' => $this->NameTKeywords,
                    'Index' => $QueryIndex,
                    'Highlight' => FALSE,
                    'IgnoreFirst' => FALSE,
                );
            }
            if ($this->Settings['Admin']->LimitTopSearches > 0 && ($Options['Landing'] == TRUE)) {
                //Get the top seaches
                $this->SphinxClient->ResetFilters();
                $this->SphinxClient->ResetGroupBy();
                $this->SphinxClient->SetFilter('mode', array(2));
                //$this->SphinxClient->SetFilterRange(); //@todo put in times for date/time
                $this->SphinxClient->SetGroupBy('keywords_crc', SPH_GROUPBY_ATTR, '@count desc');

                $Query = ''; //blank query

                $QueryIndex = $this->SphinxClient->AddQuery($Query, $Index = SS_INDEX_STATS, 'Top Searches');

                $this->Queries[] = array(
                    'Name' => $this->NameTSearches,
                    'Index' => $QueryIndex,
                    'Highlight' => FALSE,
                    'IgnoreFirst' => FALSE,
                );
            }

            if ($this->Settings['Admin']->LimitRelatedSearches > 0 && ($Options['Landing'] == FALSE)) {
                //Get related searches
                $this->SphinxClient->ResetFilters();
                $this->SphinxClient->ResetGroupBy();
                $this->SphinxClient->SetFilter('mode', array(2)); //select only full searches (i.e return "vanilla forums search" versus "vanilla", "forums", "search")
                //$this->SphinxClient->SetFilterRange(); //@todo put in times for date/time
                $this->SphinxClient->SetGroupBy('keywords_crc', SPH_GROUPBY_ATTR, '@weight desc');

                $Sanitized = $this->ValidateInputs(); //get the submitted query
                $Query = $this->OperatorOrSearch($Sanitized['Query']); // vanilla | forums | search


                $QueryIndex = $this->SphinxClient->AddQuery('@(keywords) ' . $Query, $Index = SS_INDEX_STATS, 'Related Searches');

                $this->Queries[] = array(
                    'Name' => $this->NameRSearches,
                    'Index' => $QueryIndex,
                    'Highlight' => FALSE,
                    'IgnoreFirst' => FALSE,
                );
            }
        }
        else
            return FALSE;


        return $this->Queries;
    }

    /**
     *
     * @param array $Words
     * @param string $Query as returned from sphinx. If morhpolgy is enabled, some words may look funky (i.e engine->engin)
     */
    public function InsertStats($Words) {
        //print_r($Words); die;
        if (sizeof($Words) == 0)
            return;
        $Query = '';
        //@todo roll this loop out to minimize insert statements
        foreach ($Words as $Word => $Info) {
            $Query.=$Word . ' ';
            Gdn::SQL()
                    ->Insert('sph_stats', array('keywords' => $Word, 'mode' => 1, 'date_added' => Gdn_Format::ToDateTime())); //mode == 1 means plain word
        }
        Gdn::SQL()
                ->Insert('sph_stats', array('keywords' => $Query, 'mode' => 2, 'date_added' => Gdn_Format::ToDateTime())); //mode == 2 means hwole query
    }

    public function SingleKeywords($Result, $Limit) {
        $Ids = array_keys($Result['matches']); //get all of the keyword IDs
        if (sizeof($Ids) != 0) {
            $SingleKeywords = Gdn::SQL()
                    ->Distinct(TRUE)
                    ->Select('keywords')
                    ->From('sph_stats')
                    ->WhereIn('id', $Ids)
                    ->Where('Mode', 1) //get only single keywords
                    ->Limit($Limit)
                    ->Get()
                    ->Result()
            ;
            return $SingleKeywords;
        }
        else
            return FALSE;
    }

    public function FullSearches($Result, $Limit) {
        if (!isset($Result['matches']))
            return;
        $Ids = array_keys($Result['matches']); //get all of the keyword IDs
        if (sizeof($Ids) != 0) {
            $FullSearches = Gdn::SQL()
                    ->Distinct(TRUE)
                    ->Select('keywords')
                    ->From('sph_stats')
                    ->WhereIn('id', $Ids)
                    ->Where('Mode', 2) //get the whole querie
                    ->Limit($Limit)
                    ->Get()
                    ->Result()
            ;

            return $FullSearches;
        }
        else
            return FALSE;
    }

    /**
     * Returns the top XX amount of keywords that are indexed in the main index
     *
     * reads the  words form 'word_freq' text file
     * @todo implmement this latter with --build_dict which will be in future releases
     */
    public function PopularIndexedKeywords() {
        $WordFreqFile = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'word_freq.txt';
        if (!is_readable($WordFreqFile)) {
            echo ("Unable to read word_freq file at: $WordFreqFile"); //@todo put better error handling in here

            return;
        }
        $Words = file_get_contents($WordFreqFile);       //get text from file
        $Words = explode(' ', $Words);
        foreach ($Words as $Word) {
            echo $Word . ' ,';
        }
    }

}