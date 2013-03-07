<?php

/**
 * Grabs the member from the autocomplete box
 */
class WidgetMember extends Widgets implements SplObserver {

    private $Name = 'MemberSearch'; //name of the search
    private $Queries = array(); //keep track of query offset

    public function __construct($SphinxClient, $Settings) {
        parent::__construct($SphinxClient, $Settings);
    }

    public function Update(SplSubject $Subject) {
        $Status = $Subject->getStatus(); //retrieve status array
        $Results = $Status['Results'];
        $Sender = $Status['Sender'];

        if (isset($Results[$this->Name])) {
            if (isset($Results[$this->Name]['matches'])) {
                // print_r($Results[$this->Name]['matches']); die;
                $Formatted = $this->ToString($Results[$this->Name]['matches']);
                $Module = new MemberModule($Formatted);
                $Sender->AddModule($Module);
            }
        }
    }

    public function AddQuery($Sender, $Options = FALSE) {
        if ($Sender->ControllerName == 'searchcontroller') {
            $Sanitized = $this->ValidateInputs();
            $this->SphinxClient->ResetFilters();
            $this->SphinxClient->ResetGroupBy();
            $this->SphinxClient->SetGroupDistinct(SS_ATTR_USERID); //only want one unique username
            $this->SphinxClient->SetGroupBy(SS_ATTR_USERID, SPH_GROUPBY_ATTR);
            $this->SphinxClient->SetLimits(0, $this->Settings['Admin']->LimitMemberMatches);


            $Query = $Sanitized['Query'];

            $Search = $this->FieldSearch($Query, array(SS_FIELD_USERNAME));
            $QueryIndex = $this->SphinxClient->AddQuery($Search . ' ', $Index = SS_INDEX_DIST, $this->Name);

            $this->Queries[] = array(
                'Name' => $this->Name,
                'Index' => $QueryIndex,
            );

            return $this->Queries;
        }
    }

    public function ToString($Results) {
        $UserIDs = array();
        foreach ($Results as $Row) {
            $UserIDs[] = $Row->{SS_ATTR_USERID}; ///IMPORTANT, this is lowercase since grab results directly from sphinx
        }
        // print_r($Return); die;

        $Sql = clone Gdn::Sql();
        $Users = $Sql
                        ->Select('Photo, UserID, Name')
                        ->From('User')
                        ->WhereIn('UserID', $UserIDs)
                        ->Get()->ResultObject();
        ob_start();
        ?>
        <div id="People" class="Box People">
            <h4 class="Header"><?php echo T('People') ?></h4>
            <ul class="PanelInfo PanelDiscussions">
                <?php foreach ($Users as $Row) : ?>
                    <li class="Item">
                        <?php $User = UserBuilder($Row); ?>
                        <?php  echo UserPhoto($User); echo UserAnchor($User); ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
        <?php
        $String = ob_get_contents();
        @ob_end_clean();
        return $String;
    }

}