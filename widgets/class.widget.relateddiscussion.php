<?php

/**
 * Displays recent threads either above or below the currently viewed topic
 *
 * The handler to hook onto are the following:
 * - DiscussionController_BeforeDiscussion_Handler
 * - DiscussionController_AfterDiscussion_Handler
 *
 */
class WidgetRelatedDiscussion extends Widgets implements SplObserver {

    private $Queries = array();

    public function __construct($SphinxClient, $Settings) {
        parent::__construct($SphinxClient, $Settings);
    }

    public function Update(SplSubject $Subject) {
        $Status = $Subject->getStatus(); //retrieve status array
        $Results = $Status['Results'];
        $Sender = $Status['Sender'];

        if ($Sender->ControllerName == 'discussioncontroller') {
            //this is here since 'Update' will be called in 'base_render_before' as well as any handlers
            //that wish to use the results somewhere on the page. This line makes sure to not display the
            //related threads on each 'discussioncontroller' page. Base_redner_before -> Update will pass
            //the results in an assocated result with the name being the key. Handlers should NOT do this, but
            //rather pass in the result directly

            if (!isset($Results['Related_Discussion'])) {
                echo $this->ToString($Results);
            }
        }
    }

    public function AddQuery($Sender, $Options = FALSE) {
        if ($Sender->ControllerName == 'discussioncontroller') {
            $Thread = $Sender->Discussion->Name; //get the discussion name (thread topic) to search against
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
            $this->Queries[] = array(
                'Name' => 'Related_Discussion',
                'Index' => $QueryIndex,
                'Highlight' => FALSE,
                'IgnoreFirst' => TRUE,
            );
            return $this->Queries;
        }
        else
            return FALSE;
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