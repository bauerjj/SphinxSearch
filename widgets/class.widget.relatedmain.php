<?php

class WidgetRelatedMain extends Widgets implements SplObserver{

    private $Queries = array();

    public function __construct($SphinxClient, $Settings) {
        parent::__construct($SphinxClient, $Settings);
    }

    public function Update(SplSubject $Subject){

    }

    public function AddQuery($Sender, $Options = FALSE) {
        if ($Sender->ControllerName == 'searchcontroller'){
        $Sanitized = $this->ValidateInputs();
        $Query = $this->RelatedThreads($Sanitized['Query'], $this->Settings['Admin']->LimitRelatedMain);

        $QueryIndex = $this->SphinxClient->AddQuery($Query, $Index = SPHINX_INDEX_DIST, 'Main Related');
        $this->Queries[] = array(
            'Name' => 'Related_Main',
            'Index' => $QueryIndex,
            'Highlight' => FALSE,
            'IgnoreFirst' => TRUE,
        );
        return $this->Queries;
        }
        else
            return FALSE;
    }



}