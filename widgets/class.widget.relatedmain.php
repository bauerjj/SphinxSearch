<?php

class WidgetRelatedMain extends Widgets implements SplObserver {

    private $Queries = array();
    private $Name = 'RelatedMainThreads';

    public function __construct($SphinxClient, $Settings) {
        parent::__construct($SphinxClient, $Settings);
    }

    public function Update(SplSubject $Subject) {
        $Status = $Subject->getStatus(); //retrieve status array
        $Results = $Status['Results'];
        $Sender = $Status['Sender'];

        if (isset($Results[$this->Name])) {
            if ($this->Settings['Admin']->LimitRelatedThreadsMain > 0) {
                if (isset($Results[$this->Name]['matches'])) {
                    $Matches = $this->GetSQLData('simple', $Results[$this->Name]['matches']);
                    $Module = new RelatedThreadsModule($Matches);
                    $Sender->AddModule($Module);
                }
            }
        }
    }

    public function AddQuery($Sender, $Options = FALSE) {
        if ($Sender->ControllerName == 'searchcontroller' && ($Options['Landing'] == FALSE) && $this->Settings['Admin']->LimitRelatedThreadsMain > 0) {
            $Sanitized = $this->ValidateInputs();
            $this->SphinxClient->ResetFilters();
            $this->SphinxClient->ResetGroupBy();
            $this->SphinxClient->SetSortMode(SPH_SORT_RELEVANCE);
            $this->SphinxClient->SetRankingMode(SPH_RANK_WORDCOUNT);
            $this->SphinxClient->SetLimits(0, $this->Settings['Admin']->LimitRelatedThreadsMain);
            //must first clear any special characters in query string if using the extended syntax
            //@todo probably better to use the 'words' index
            $Query = $this->FieldSearch($this->OperatorOrSearch($this->ClearFromTags($Sanitized['Query'])), array(SS_FIELD_TITLE));
            //echo $Query; die;

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

}