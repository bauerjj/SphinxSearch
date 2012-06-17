<?php

/**
 * Create a Facade
 */

class SphinxSearchAdmin {

    public $Setup;

    public $Install;

    public $Service;

    public $Wizard;

    protected $Settings;

    public $Observable;



    public function __construct($Sender, $View) {

        $this->Setup = SphinxFactory::BuildSettings();
        $this->Settings = $this->Setup->GetAllSettings();

        $this->Install = SphinxFactory::BuildInstall($this->Settings);
        $this->Install->Attach(new SphinxStatusLogger($Sender, $View));

        $this->Service = SphinxFactory::BuildService($this->Settings);
        $this->Service->Attach(new SphinxStatusLogger($Sender, $View));

        $this->Wizard = SphinxFactory::BuildWizard();


    }

    public function GetSettings(){
        //return $this->Settings;
        $Setup = SphinxFactory::BuildSettings();
        return $Setup->GetAllSettings();
    }

    public function ValidateInstall(){
        $this->Service->ValidateInstall();
    }


    public function Status(){
        $this->Service->Status();
    }
    public function Start(){
        $this->Service->Start();
    }
    public function Stop(){
        $this->Service->Stop();
    }
    public function ReIndexMain(){
        $this->Service->ReIndexMain();
    }
    public function ReIndexDelta(){
        $this->Service->ReIndexDelta();
    }
    public function CheckPort(){
        $this->Service->CheckPort();
    }


}