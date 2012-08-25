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
    private $NameTMainSearches = 'TotalMain'; //total queries for main
    private $NameTDeltaSearches = 'TotalDelta'; //total queries for delta
    private $NameTStatsSearches = 'TotalStats'; //total queries for stats
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
            if (isset($Results['MainSearch']['words'])) {
                $this->InsertStats($Results['MainSearch']['words']);
            }
            if (isset($Results[$this->NameTKeywords]['matches'])) {
                $SingleKeywords = $this->SingleKeywords($Results[$this->NameTKeywords], $this->Settings['Admin']->LimitTopKeywords); //single keywords (apple, vanilla, cool)
                foreach ($SingleKeywords as $Row) {
                    $KeywordsArray [] = $Row->keywords;
                }
                if (isset($KeywordsArray)) {
                    $Module = new KeywordsCloudModule($KeywordsArray);
                    $Sender->AddModule($Module);
                }
            }

            if (isset($Results[$this->NameTSearches]['matches'])) {
                $TopSearches = $this->FullSearches($Results[$this->NameTSearches], $this->Settings['Admin']->LimitTopSearches);
                $Module = new TopSearchesModule($TopSearches);
                $Sender->AddModule($Module);
            }

            if (isset($Results[$this->NameRSearches]['matches'])) {
                $RelatedSearches = $this->FullSearches($Results[$this->NameRSearches], $this->Settings['Admin']->LimitRelatedSearches); // full search ("vanllia is cool")
                $Module = new RelatedSearchesModule($RelatedSearches);
                $Sender->AddModule($Module);
            }
        } else if (isset($Results[$this->NameTMainSearches]['matches'])) {
            SaveToConfig('Plugin.SphinxSearch.IndexerMainTotal', $Results[$this->NameTMainSearches]['total_found']);
        } else if (isset($Results[$this->NameTDeltaSearches]['matches'])) {
            SaveToConfig('Plugin.SphinxSearch.IndexerDeltaTotal', $Results[$this->NameTDeltaSearches]['total_found']);
        } else if (isset($Results[$this->NameTStatsSearches]['matches'])) {
            SaveToConfig('Plugin.SphinxSearch.IndexerStatsTotal', $Results[$this->NameTStatsSearches]['total_found']);
        }
    }

    public function AddQuery($Sender, $Options = FALSE) {
        if ($Sender->ControllerName == 'searchcontroller') {
            //use this: $Options['Landing'] to indicate we are on the main search page with all the advanced features and such
            if ($this->Settings['Admin']->LimitTopKeywords > 0 && ($Options['Landing'] == TRUE)) {
                //Get the top keywords
                $this->SphinxClient->ResetFilters();
                $this->SphinxClient->ResetGroupBy();
                $this->SphinxClient->SetFilter('mode', array(1)); //only related keywords
                //$this->SphinxClient->SetFilterRange(); //@todo put in times for date/time
                $this->SphinxClient->SetGroupBy('keywords_crc', SPH_GROUPBY_ATTR, '@count desc');
                $this->SphinxClient->SetLimits(0, $this->Settings['Admin']->LimitTopKeywords);

                $Query = ''; //blank query

                $QueryIndex = $this->SphinxClient->AddQuery($Query, $Index = SS_INDEX_STATS, 'Top Keywords');

                $this->Queries[] = array(
                    'Name' => $this->NameTKeywords,
                    'Index' => $QueryIndex,
                );
            }
            if ($this->Settings['Admin']->LimitTopSearches > 0 && ($Options['Landing'] == TRUE)) {
                //Get the top seaches
                $this->SphinxClient->ResetFilters();
                $this->SphinxClient->ResetGroupBy();
                $this->SphinxClient->SetFilter('mode', array(2));//this is set in the sph_stats database ....mode = 2 when full sphinx search is made - not individual words
                //$this->SphinxClient->SetFilterRange(); //@todo put in times for date/time
                $this->SphinxClient->SetGroupBy('keywords_crc', SPH_GROUPBY_ATTR, '@count desc');
                $this->SphinxClient->SetLimits(0,$this->Settings['Admin']->LimitTopSearches);

                $Query = ''; //blank query

                $QueryIndex = $this->SphinxClient->AddQuery($Query, $Index = SS_INDEX_STATS, 'Top Searches');

                $this->Queries[] = array(
                    'Name' => $this->NameTSearches,
                    'Index' => $QueryIndex,
                );
            }

            if ($this->Settings['Admin']->LimitRelatedSearches > 0 && ($Options['Landing'] == FALSE)) {
                //Get related searches
                $this->SphinxClient->ResetFilters();
                $this->SphinxClient->ResetGroupBy();
                $this->SphinxClient->SetFilter('mode', array(2)); //select only full searches (i.e return "vanilla forums search" versus "vanilla", "forums", "search")
                //$this->SphinxClient->SetFilterRange(); //@todo put in times for date/time
                $this->SphinxClient->SetGroupBy('keywords_crc', SPH_GROUPBY_ATTR, '@weight desc');
                $this->SphinxClient->SetLimits(1, $this->Settings['Admin']->LimitRelatedSearches); //notice the '1' offset. This is so don't get exact search match

                $Sanitized = $this->ValidateInputs(); //get the submitted query
                $Query = $this->OperatorOrSearch($Sanitized['Query']); // vanilla | forums | search

                //echo $Query; die;
                $QueryIndex = $this->SphinxClient->AddQuery('@(keywords) ' . $Query.' ', $Index = SS_INDEX_STATS, 'Related Searches');

                $this->Queries[] = array(
                    'Name' => $this->NameRSearches,
                    'Index' => $QueryIndex,
                );
            }
        } else if ($Sender->ControllerName == 'plugincontroller') {
            if (GetValue('Index', $Options)) { //pass this manually inside of the sphinxsearch plugin index controller
                /**
                 * Use this for only the Admin side of things for now...this gets the total number of queries
                 *
                 * This is a very potential EXPENSIVE query to make since it bindly matches all the documents to get the total amount indexed
                 */
                $Prefix = $this->Settings['Install']->Prefix;
                $Index = GetValue('Index', $Options);
                $this->SphinxClient->ResetFilters();
                $this->SphinxClient->ResetGroupBy();
                $this->SphinxClient->SetLimits(0, 1); //no limits


                $QueryIndex = $this->SphinxClient->AddQuery('', $Prefix . $Index); //blank query..match all documents basically

                switch ($Index) { //get index name ....use this later in 'update' to determine which statistic to update the total count for
                    case SS_STATS_INDEX:
                        $IndexName = $this->NameTStatsSearches;
                        break;
                    case SS_DELTA_INDEX:
                        $IndexName = $this->NameTDeltaSearches;
                        break;
                    case SS_MAIN_INDEX:
                    default:
                        $IndexName = $this->NameTMainSearches;
                        break;
                }

                $this->Queries[] = array(
                    'Name' => $IndexName,
                    'Index' => $QueryIndex,
                );
            }
        }else
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
        if (!GetValue('matches', $Result))
            return array(); //no matches
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
            return array();
    }
    /**
     * @todo put in the count of each search in here to display on the widget
     * @param type $Result
     * @param type $Limit
     * @return boolean
     */
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