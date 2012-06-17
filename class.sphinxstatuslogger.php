<?php

/**
 * This class is an observer to the following classes
 *  -Service: class.sphinxsearchinstall.php
 *
 *  This class handles all of the saving of configurations
 */
class SphinxStatusLogger implements SplObserver {

    public $Sender;
    public $View;

    public function __construct($Sender, $View) {
        $this->Sender = $Sender;
        $this->View = $View;
    }

    public function Update(SplSubject $Subject) {
        $Status = $Subject->getStatus(); //retrieve status array
        $Latest = $Status[sizeof($Status) - 1];
        $ClassName = GetValue('Name', $Latest);
        $Priority = GetValue('Priority', $Latest);
        $Name = GetValue('SettingsName', $Latest);
        $Value = GetValue('Value', $Latest);
        //echo SPHINX_PREFIX.$Name.$Value.',  ';
        switch ($ClassName) {
            case 'Service':     //class.sphinxsearchinstall.php
            case 'Install':
            default:
                if ($Priority != SPHINX_FATAL_ERROR)
                    SaveToConfig(SPHINX_PREFIX . $Name, $Value); //save this to configuration
                else {
                    if ($Name) //if this is given, assume that the value will be FALSE
                        SaveToConfig(SPHINX_PREFIX . $Name, FALSE); //non recoverable error has occured
                }
                break;
        }
        if ($Priority == SPHINX_FATAL_ERROR) {
            $Msg = GetValue('Message', $Latest);
            //If an error, must handle this immidiatl to stop program flow from continuing
            if (isset($Msg))
                $this->Sender->Form->AddError($Msg);
            else {
                $this->Sender->Form->AddError('An error has occured in ' . $ClassName);
            }
            if (is_null($this->Sender))
                throw new Exception($Msg);
            else
                $this->Sender->Render($this->View);
        }
    }

}
