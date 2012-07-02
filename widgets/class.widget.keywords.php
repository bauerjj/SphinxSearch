<?php

/**
 * Gets the top xx amount of search keywords
 *
 * Also puts the query search words into the mysql database
 */
class WidgetKeywords extends Widgets implements SplObserver {

    private $NameKeywords = 'Popular_Keywords'; //name of the search
    private $NameSearches = 'Popular_Searches';
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
            if (isset($Results['Main']['words']))
                $this->InsertStats($Results['Main']['words']);

            $SingleKeywords = $this->PopularSingleKeywords($Results[$this->NameKeywords]);
        $FullKeywords = $this->PopularFullKeywords($Results[$this->NameSearches]);

        $Sender->SetData('SingleKeywords', $this->ToString($SingleKeywords, FALSE));
        $Sender->SetData('FullKeywords', $this->ToString($FullKeywords, TRUE));
        }

    }

    public function AddQuery($Sender, $Options = FALSE) {
        if ($Sender->ControllerName == 'searchcontroller') {
            //Get the top keywords
            $this->SphinxClient->ResetFilters();
            $this->SphinxClient->ResetGroupBy();
            //$this->SphinxClient->SetFilterRange(); //@todo put in times for date/time
            $this->SphinxClient->SetGroupBy('keywords_crc', SPH_GROUPBY_ATTR, '@count desc');

            $Query = ''; //blank query

            $QueryIndex = $this->SphinxClient->AddQuery($Query, $Index = SPHINX_INDEX_STATS, 'Popular Search - Top Keywords');

            $this->Queries[] = array(
                'Name' => $this->NameKeywords,
                'Index' => $QueryIndex,
                'Highlight' => FALSE,
                'IgnoreFirst' => FALSE,
            );

            //Get related searches
            $this->SphinxClient->ResetFilters();
            $this->SphinxClient->ResetGroupBy();
            $this->SphinxClient->SetFilter('mode', array(2)); //select only full searches (i.e return "vanilla forums search" versus "vanilla", "forums", "search")
            //$this->SphinxClient->SetFilterRange(); //@todo put in times for date/time
            $this->SphinxClient->SetGroupBy('keywords_crc', SPH_GROUPBY_ATTR, '@weight desc');

            $Sanitized = $this->ValidateInputs(); //get the submitted query
            $Query = $this->OperatorOrSearch($Sanitized['Query']); // vanilla | forums | search


            $QueryIndex = $this->SphinxClient->AddQuery('@(keywords) ' . $Query, $Index = SPHINX_INDEX_STATS, 'Popular Search - Related Searches');

            $this->Queries[] = array(
                'Name' => $this->NameSearches,
                'Index' => $QueryIndex,
                'Highlight' => FALSE,
                'IgnoreFirst' => FALSE,
            );
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

    public function PopularSingleKeywords($Result) {
        $Ids = array_keys($Result['matches']); //get all of the keyword IDs
        $SingleKeywords = Gdn::SQL()
                ->Distinct(TRUE)
                ->Select('keywords')
                ->From('sph_stats')
                ->WhereIn('id', $Ids)
                ->Where('Mode', 1) //get only single keywords
                ->Limit($this->Settings['Admin']->LimitRelatedKeywords)
                ->Get()
                ->Result()
        ;
        return $SingleKeywords;
    }

    public function PopularFullKeywords($Result) {
        if (!isset($Result['matches']))
            return;
        $Ids = array_keys($Result['matches']); //get all of the keyword IDs
        $FullKeywords = Gdn::SQL()
                ->Distinct(TRUE)
                ->Select('keywords')
                ->From('sph_stats')
                ->WhereIn('id', $Ids)
                ->Where('Mode', 2) //get the whole querie
                ->Limit($this->Settings['Admin']->LimitRelatedSearches)
                ->Get()
                ->Result()
        ;

        return $FullKeywords;
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

    public function ToString($Keywords, $Multi = FALSE) {
        $String = '';
        if (sizeof($Keywords) == 0) {
            return; //return an empty string if no results
        }
        // $String .= '<h4 id="Header_Related">Related Discussions</h4>';
        ob_start();
        ?>
        <div id="Search_Container">
            <div id="RelatedThreads">
                <?php
                foreach ($Keywords as $Row):
                    if ($Multi)
                        $QueryString = 'search/results?q=' . str_replace(' ', '+', $Row->keywords);
                    else
                        $QueryString = 'search/results?q=' . ($Row->keywords);
                    ?>
                    <h4 class="DiscussionTitle"><?php echo Anchor($Row->keywords, $QueryString) ?></h4>
                <?php endforeach ?>
                <div style="clear: both"></div>
            </div>
        </div>
        <?php
        $String .= ob_get_contents();
        @ob_end_clean();

        return $String;
    }

}