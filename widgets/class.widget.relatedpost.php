<?php

/**
 * Displays related threads above new topic discussions on the postcontroller
 *
 */
class WidgetRelatedPost extends Widgets implements SplObserver {

    public $Results = array();
    private $Queries = array();
    private $Name = 'RelatedPost';

    public function __construct($SphinxClient, $Settings) {
        parent::__construct($SphinxClient, $Settings);
    }

    public function Update(SplSubject $Subject) {
        //do nothing
    }

    /**
     * @todo fix this so the update function does something here
     *
     * @param type $Sender
     * @param type $Options
     * @return boolean
     */
    public function AddQuery($Sender, $Options = FALSE) {
        $Thread = $this->SphinxClient->EscapeString(GetValue('Query', $Options)); //get the discussion name (thread topic) to search against
        if ($Thread) { //call this directly from handler: Controller_newdiscussion
            $this->SphinxClient->ResetFilters();
            $this->SphinxClient->ResetGroupBy();
            $this->SphinxClient->SetLimits(0, $this->Settings['Admin']->LimitRelatedThreadsPost);

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

    public function ToString($Results, $Query) {
        $Matches = $this->GetSQLData($this->Settings['Admin']->RelatedThreadsPostFormat, GetValue('matches', $Results)); //get SQL data
        $Results = $this->HighLightResults($Matches, $Query, $this->Settings['Admin']->BuildExcerptsTitleEnable, $this->Settings['Admin']->BuildExcerptsBodyEnable);
        $String = '';
        if ($Results == 0) {
            return; //return an empty string if no results
        }
        $String .= WriteResults($this->Settings['Admin']->RelatedThreadsPostFormat, $Results);

        return $String;
    }

}

