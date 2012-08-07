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

        $this->Service = SphinxFactory::BuildService($this->Settings);
        $this->Service->Attach(new SphinxStatusLogger($Sender, $View));

        $this->Wizard = SphinxFactory::BuildWizard($this->Settings);
        $this->Wizard->Attach(new SphinxStatusLogger($Sender, $View));
    }

    public function ToggleWizard() {
        $this->Wizard->ToggleWizard();
    }

    /**
     *Checks if the pid/error/output.txt files are both read/writeable for any tasks to be run in the background require
     * that their status can be written to
     */
    public function CheckDebugFiles(){
        $this->Install->CheckDebugFiles();
    }

    public function Detect() {
        $this->Wizard->DetectionAction();    //attempt to find prescense of sphinx
    }

    public function GetSettings() {
        //return $this->Settings;
        $Setup = SphinxFactory::BuildSettings();
        return $Setup->GetAllSettings();
    }

    public function ValidateInstall() {
        $this->Service->ValidateInstall();
    }

    public function SetupCron(){
        $this->Install->SetupCron();
    }

    public function WriteConfigFile(){
        $this->Install->InstallWriteConfig();
    }

    public function InstallConfig(){
        //Get here when indexer and searchd have been installed...don't check if sphinx.conf exists just yet!
        $this->Install->InstallWriteConfig();
        $this->Service->ValidateInstall();  //now check if sphinx is installed
        $this->SetupCron();
    }

    public function InstallAction($InstallAction, $Background ){
        if($this->CheckSphinxRunning())
            $this->Stop(); //stop if it is running before a new install is made
        return $this->Wizard->InstallAction($InstallAction, $Background , $this->Service, $this->Install);
    }

    public function CheckSphinxRunning() {
        return $this->Service->CheckSphinxRunning();
    }

    public function Status() {
        $this->Service->Status();
    }

    public function Start() {
        $this->Service->Start();
    }

    public function Stop() {
        $this->Service->Stop();
    }

    public function ReIndexMain($Background) {
        $this->Service->ReIndexMain($Background);
    }

    public function ReIndexDelta($Background) {
        $this->Service->ReIndexDelta($Background);
    }

    public function ReIndexStats($Background) {
        $this->Service->ReIndexStats($Background);
    }

    public function CheckPort() {
        $this->Service->CheckPort();
    }

    /**
     * Searches for all instances of searchd and kills them
     * This is useful is you get the "Unknown PID error" (multiple instances running)
     */
    public function KillSearchd(){
        $this->Service->KillSearchd();
    }

}