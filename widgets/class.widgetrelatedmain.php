<?php

class SphinxWidgetRelatedMain extends SphinxWidgets {

    private $Queries = array();

    public function __construct($SphinxClient) {
        parent::__construct($SphinxClient);
    }

    public function AddQuery($Options = FALSE) {
        $Sanitized = $this->ValidateInputs();
        $Query = $this->RelatedThreads($Sanitized['Query'], $this->Settings['Admin']->LimitRelatedMain);

        $QueryIndex = $this->SphinxClient->AddQuery($Query, $Index = SPHINX_INDEX_DIST, 'Main Related');
        $this->Queries = array(
            'Name' => 'Related_Main',
            'Index' => $QueryIndex,
            'Highlight' => FALSE,
            'IgnoreFirst' => TRUE,
        );
        return $this->Queries;
    }



}