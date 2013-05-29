<?php

class SphinxSearchService extends SphinxObservable {

    private $Settings = array();

    public function __construct($Config) {
        $this->Settings = $Config;
        parent::__construct();
    }

    //Use this when want to pass in new settings
    public function NewSettings($Settings) {
        $this->Settings = $Settings;
    }

    /**
     * Parse sphinx conf and grab path to search pid file
     *
     * @param string $SS_conf filename
     * @return string
     */
    public function GetPIDFileName() {
        $Content = $this->Settings['Install']->ConfText;
        if (preg_match("#\bpid_file\s+=\s+(.*)\b#", $Content, $Matches)) {
            return $Matches[1];
        }
        else
            parent::Update(SS_FATAL_ERROR, '', FALSE, 'Cannot find PID file location defined inside of configuration text');
        return FALSE;
    }

    /**
     * @pre validatelogpermissions
     * @return boolean
     */
    public function GetSearchLog() {
        $Content = $this->Settings['Install']->ConfText;
        if (preg_match("#\blog\s+=\s+(.*)\b#", $Content, $Matches)) {
            return $Matches[1];
        }
        else
            parent::Update(SS_FATAL_ERROR, '', FALSE, 'Cannot find Search log location defined inside of configuration text');
        return FALSE;
    }

    public function GetQueryLog() {
        $Content = $this->Settings['Install']->ConfText;
        if (preg_match("#\bquery_log\s+=\s+(.*)\b#", $Content, $Matches)) {
            return $Matches[1];
        }
        else
            parent::Update(SS_FATAL_ERROR, '', FALSE, 'Cannot find query log location defined inside of configuration text');
        return FALSE;
    }

    public function GetDataPath() {
        $Content = $this->Settings['Install']->ConfText;
        if (preg_match("#\bpath\s+=\s+(.*\bdata\b)\b#", $Content, $Matches)) {
            return $Matches[1] . DS; //IMPORTANT!! return with the slash
        }
        else
            echo 'no'; die;
        parent::Update(SS_FATAL_ERROR, '', FALSE, 'Cannot find data path location defined inside of configuration text');
        return FALSE;
    }

    public function Status() {
        $SphinxSearchModel = new SphinxClient(); ///@todo fix this from getting new instance of sphinxclient
        $Status = $SphinxSearchModel->Status(); //will return an array of misc info if sphinx is running
        if ($Status) {
        }
        $this->CheckSphinxRunning(); //update searchd status
        $this->ValidateInstall(); //validate the install
    }

    /**
     * No more validation
     */
    public function ValidateInstall() {
//        if (!file_exists($this->Settings['Install']->IndexerPath))
//            parent::Update(SS_FATAL_ERROR, 'IndexerFound', FALSE, "Can't find indexer at path: " . $this->Settings['Install']->IndexerPath);
//        else
//            parent::Update(SS_SUCCESS, 'IndexerFound', TRUE);
//        if (!file_exists($this->Settings['Install']->SearchdPath))
//            parent::Update(SS_FATAL_ERROR, 'SearchdFound', FALSE, "Can't find searchd at path: " . $this->Settings['Install']->SearchdPath);
//        else
//            parent::Update(SS_SUCCESS, 'SearchdFound', TRUE);
//        if (!file_exists($this->Settings['Install']->ConfPath))
//            parent::Update(SS_FATAL_ERROR, 'ConfFound', FALSE, "Can't find configuration file at path: " . $this->Settings['Install']->ConfPath);
//        else
//            parent::Update(SS_SUCCESS, 'ConfFound', TRUE);
    }

    public function CheckPort() {
        $Host = $this->Settings['Install']->Host;
        $Port = $this->Settings['Install']->Port;
        try {
            $fp = fsockopen($Host, $Port, $errno, $errstr, 5);
            if (is_resource($fp)) {
                fclose($fp);
                parent::Update(SS_SUCCESS, 'SearchdPortStatus', 'Open');
            }
            else
                parent::Update(SS_WARNING, 'SearchdPortStatus');
        } catch (Exception $e) {
            parent::Update(SS_FATAL_ERROR, 'SearchdPortStatus', FALSE, $e);
        }
    }

    public function CheckLogPermssions() {
        $QueryLog = $this->GetQueryLog();
        $SearchLog = $this->GetSearchLog();

        if (!file_exists($QueryLog) || !is_readable($QueryLog))
            parent::Update(SS_FATAL_ERROR, 'This file does not exist or is not readable...try chmod 777 here: ' . $QueryLog);
        if (!file_exists($QueryLog) || !is_readable($QueryLog))
            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, 'This file does not exist or is not readable...try chmod 777 here: ' . $SearchLog);
    }

    public function CheckSphinxRunning() {
        $SphinxSearchModel = new SphinxClient();///@todo fix this from getting new instance of sphinxclient
        $Status = $SphinxSearchModel->Status(); //will return an array of misc info if sphinx is running
        if (!empty($Status)) {
            parent::Update(SS_SUCCESS, 'SearchdRunning', TRUE); //save as running
            return $Status; //yes, it is
        } else {
            parent::Update(SS_SUCCESS, 'SearchdRunning', FALSE); //save as not running
            return FALSE; //not running
        }
    }

    /**
     * @param string possible extensions (spi/spa/spd/sph/spk/spm/spp/sps/)
     * @return array main/delta index file path location
     */
    public function GetMainIndexFileName($Extension = '.spi') {
        $Return = array();
        $SphinxConf = $this->Settings['Install']->ConfText;
        $Content = $SphinxConf;
        if (preg_match_all("#\bpath\s+=\s+(.*)\b#", $Content, $Matches)) {
            $SphinxMainIndexPath = $Matches[1][0] . $Extension;
            $SphinxDeltaIndexPath = $Matches[1][1] . $Extension;
        }
        if (!$SphinxMainIndexPath || !file_exists($SphinxMainIndexPath) || !is_readable($SphinxMainIndexPath))
            $Return['Main'] = FALSE;

        else
            $Return['Main'] = $SphinxMainIndexPath;
        if (!$SphinxDeltaIndexPath || !file_exists($SphinxDeltaIndexPath) || !is_readable($SphinxDeltaIndexPath))
            $Return['Delta'] = FALSE;
        else
            $Return['Delta'] = $SphinxDeltaIndexPath;

        return $Return;
    }
}