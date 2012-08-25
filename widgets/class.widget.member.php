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
        //do nothing
    }

    public function AddQuery($Sender, $Options = FALSE) {
         $MemberName = GetValue('MemberName', $Options);
        if ($MemberName) { //call this directy from within the POST controller in the plugin 'Controller_AutoCompleteMember'
            $this->SphinxClient->ResetFilters();
            $this->SphinxClient->ResetGroupBy();
            $this->SphinxClient->SetGroupDistinct(SS_ATTR_USERID); //only want one unique username
            $this->SphinxClient->SetGroupBy(SS_ATTR_USERID, SPH_GROUPBY_ATTR);

            //not limits

            $Query = $MemberName;

            $Search = $this->FieldSearch($Query, array(SS_FIELD_USERNAME));
            $QueryIndex = $this->SphinxClient->AddQuery($Search.' ', $Index = SS_INDEX_DIST, $this->Name);

            $this->Queries[] = array(
                'Name' => $this->Name,
                'Index' => $QueryIndex,
            );

            return $this->Queries;
        }
    }

    public function ToString($Results) {
        $Return = array();
        foreach ($Results as $Row) {
            $Return[] = $Row->{SS_FIELD_USERNAME}; ///IMPORTANT, this is lowercase since grab results directly from sphinx
        }
        return $Return;
    }

}