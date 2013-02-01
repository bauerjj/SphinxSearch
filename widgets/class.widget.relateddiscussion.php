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
    private $Name = 'RelatedDiscussion';

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
            //@todo this only works right now because only use one event handler - FIX THIS

            if (!isset($Results[$this->Name])) { // the '!' is important here
                if ($this->Settings['Admin']->LimitRelatedThreadsBottomDiscussion > 0) { //put it on the bottom
                    $Matches = $this->GetSQLData($this->Settings['Admin']->RelatedThreadsBottomDiscussionFormat, GetValue('matches', $Results));
                    $String = $this->ToString($Matches);
                    echo $String;
                }
            } else {
                if ($this->Settings['Admin']->LimitRelatedThreadsSidebarDiscussion > 0) { //put it on the sidebar
                    $Matches = $this->GetSQLData('simple', GetValue('matches', $Results[$this->Name]));
                    $Module = new RelatedDiscussionModule($this->ToString($Matches, TRUE));
                    $Sender->AddModule($Module);
                }

                //first time seeing this after 'base_render_before'...must set the data on the view
                $Sender->SetData($this->Name, $Results[$this->Name]);
            }
        }
    }

    public function AddQuery($Sender, $Options = FALSE) {
        if ($Sender->ControllerName == 'discussioncontroller') {
            $this->SphinxClient->ResetFilters();
            $this->SphinxClient->ResetGroupBy();
            $this->SphinxClient->SetSortMode(SPH_SORT_RELEVANCE);
            $this->SphinxClient->SetRankingMode(SPH_RANK_WORDCOUNT);
            $this->SphinxClient->SetLimits(1, $this->Settings['Admin']->LimitRelatedThreadsSidebarDiscussion); //notice the offset of '1'. This is so don't select current viewed discussion as related
            $Thread = $this->SphinxClient->EscapeString($Sender->Discussion->Name); //get the discussion name (thread topic) to search against
            $Query = $this->FieldSearch($this->OperatorOrSearch($Thread), array(SS_FIELD_TITLE));

            //Make sure results respect category permissions depending on user performing search
            $Permissions = Gdn::Session()->GetPermissions(); // Get user permissions
            $Permissions = $Permissions['Vanilla.Discussions.View']; // Only care about 'viewing' permissions
            $this->SphinxClient->SetFilter(SS_ATTR_CATPERMID, $Permissions);


            $QueryIndex = $this->SphinxClient->AddQuery($Query . ' ', $Index = SS_INDEX_DIST, $this->Name);

            $this->Queries[] = array(
                'Name' => $this->Name,
                'Index' => $QueryIndex,
            );
            return $this->Queries;
        }
        else
            return FALSE;
    }

    /**
     *
     *
     *
     */
    private function ToString($Results, $Panel = FALSE) {
        $String = '';
        if (sizeof($Results) == 0) {
            return; //return an empty string if no results
        }
        if (!$Panel) { //writing to the bottom of the discussion
            ob_start();
            ?>

            <div id="RelatedDiscussion" class="Box"><!--   Important to distinugish this from the one in the panel because of the h4 banner in mainsearch.css-->
                <h4 class="Header Bottom">Related Discussions</h4>
                <?php echo WriteResults($this->Settings['Admin']->RelatedThreadsBottomDiscussionFormat, $Results) ?>
            </div>

            <?php
            $String = ob_get_contents();
            @ob_end_clean();
            return $String;
        } else { //writing to the panel
            ob_start();
            ?>

            <div id="RelatedDiscussion" class="Box">
                <h4 class="Header">Related Discussions</h4>
                <?php echo WriteResults('Simple', $Results, FALSE, TRUE) ?>
            </div>

            <?php
            $String = ob_get_contents();
            @ob_end_clean();
            return $String;
        }
    }

}