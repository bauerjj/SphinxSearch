<?php

class SphinxSearchInstallWizard extends SphinxObservable {

    private $Settings = array();

    public function __construct($Config) {
        $this->Settings = $Config;
        parent::__construct();
    }

    /**
     * enter here after clikcing the 'start/stop' wizard button
     */
    public function ToggleWizard() {
        if (!$this->Settings['Wizard']->StartWizard) {
            parent::Update(SS_SUCCESS, 'StartWizard', TRUE);
        } else {
            //Reset steps
            parent::Update(SS_SUCCESS, 'StartWizard', FALSE);
            parent::Update(SS_SUCCESS, 'Connection', FALSE);
            parent::Update(SS_SUCCESS, 'Installed', FALSE);
            parent::Update(SS_SUCCESS, 'Config', FALSE);
            parent::Update(SS_SUCCESS, 'Task', 'Idle');
            parent::Update(SS_SUCCESS, 'Installed', FALSE);
        }
    }

    /**
     * attempts to detect the presensce of sphinx in this order:
     *
     *  1. using the command prompt (auto detect)
     *  2. default installation path (prepackaged)
     *  3. manual input paths
     *
     *  will only use binaries found automatically via these three methods...the manual inputs takes prescedence
     */
    public function DetectionAction() {
        $DetectSystemSearchd = $this->AutoDetectProgram('searchd'); //returns the full path if found
        $DetectSystemIndexer = $this->AutoDetectProgram('indexer'); //returns the full path if found
        //test
        //$DetectSystemSearchd = FALSE;
        //$DetectSystemIndexer = FALSE;

        if ($DetectSystemSearchd == FALSE) {
            parent::Update(SS_SUCCESS, 'ManualSearchdPath', 'Not Detected'); //did not find an instance of searchd
        } else {
            parent::Update(SS_SUCCESS, 'ManualSearchdPath', $DetectSystemSearchd); //DID find searchd
        }
        if ($DetectSystemIndexer == FALSE) {
            parent::Update(SS_SUCCESS, 'ManualIndexerPath', 'Not Detected'); //did not find an instance of indexer
        } else {
            parent::Update(SS_SUCCESS, 'ManualIndexerPath', $DetectSystemIndexer); //DID find searchd
        }
        //check if prepackaged sphinx is installed
        $ExistingDetect = $this->DetectProgram($ShowError = FALSE, array(
            'IndexerPath' => $this->Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'bin' . DS . 'indexer',
            'SearchdPath' => $this->Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'bin' . DS . 'searchd',
            'ConfPath' => $this->Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'etc' . DS . 'sphinx.conf',
                ));
        if ($ExistingDetect)
            $DefaultInstall = TRUE;
        else
            $DefaultInstall = FALSE; //manual location is not detected


//check if sphinx installed at manual paths
        $ManualDetect = $this->DetectProgram($ShowError = FALSE, array(
            'IndexerPath' => $this->Settings['Install']->ManualIndexerPath,
            'SearchdPath' => $this->Settings['Install']->ManualSearchdPath,
            'ConfPath' => $this->Settings['Install']->ManualConfPath,
                ));
        if ($ManualDetect)
            $ManualInstall = TRUE;
        else
            $ManualInstall = FALSE;

        //checks if (auto detect || prepackaged install || manual locations ) are installed
        if ((($DetectSystemSearchd && $DetectSystemIndexer) == TRUE) || $DefaultInstall)
            parent::Update(SS_SUCCESS, 'AutoDetected', TRUE); //already exists..no install required
        else
            parent::Update(SS_SUCCESS, 'AutoDetected', FALSE); //not detected
        if ($ManualInstall)
            parent::Update(SS_SUCCESS, 'ManualDetected', TRUE); //already exists..no install required
        else
            parent::Update(SS_SUCCESS, 'ManualDetected', FALSE); //already exists..no install required
    }

    public function InstallAction($InstallAction, $Background, $Service, $Install) {
        if ($InstallAction == 'Manual') {
            //check if file exist at the said locations
            $Detect = $this->DetectProgram($ShowError = TRUE, array(
                'ManualIndexerPath' => $this->Settings['Install']->ManualIndexerPath,
                'ManualSearchdPath' => $this->Settings['Install']->ManualSearchdPath,
                'ManualConfPath' => $this->Settings['Install']->ManualConfPath,
                    ));
            if ($Detect) {//if they do, then save them at the REAL paths that are used by plugin (i.e strip off the 'Manual' prefix)
                parent::Update(SS_SUCCESS, 'IndexerPath', $this->Settings['Install']->ManualIndexerPath);
                parent::Update(SS_SUCCESS, 'SearchdPath', $this->Settings['Install']->ManualSearchdPath);
                parent::Update(SS_SUCCESS, 'ConfPath', $this->Settings['Install']->ManualConfPath);

                //get new settings here since confpath has suddnely changed above
                $Settings = SphinxFactory::BuildSettings();
                $Service->NewSettings($Settings->GetAllSettings());
                //now get the existing PID/log/query_log paths to save in the new sphinx.conf from the exisitng sphinx.conf
                parent::Update(SS_SUCCESS, 'LogPath', $Service->GetSearchLog());
                parent::Update(SS_SUCCESS, 'QueryPath', $Service->GetQueryLog());
                parent::Update(SS_SUCCESS, 'PIDPath', $Service->GetPIDFileName());
                parent::Update(SS_SUCCESS, 'DataPath', $Service->GetDataPath());
            }
        } else if ($InstallAction == 'NotDetected') { //perform the installation
            parent::Update(SS_SUCCESS, 'ManualDetected', FALSE); // For the radio button to stay put on "prepackaged" install
            parent::Update(SS_SUCCESS, 'LogPath', $this->Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'var' . DS . 'log' . DS . 'search.log');
            parent::Update(SS_SUCCESS, 'QueryPath', $this->Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'var' . DS . 'log' . DS . 'query.log');
            parent::Update(SS_SUCCESS, 'PIDPath', $this->Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'var' . DS . 'log' . DS . 'search.pid');
            parent::Update(SS_SUCCESS, 'DataPath', $this->Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'var' . DS . 'data' . DS); //leave the slash in here!
            parent::Update(SS_SUCCESS, 'IndexerPath', $this->Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'bin' . DS . 'indexer');
            parent::Update(SS_SUCCESS, 'SearchdPath', $this->Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'bin' . DS . 'searchd');
            parent::Update(SS_SUCCESS, 'ConfPath', $this->Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'etc' . DS . 'sphinx.conf');
            $Settings = SphinxFactory::BuildSettings();
            $Install->NewSettings($Settings->GetAllSettings()); //need new settings
            $Install->InstallExtract($Background); //This begins the installation process if using the packaged sphinx installer
        }
    }

    /**
     * simply checks to see if that file exists there
     *
     * @param type $IndexerPath
     * @param type $SearchdPath
     */
    private function DetectProgram($ShowError, $Files) {
        foreach ($Files as $Name => $Path) {
            if (!file_exists($Path)) {
                if ($ShowError)
                    parent::Update(SS_FATAL_ERROR, $Name, 'Not Detected', T($Name . ' not found at: ' . $Path). "<br>May also try
                      turning on all errors, error_reporting(E_ALL);, to see if 'open_basedir restriction is NOT in effect"); //save as 'not detected'
                $Error = TRUE;
            }
            else
                parent::Update(SS_SUCCESS, $Name, $Path); //save path in settings
        }
        if (isset($Error))
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

    private function AutoDetectProgram($Command) {
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

}