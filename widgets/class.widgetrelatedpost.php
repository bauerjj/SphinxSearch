<?php

/**
 * Displays recent threads either above or below the currently viewed topic
 *
 * The handler to hook onto are the following:
 * - DiscussionController_BeforeDiscussion_Handler
 * - DiscussionController_AfterDiscussion_Handler
 *
 */
class SphinxWidgetRelatedPost extends SphinxWidgets {

    public $Results = array();

    private $Queries = array();

    public function __construct($SphinxClient) {
        parent::__construct($SphinxClient);
    }

    public function AddQuery($Options = FALSE) {
        $Thread = $Options['Query']; //get the discussion name (thread topic) to search against
        $Query = $this->RelatedThreads($Thread, $this->Settings['Admin']->LimitRelatedPost);
        $QueryIndex = $this->SphinxClient->AddQuery($Query, $Index = SPHINX_INDEX_DIST, 'Related Post');
        $this->Queries = array(
            'Name' => 'Related_Post',
            'Index' => $QueryIndex,
            'Highlight' => FALSE,
            'IgnoreFirst' => FALSE,
        );
        return $this->Queries;
    }

    public function ToString($Results) {
        $String = '';
        if ($Results == 0) {
            return; //return an empty string if no results
        }
       // $String .= '<h4 id="Header_Related">Related Discussions</h4>';
        $String .= WriteTable($Results);

        return $String;
    }


}

