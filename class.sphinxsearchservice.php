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

    public function KillSearchd(){
        $Command = 'killall -9 searchd'; //kills all instances of searchd
        $Error = SphinxSearchGeneral::RunCommand($Command, '/', 'Attempting to kill all instances of searchd', $Background = FALSE);
    }

    /**
     * Parse sphinx conf and grab path to search pid file
     *
     * @param string $SS_conf filename
     * @return string
     */
    public function GetPIDFileName() {
        $Content = file_get_contents($this->Settings['Install']->ConfPath);
        if (preg_match("#\bpid_file\s+=\s+(.*)\b#", $Content, $Matches)) {
            return $Matches[1];
        }
        else
            parent::Update(SS_FATAL_ERROR, '', FALSE, 'Cannot find PID file location defined inside of configuration: ' . $this->Settings['Install']->ConfPath);
        return FALSE;
    }

    /**
     * @pre validatelogpermissions
     * @return boolean
     */
    public function GetSearchLog() {
        $Content = file_get_contents($this->Settings['Install']->ConfPath);
        if (preg_match("#\blog\s+=\s+(.*)\b#", $Content, $Matches)) {
            return $Matches[1];
        }
        else
            parent::Update(SS_FATAL_ERROR, '', FALSE, 'Cannot find Search log location defined inside of configuration: ' . $this->Settings['Install']->ConfPath);
        return FALSE;
    }

    public function GetQueryLog() {
        $Content = file_get_contents($this->Settings['Install']->ConfPath);
        if (preg_match("#\bquery_log\s+=\s+(.*)\b#", $Content, $Matches)) {
            return $Matches[1];
        }
        else
            parent::Update(SS_FATAL_ERROR, '', FALSE, 'Cannot find query log location defined inside of configuration found: ' . $this->Settings['Install']->ConfPath);
        return FALSE;
    }

    public function GetDataPath() {
        $Content = file_get_contents($this->Settings['Install']->ConfPath);
        if (preg_match("#\bpath\s+=\s+(.*\bdata\b)\b#", $Content, $Matches)) {
            return $Matches[1] . DS; //IMPORTANT!! return with the slash
        }
        else
            echo 'no'; die;
        parent::Update(SS_FATAL_ERROR, '', FALSE, 'Cannot find data path location defined inside of configuration found: ' . $this->Settings['Install']->ConfPath);
        return FALSE;
    }

    public function Status() {
        $SphinxSearchModel = new SphinxClient(); ///@todo fix this from getting new instance of sphinxclient
        $Status = $SphinxSearchModel->Status(); //will return an array of misc info if sphinx is running
        if ($Status) {
            parent::Update(SS_SUCCESS, 'Uptime', $Status[0][1]); //sphinx returns uptime in seconds
            parent::Update(SS_SUCCESS, 'SearchdConnections', $Status[1][1]);
            parent::Update(SS_SUCCESS, 'MaxedOut', $Status[2][1]);
            parent::Update(SS_SUCCESS, 'TotalQueries', $Status[12][1]);
        }
        $this->CheckSphinxRunning(); //update searchd status
        $this->ValidateInstall(); //validate the install
    }

    /**
     * Simply checks the existense of searchd/indexer/sphinx.conf
     */
    public function ValidateInstall() {
        if (!file_exists($this->Settings['Install']->IndexerPath))
            parent::Update(SS_FATAL_ERROR, 'IndexerFound', FALSE, "Can't find indexer at path: " . $this->Settings['Install']->IndexerPath);
        else
            parent::Update(SS_SUCCESS, 'IndexerFound', TRUE);
        if (!file_exists($this->Settings['Install']->SearchdPath))
            parent::Update(SS_FATAL_ERROR, 'SearchdFound', FALSE, "Can't find searchd at path: " . $this->Settings['Install']->SearchdPath);
        else
            parent::Update(SS_SUCCESS, 'SearchdFound', TRUE);
        if (!file_exists($this->Settings['Install']->ConfPath))
            parent::Update(SS_FATAL_ERROR, 'ConfFound', FALSE, "Can't find configuration file at path: " . $this->Settings['Install']->ConfPath);
        else
            parent::Update(SS_SUCCESS, 'ConfFound', TRUE);
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

    /**
     * starting the daemon service (searchd) will create log files and search.pid
     * @return type
     */
    public function Start() {
        if (!$this->CheckSphinxRunning()) { //don't start if already running
            $Command = $this->Settings['Install']->SearchdPath . ' --config ' . $this->Settings['Install']->ConfPath;
            $Error = SphinxSearchGeneral::RunCommand($Command, '/', 'Starting searchd', $Background = FALSE);
            if ($Error) {
                parent::Update(SS_FATAL_ERROR, 'SearchdRunning', FALSE, $Error);
            }
            else
                parent::Update(SS_SUCCESS, 'SearchdRunning', TRUE); //save as running
        }
    }

    /**
     * stops the searchd daemon
     * @return type
     */
    public function Stop() {
        if ($this->CheckSphinxRunning()) {
            $Command = $this->Settings['Install']->SearchdPath . ' --config ' . $this->Settings['Install']->ConfPath . ' --stop';
            if ($Error = SphinxSearchGeneral::RunCommand($Command, '/', 'Attempting to stop searchd', $Background = FALSE))
                parent::Update(SS_FATAL_ERROR, 'SearchdRunning', FALSE, $Error);
            else
                parent::Update(SS_SUCCESS, 'SearchdRunning', FALSE); //save as not running
        }
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

    public function GetPID() {
        $PIDPath = $this->GetPIDFileName($this->Settings['Install']->ConfPath); //get path to PID file
        if (file_exists($PIDPath) && is_readable($PIDPath)) {
            $PID = trim(file_get_contents($PIDPath));
            exec("ps $PID", $Output);
            if (count($Output) >= 2) {
                return TRUE;
            }
        } else {
            parent::Update(SS_FATAL_ERROR, '', '', 'Unable to read PID file at ' . $PIDPath);
        }
    }

    /**
     * There is a bug using FuzzyTime as of 2.0.18...multiple redefines
     * @param type $FuzzyTime
     * @return boolean
     */
    public function GetIndexModifyTimer($FuzzyTime = TRUE) {
        $Return = array();
        $Format = new Gdn_Format();


        $PIDPath = $this->GetPIDFileName();
        $Index = $this->GetMainIndexFileName();
        $MainIndex = $Index['Main'];
        $DeltaIndex = $Index['Delta'];

        if ($MainIndex) {
            if ($FuzzyTime)
                $Return['Main'] = $Format->ToDateTime(filemtime($MainIndex), TRUE);
            else
                $Return['Main'] = filemtime($MainIndex);
        }
        else
            $Return['Main'] = FALSE;
        if ($DeltaIndex) {
            if ($FuzzyTime)
                $Return['Delta'] = $Format->ToDateTime(filemtime($DeltaIndex), TRUE);
            else
                $Return['Delta'] = filemtime($DeltaIndex);
        }
        else
            $Return['Delta'] = FALSE;

        return $Return;
    }

    /**
     * @param string possible extensions (spi/spa/spd/sph/spk/spm/spp/sps/)
     * @return array main/delta index file path location
     */
    public function GetMainIndexFileName($Extension = '.spi') {
        $Return = array();
        $SphinxConf = $this->Settings['Install']->ConfPath;
        $Content = file_get_contents($SphinxConf);
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

    private function ReIndex($IndexName, $Background = FALSE) {
        $Prefix = $this->Settings['Install']->Prefix;
        switch ($IndexName) {
            case 'all':
                $IndexName = $Prefix . SS_STATS_INDEX . ' ' . $Prefix . SS_DELTA_INDEX . ' ' . $Prefix . SS_MAIN_INDEX . ' ';
                break;
            case SS_STATS_INDEX:
                $IndexName = $Prefix . SS_STATS_INDEX;
                break;
            case SS_DELTA_INDEX:
                $IndexName = $Prefix . SS_DELTA_INDEX;
                break;
            case SS_MAIN_INDEX:
            default:
                $IndexName = $Prefix . SS_MAIN_INDEX;
                break;
        }

        //if sphinx is running..add the 'rotate' command
        $Rotate = '--rotate';

        $Command = $this->Settings['Install']->IndexerPath . " $IndexName  $Rotate  --config " . $this->Settings['Install']->ConfPath;

        $Error = SphinxSearchGeneral::RunCommand($Command, '/', 'Indexing ' . $IndexName, $Background); //run this in the background perhaps
        if ($Error)
            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, 'Failed to index ' . $IndexName . ': ' . $Error);
    }

    public function ReIndexMain($Background) {
        $this->ReIndex(SS_MAIN_INDEX,$Background);
        parent::Update(SS_SUCCESS, 'ServicePollTask', 'IndexMain');
        parent::Update(SS_SUCCESS, 'IndexerMainLast', now());
    }

    public function ReIndexDelta($Background) {
        $this->ReIndex(SS_DELTA_INDEX,$Background);
        parent::Update(SS_SUCCESS, 'ServicePollTask', 'IndexDelta');
        parent::Update(SS_SUCCESS, 'IndexerDeltaLast', now());
    }

    public function ReIndexStats($Background) {
        $this->ReIndex(SS_STATS_INDEX,$Background);
        parent::Update(SS_SUCCESS, 'ServicePollTask', 'IndexStats');
        parent::Update(SS_SUCCESS, 'IndexerStatsLast', now());
    }

}