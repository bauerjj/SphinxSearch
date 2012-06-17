<?php

class SphinxSearchService extends SphinxObservable {

    protected $_Name = 'Service'; //for reporting purposes
    private $_Settings = array();

    public function __construct($Config) {
        $this->_Settings = $Config;
        parent::__construct();
    }

    public function Status(){
        $SphinxSearchModel = new SphinxSearchModel();
        $Status = $SphinxSearchModel->SphinxStatus(); //will return an array of misc info if sphinx is running
        if($Status){
            parent::Update(SPHINX_SUCCESS, 'Uptime', $Status[0][1]); //sphinx returns uptime in seconds
            parent::Update(SPHINX_SUCCESS, 'SearchdConnections', $Status[1][1]);
            parent::Update(SPHINX_SUCCESS, 'MaxedOut', $Status[2][1]);
            parent::Update(SPHINX_SUCCESS, 'TotalQueries', $Status[12][1]);
        }
        $this->CheckSphinxRunning(); //update searchd status
        $this->ValidateInstall(); //validate the install
    }

     /**
     * Simply checks the existense of searchd/indexer/sphinx.conf
     */
    public function ValidateInstall() {
        if (!file_exists($this->_Settings['Install']->IndexerPath))
            parent::Update(SPHINX_FATAL_ERROR, 'IndexerFound', FALSE, "Can't find indexer at path: ".$this->_Settings['Install']->IndexerPath);
        else
            parent::Update(SPHINX_SUCCESS, 'IndexerFound', TRUE);
        if (!file_exists($this->_Settings['Install']->SearchdPath))
            parent::Update(SPHINX_FATAL_ERROR, 'SearchdFound', FALSE, "Can't find searchd at path: ".$this->_Settings['Install']->SearchdPath);
        else
            parent::Update(SPHINX_SUCCESS, 'SearchdFound', TRUE);
        if (!file_exists($this->_Settings['Install']->ConfPath))
            parent::Update(SPHINX_FATAL_ERROR, 'SearchdFound', FALSE, "Can't find configuration file at path: ".$this->_Settings['Install']->ConfPath);
        else
            parent::Update(SPHINX_SUCCESS, 'ConfFound', TRUE);
    }


    public function CheckPort() {
        $Host = $this->_Settings['Install']->Host;
        $Port = $this->_Settings['Install']->Port;
        try {
            $fp = fsockopen($Host, $Port, $errno, $errstr, 5);
            if (is_resource($fp)) {
                fclose($fp);
                parent::Update(SPHINX_SUCCESS, 'SearchdPortStatus', 'Open');
            }
            else
                parent::Update(SPHINX_ERROR, 'SearchdPortStatus');
        } catch (Exception $e) {
            parent::Update(SPHINX_FATAL_ERROR, 'SearchdPortStatus', FALSE, $e);
        }
    }

    public function CheckLogPermssions() {
        $QueryLog = $this->GetQueryLog();
        $SearchLog = $this->GetSearchLog();

        if (!file_exists($QueryLog) || !is_readable($QueryLog))
            parent::Update(SPHINX_FATAL_ERROR, 'This file does not exist or is not readable...must CHMOD 777 here: ' . $QueryLog);
        if (!file_exists($QueryLog) || !is_readable($QueryLog))
            parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, 'This file does not exist or is not readable...must CHMOD 777 here: ' . $SearchLog);
    }

    /**
     * starting the daemon service (searchd) will create log files and search.pid
     * @return type
     */
    function Start() {
        if (!$this->CheckSphinxRunning()) { //don't start if already running
            $Command = $this->_Settings['Install']->SearchdPath . ' --config ' . $this->_Settings['Install']->ConfPath;
            if($Error = SphinxSearchGeneral::RunCommand($Command, '/', 'Starting searchd'))
                    parent::Update(SPHINX_FATAL_ERROR, '', '', $Error);
            else
                parent::Update(SPHINX_SUCCESS, 'SearchdStatus', TRUE);//save as running

        }
    }

    /**
     * stops the searchd daemon
     * @return type
     */
    function Stop() {
        if ($this->CheckSphinxRunning()) {
            $Command = $this->_Settings['Install']->SearchdPath . ' --config ' . $this->_Settings['Install']->ConfPath . ' --stop';
            if($Error = SphinxSearchGeneral::RunCommand($Command, '/', 'Attempting to stop searchd', $Background = FALSE))
                    parent::Update(SPHINX_FATAL_ERROR, '', '', $Error);
            else
                parent::Update(SPHINX_SUCCESS, 'SearchdStatus', FALSE); //save as not running
        }
    }

    public function CheckSphinxRunning() {
        $SphinxSearchModel = new SphinxSearchModel();
        $Status = $SphinxSearchModel->SphinxStatus(); //will return an array of misc info if sphinx is running
        if (!empty($Status)){
            parent::Update(SPHINX_SUCCESS, 'SearchdStatus', TRUE);//save as running
            return $Status; //yes, it is
        }
        else{
            parent::Update(SPHINX_SUCCESS, 'SearchdStatus', FALSE); //save as not running
            return FALSE; //not running
        }
    }

    public function GetPID() {
        $PIDPath = $this->GetPIDFileName($this->_Settings['Install']->ConfPath); //get path to PID file
        if (file_exists($PIDPath) && is_readable($PIDPath)) {
            $PID = trim(file_get_contents($PIDPath));
            exec("ps $PID", $Output);
            if (count($Output) >= 2) {
                return TRUE;
            }
        } else {
            parent::Update(SPHINX_FATAL_ERROR, '', '', 'Unable to read PID file at ' . $PIDPath);
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
        $SphinxConf = $this->_Settings['Install']->ConfPath;
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

    /**
     * Parse sphinx conf and grab path to search pid file
     *
     * @param string $sphinx_conf filename
     * @return string
     */
    function GetPIDFileName() {
        $Content = file_get_contents($this->_Settings['Install']->ConfPath);
        if (preg_match("#\bpid_file\s+=\s+(.*)\b#", $Content, $Matches)) {
            return $Matches[1];
        }
        return FALSE;
    }

    /**
     * @pre validatelogpermissions
     * @return boolean
     */
    public function GetSearchLog() {
        $Content = file_get_contents($this->_Settings['Install']->ConfPath);
        if (preg_match("#\blog\s+=\s+(.*)\b#", $Content, $Matches)) {
            return $Matches[1];
        }
        return FALSE;
    }

    public function GetQueryLog($SphinxConf) {
        $Content = file_get_contents($this->_Settings['Install']->ConfPath);
        if (preg_match("#\bquery_log\s+=\s+(.*)\b#", $Content, $Matches)) {
            return $Matches[1];
        }
        return FALSE;
    }

    private function ReIndex($IndexName) {
        $Prefix = $this->_Settings['Install']->Prefix;
        switch ($IndexName) {
            case 'all':
                $IndexName = $Prefix . SPHINX_SEARCH_STATS_INDEX . ' ' . $Prefix . SPHINX_SEARCH_DELTA_INDEX . ' ' . $Prefix . SPHINX_SEARCH_MAIN_INDEX . ' ';
                break;
            case SPHINX_SEARCH_DELTA_INDEX:
                $IndexName = $Prefix . SPHINX_SEARCH_DELTA_INDEX;
                break;
            case SPHINX_SEARCH_DELTA_INDEX:
                $IndexName = $Prefix . SPHINX_SEARCH_DELTA_INDEX;
                break;
            case SPHINX_SEARCH_MAIN_INDEX:
            default:
                $IndexName = $Prefix . SPHINX_SEARCH_MAIN_INDEX;
                break;
        }

        //if sphinx is running..add the 'rotate' command
        $Rotate = '--rotate';

        $Command = $this->_Settings['Install']->IndexerPath . " $IndexName  $Rotate  --config " . $this->_Settings['Install']->ConfPath;
        SphinxSearchGeneral::RunCommand($Command, '/', 'Indexing ' . $IndexName, $Background = TRUE); //run this in the background
    }

    public function ReIndexMain() {
        $this->ReIndex(SPHINX_SEARCH_MAIN_INDEX);
        parent::Update(SPHINX_SUCCESS, 'ServicePollTask', 'IndexMain');
        parent::Update(SPHINX_SUCCESS, 'IndexerMainLast', time());
    }

    public function ReIndexDelta() {
        $this->ReIndex(SPHINX_SEARCH_DELTA_INDEX);
        parent::Update(SPHINX_SUCCESS, 'ServicePollTask', 'IndexDelta');
        parent::Update(SPHINX_SUCCESS, 'IndexerDeltaLast', time());
    }

    public function ReIndexStats() {
        $this->ReIndex(SPHINX_SEARCH_STATS_INDEX);
        parent::Update(SPHINX_SUCCESS, 'ServicePollTask', 'IndexStats');
        parent::Update(SPHINX_SUCCESS, 'IndexerStatsLast', time());
    }

}