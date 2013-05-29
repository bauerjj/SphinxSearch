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
            parent::Update(SS_SUCCESS, 'SearchdPath', 'Enter Path'); //did not find an instance of searchd
        } else {
            parent::Update(SS_SUCCESS, 'SearchdPath', $DetectSystemSearchd); //DID find searchd
        }
        if ($DetectSystemIndexer == FALSE) {
            parent::Update(SS_SUCCESS, 'IndexerPath', 'Enter Path'); //did not find an instance of indexer
        } else {
            parent::Update(SS_SUCCESS, 'IndexerPath', $DetectSystemIndexer); //DID find searchd
        }

        //check if sphinx installed at manual paths
        $ManualDetect = $this->DetectProgram($ShowError = FALSE, array(
            'IndexerPath' => $this->Settings['Install']->IndexerPath,
            'SearchdPath' => $this->Settings['Install']->SearchdPath,
            'ConfPath' => $this->Settings['Install']->ConfPath,
                ));

    }

    /**
     * Force a manual install via distro or command line (Plugin CANNOT do install by itself)
     * @param type $InstallAction
     * @param type $Background
     * @param type $Service
     * @param type $Install
     */
    public function InstallAction($InstallAction, $Background, $Service, $Install) {
        /**
         * Lets not detect the location of the indexer/searchd/sphinx.conf file. The webserver
         * must be given proper read/write permissions outside of the web root in order to detect
         * the prescense of sphinx (assuming installation is outside of the web root).
         */
//            $Detect = $this->DetectProgram($ShowError = TRUE, array(
//                'IndexerPath' => $this->Settings['Install']->IndexerPath,
//                'SearchdPath' => $this->Settings['Install']->SearchdPath,
//                'ConfPath' => $this->Settings['Install']->ConfPath,
//                    ));
            $Detect = true;
            if ($Detect) {//if they do, then save them at the REAL paths that are used by plugin (i.e strip off the 'Manual' prefix)
                parent::Update(SS_SUCCESS, 'IndexerPath', $this->Settings['Install']->IndexerPath);
                parent::Update(SS_SUCCESS, 'SearchdPath', $this->Settings['Install']->SearchdPath);
                parent::Update(SS_SUCCESS, 'ConfPath', $this->Settings['Install']->ConfPath);
                parent::Update(SS_SUCCESS, 'ConfText', $this->Settings['Install']->ConfText);

                //get new settings here since confpath has suddnely changed above
                $Settings = SphinxFactory::BuildSettings();
                $Service->NewSettings($Settings->GetAllSettings());
                //now get the existing PID/log/query_log paths to save in the new sphinx.conf from the exisitng sphinx.conf
                parent::Update(SS_SUCCESS, 'LogPath', $Service->GetSearchLog());
                parent::Update(SS_SUCCESS, 'QueryPath', $Service->GetQueryLog());
                parent::Update(SS_SUCCESS, 'PIDPath', $Service->GetPIDFileName());
                parent::Update(SS_SUCCESS, 'DataPath', $Service->GetDataPath());
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