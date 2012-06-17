<?php

/**
 * Displays recent threads either above or below the currently viewed topic
 *
 * The handler to hook onto are the following:
 * - DiscussionController_BeforeDiscussion_Handler
 * - DiscussionController_AfterDiscussion_Handler
 *
 */
class SphinxWidgetRelatedDiscussion extends SphinxWidgets {

    private $Queries = array();

    public function __construct($SphinxClient) {
        parent::__construct($SphinxClient);
    }

    public function AddQuery($Options = FALSE) {
        $Thread = $Options['DiscussionName']; //get the discussion name (thread topic) to search against
        $Query = $this->RelatedThreads($Thread, $this->Settings['Admin']->LimitRelatedDiscussion, array(
            SPHINX_ATTR_STR_DISCUSSIONNAME,
            SPHINX_ATTR_STR_CATNAME,
            SPHINX_ATTR_STR_CATULCODE,
            SPHINX_ATTR_TSTAMP_DISCUSSIONDATEINSERTED,
            SPHINX_ATTR_UINT_DISCUSSIONVIEWS,
            SPHINX_ATTR_UINT_DISCUSSIONCOMENTS,
            SPHINX_ATTR_UINT_DISCUSSIONID,
            SPHINX_ATTR_UINT_CATID,
            SPHINX_FIELD_STR_USERNAME,
            SPHINX_ATTR_UINT_USERID,
                ));
        $QueryIndex = $this->SphinxClient->AddQuery($Query, $Index = SPHINX_INDEX_DIST, 'Main Related');
        $this->Queries = array(
            'Name' => 'Related_Discussion',
            'Index' => $QueryIndex,
            'Highlight' => FALSE,
            'IgnoreFirst' => TRUE,
        );
        return $this->Queries;
    }

    public function ToString($Results) {
        $String = '';
        if ($Results == 0) {
            return; //return an empty string if no results
        }
        $String .= '<h4 id="Header_Related">Related Discussions</h4>';
        $String .= WriteTable($Results);

        return $String;
    }

}