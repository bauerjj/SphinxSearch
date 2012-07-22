<?php

class SphinxSearchInstallWizard {

    var $PostPrefix = 'Configuration/'; //not sure why this is inside of the $_POST.....

    public function __construct() {
        //First clear all temp install files
        SphinxSearchGeneral::ClearLogFiles();
    }

    public function Index() {
        //create validation
        $Validation = new Gdn_Validation();
        $this->ConfigurationModel = new Gdn_ConfigurationModel($Validation);
        $this->ConfigurationModel->SetField(array(
        ));

        $this->Sender->Form->SetModel($this->ConfigurationModel);   //set model on form

        $this->Sender->SetData('NextAction', 'Detection');
        $this->Sender->SetData('InstallSphinx', FALSE);
        //print_r($this->Sender->Form); die;
        //if ($this->Sender->Form->AuthenticatedPostBack() === FALSE) {
        $this->Sender->Form->SetData($this->ConfigurationModel->Data);

        $this->_NextAction();

        //print_r($_POST);
        $this->Sender->Render($this->View);
    }

    /**
     * @pre must first validate rules according to each step
     *
     */
    private function _SaveSettings() {
        $Saved = $this->Sender->Form->Save();
        if ($Saved) {
            $this->Sender->StatusMessage = T("Your changes have been saved.");
            return TRUE;
        }
        else
            return FALSE;
    }

    /**
     * attempts to detect the presensce of sphinx in this order:
     *
     *  1. using the command prompt
     *  2. default installation path
     *  3. manual input paths
     *
     *  will only use binaries found automatically via these three methods...the manual inputs takes prescedence
     */
    private function _DetectionAction() {
        $DetectSystemSearchd = $this->_AutoDetectProgram('searchd');
        $DetectSystemIndexer = $this->_AutoDetectProgram('indexer');

        //test
//        $DetectSystemSearchd = FALSE;
//        $DetectSystemIndexer = FALSE;


        if ($DetectSystemSearchd == FALSE) {
            SaveToConfig('Plugin.SphinxSearch.SearchdPath', 'Not Detected');
        } else {
            SaveToConfig('Plugin.SphinxSearch.SearchdPath', $DetectSystemSearchd);
        }
        if ($DetectSystemIndexer == FALSE) {
            SaveToConfig('Plugin.SphinxSearch.IndexerPath', 'Not Detected');
        } else {
            SaveToConfig('Plugin.SphinxSearch.IndexerPath', $DetectSystemIndexer);
        }
        //check if prepackaged sphinx is installed
        if ($this->_ManualDetectProgram(C('Plugin.SphinxSearch.InstallPath', '') . DS . 'sphinx' . DS . 'bin' . DS . 'indexer', C('Plugin.SphinxSearch.InstallPath', '') . DS . 'sphinx' . DS . 'bin' . DS . 'searchd', $ShowError = FALSE)) {
            SaveToConfig('Plugin.SphinxSearch.SearchdPath', C('Plugin.SphinxSearch.InstallPath', '') . DS . 'bin' . DS . 'searchd');
            SaveToConfig('Plugin.SphinxSearch.IndexerPath', C('Plugin.SphinxSearch.InstallPath', '') . DS . 'bin' . DS . 'indexer');
            $DefaultInstall = TRUE;
        } else {

        }

        //check if sphinx installed at manual paths
        if ($this->_ManualDetectProgram(C('Plugin.SphinxSearch.ManualIndexerPath', ''), C('Plugin.SphinxSearch.ManualSearchdPath', ''), $ShowError = FALSE)) {
            SaveToConfig('Plugin.SphinxSearch.SearchdPath', C('Plugin.SphinxSearch.ManualSearchdPath', ''));
            SaveToConfig('Plugin.SphinxSearch.IndexerPath', C('Plugin.SphinxSearch.ManualIndexerPath', ''));
            $ManualInstall = TRUE;
        }
        else
            $ManualInstall = FALSE;


        if ((($DetectSystemSearchd && $DetectSystemIndexer) || $DefaultInstall || $ManualInstall) == TRUE)
            SaveToConfig('Plugin.SphinxSearch.Detected', TRUE); //already exists..no install required
        else {
            SaveToConfig('Plugin.SphinxSearch.SearchdPath', 'Not Detected');
            SaveToConfig('Plugin.SphinxSearch.IndexerPath', 'Not Detected');
            SaveToConfig('Plugin.SphinxSearch.Detected', FALSE); //not installed
        }
    }

    private function _VerifyDetection($InstallAction) {
        //save settings depending on if using system binaries or prepackaged installer or inputing manual paths to indexer/searchd
        if ($InstallAction == 'Manual') {
            $this->ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.ManualSearchdPath', 'Required');
            $this->ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.ManualIndexerPath', 'Required');
        } else if ($InstallAction == 'Detected') {
            $this->ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.SearchdPath', 'Required');
            $this->ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.IndexerPath', 'Required');
        } else if ($InstallAction == 'NotDetected')
            $this->ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.InstallPath', 'Required');
        if ($this->_SaveSettings()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * enter here when trying to verify the user input paths
     *
     * @param type $IndexerPath
     * @param type $SearchdPath
     */
    private function _DetectProgram($IndexerPath, $SearchdPath) {
        //first check manually inputs
        if ($this->_ManualDetectProgram($IndexerPath, $SearchdPath, TRUE)) {
            //found sphinx...skip auto detect
            SaveToConfig('Plugin.SphinxSearch.SearchdPath', $SearchdPath);
            SaveToConfig('Plugin.SphinxSearch.IndexerPath', $IndexerPath);
        } else {
            //no manual install found...check system automatically
            $this->_DetectionAction();
        }
    }

    /**
     * simply checks to see if that file exists there
     * @todo perhaps check the file size and compare? Maybe problematic on different systems
     *
     * @param type $IndexerPath
     * @param type $SearchdPath
     */
    private function _ManualDetectProgram($IndexerPath, $SearchdPath, $ShowError = FALSE) {
        $Error = FALSE;
        if (!file_exists($IndexerPath)) {
            if ($ShowError)
                $this->Sender->Form->AddError(T('Indexer not found at: ' . $IndexerPath));
            $Error = TRUE;
        }
        if (!file_exists($SearchdPath)) {
            if ($ShowError)
                $this->Sender->Form->AddError(T('Searchd not found at: ' . $SearchdPath));
            $Error = TRUE;
        }
        if ($Error)
            return FALSE; //errors
        else
            return TRUE; //found
    }

    //keep this public since used in install wizard
    public function RunTypeCommand($Prefix, $ProgramName) {
        $Haystack = exec("type {$Prefix}{$ProgramName}");
        $Needle = $ProgramName . ' is ';          //this is what 'type' returns if a match
        if (preg_match("#{$Needle}\s?([\w-/]+)\s?#", $Haystack, $Matches))  // \s matches whitepace .. \w matches word character
            return $Matches[1];
        else
            return FALSE;
    }

    private function _AutoDetectProgram($Command) {
        $Options = array(//populate this on a need-to basis for different installs
            '',
            'sphinx-',
            'sphinx_',
        );
        $ProgramName = escapeshellcmd($Command);

        foreach ($Options as $Prefix) {
            if ($Matched = $this->RunTypeCommand($Prefix, $ProgramName)) {
                if (file_exists($Matched))
                    return $Matched; //found
            }
        }
        return FALSE;   //no instance found
    }

    private function _ConnectionAction() {
        $this->ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.Prefix', 'Required');
        $this->ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.Port', 'Required');
        $this->ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.Port', 'Integer');
        if ($this->_SaveSettings()) {
            SaveToConfig('Plugin.SphinxSearch.Connection', TRUE); //complete this step
            $this->Sender->SetData('NextAction', 'Install');
        }
        else
            SaveToConfig('Plugin.SphinxSearch.Connection', FALSE); //don't continue
    }

    private function _InstallAction($InstallAction) {

        if ($InstallAction == 'Manual') {
            $this->_DetectProgram(C('Plugin.SphinxSearch.ManualIndexerPath', ''), C('Plugin.SphinxSearch.ManualSearchdPath', ''));
            return TRUE; //now the auto detect box should work
        } else if ($InstallAction == 'Detected') {
            //setup cron
            return TRUE; //continue to next step
        } else if ($InstallAction == 'NotDetected') { //perform the installation
            include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchinstall.php');
            $SphinxInstall = new SphinxSearchInstall(); //install program
            if (!$Error = $SphinxInstall->InstallExtract()) {

            } else {
                $this->Sender->Form->AddError($Error);
                $this->Sender->SetData('NextAction', 'Install'); //failed
            }
        }
    }

    private function _ToggleWizard() {
        if (Gdn::Session()->ValidateTransientKey(GetValue(1, $this->Sender->RequestArgs))) {
            if (!C('Plugin.SphinxSearch.StartWizard', FALSE)) {
                SaveToConfig('Plugin.SphinxSearch.StartWizard', TRUE);
            } else {
                //Reset steps
                SaveToConfig('Plugin.SphinxSearch.StartWizard', FALSE);
                SaveToConfig('Plugin.SphinxSearch.Connection', FALSE);
                SaveToConfig('Plugin.SphinxSearch.Installed', FALSE);
                SaveToConfig('Plugin.SphinxSearch.Config', FALSE);
                SaveToConfig('Plugin.SphinxSearch.Task', 'Idle');
                SaveToConfig('Plugin.SphinxSearch.Installed', FALSE);
            }
        }
        redirect('plugin/sphinxsearch/installwizard');
    }

    private function _InstallConfig() {
        $SphinxInstall = new SphinxSearchInstall(); //install program
        if ($Error = $SphinxInstall->InstallWriteConfig(C('Plugin.SphinxSearch.InstallPath'))) {
            $this->Sender->Form->AddError($Error);
            $this->Sender->SetData('NextAction', 'Install'); //failed
        } else if ($Error = $SphinxInstall->SetupCron(C('Plugin.SphinxSearch.IndexerPath'),  C('Plugin.SphinxSearch.ConfPath'), C('Plugin.SphinxSearch.Prefix'))) {
            $this->Sender->Form->AddError($Error);
            $this->Sender->SetData('NextAction', 'Install'); //failed
        } else { //SUCCESS
            SaveToConfig('Plugin.SphinxSearch.Installed', TRUE);
            $this->Sender->SetData('NextAction', 'Finish');
        }
    }

    private function _NextAction() {
        //$this->_InstallConfig();
        if (isset($_POST['NextAction'])) {
            SaveToConfig('Plugin.SphinxSearch.Config', TRUE);
            $this->Sender->SetData('NextAction', 'Config');
            die;
        }
        if (isset($_GET['action'])) {
            if (($_GET['action'] == 'ToggleWizard'))
                $this->_ToggleWizard();             //stop/start wizard
        }
        else if (isset($_POST[$this->PostPrefix . 'NextAction'])) {
            switch ($_POST[$this->PostPrefix . 'NextAction']) {
                case 'Detection':
                    $this->Sender->SetData('NextAction', 'Detection');
                    $this->_DetectionAction();    //attempt to find prescense of sphinx
                    $this->_ConnectionAction();   //verify inputs
                    break;
                case 'Install':              //Install Sphinx
                    $this->Sender->SetData('NextAction', 'Install');
                    $InstallAction = GetValue($this->PostPrefix . 'Plugin-dot-SphinxSearch-dot-Detected', $_POST);
                    if ($this->_VerifyDetection($InstallAction)) {
                        if ($this->_InstallAction($InstallAction)) //install
                            $this->Sender->SetData('NextAction', 'Detection');
                    }
                    break;
                case 'Config':
                    $this->_InstallConfig();
                    break;
            }
        }
        if (C('Plugin.SphinxSearch.Config') == TRUE) {
            $this->Sender->SetData('NextAction', 'Config');
        }
    }

}