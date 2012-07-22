<?php

class WidgetHitBox extends Widgets implements SplObserver {

    private $Name = 'HitBox';
    private $Queries = array(); //keep track of query offset

    public function __construct($SphinxClient, $Settings) {
        parent::__construct($SphinxClient, $Settings);
    }

    public function Update(SplSubject $Subject) {
        $Status = $Subject->getStatus(); //retrieve status array
        $Results = $Status['Results'];
        $Sender = $Status['Sender'];

        if ($Sender->ControllerName == 'searchcontroller' && isset($Results['Main']['words']) && !(empty($_GET) || isset($_GET['tar']))) {
            if ($this->Settings['Admin']->MainHitBoxEnable > 0) { //is the hitbox enabled?
                if (sizeof($Results['Main']['words']) > 0) {
                    $Module = new HitBoxModule($Results['Main']['words']);
                    $Sender->AddModule($Module);
                }
            }
        }
    }

    public function AddQuery($Sender, $Options = FALSE) {
        //do nothing
    }

}