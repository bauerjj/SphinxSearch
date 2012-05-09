<?php

class SphinxSearchService {

    public function __construct($Sender, $View) {
        $this->Sender = $Sender;
        $this->View = $View;
    }

    /**
     * exit due to one or more errors
     */
    private function _exit($Message = '') {
        $this->Sender->Form->AddError($Message);
        $this->Sender->Render($this->View);
        die;
    }

    /**
     * starting the daemon service (searchd) will create log files and search.pid
     * @return type
     */
    function Start() {
        if ($Error = SphinxSearchGeneral::ValidateInstall())
            return $Error;
        $this->Stop(); //kill daemon if runned
        $Command = C('Plugin.SphinxSearch.SearchdPath') . ' --config ' . C('Plugin.SphinxSearch.ConfPath');
        echo $Command ; die;
        if ($Error = SphinxSearchGeneral::RunCommand($Command, '/', 'Starting searchd')) {
            SaveToConfig('Plugin.SphinxSearch.Running', FALSE); //searchd start/stopped
            return $Error; //fail
        }
        else
            return FALSE; //success
    }

    /**
     * stops the searchd daemon
     * @return type
     */
    function Stop() {
        if (1) {
            $Command = C('Plugin.SphinxSearch.SearchdPath') . ' --config ' . C('Plugin.SphinxSearch.ConfPath') . ' --stop';
            if ($Error = SphinxSearchGeneral::RunCommand($Command, '/', 'Stopping searchd')) {
                return $Error;
            }
            else
                return FALSE;
        }
    }

    public function _CheckSphinxRunning() {
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'sphinxsearchmodel.php'); //load the Sphinx API file

        $this->GetPID(C('Plugin.SphinxSearch.ConfPath'));
    }

///var/lib/sphinx/log/

    /**
     * Parse sphinx conf and grab path to search pid file
     *
     * @param string $sphinx_conf filename
     * @return string
     */
    function GetPID() {
        $SphinxConf = C('Plugin.SphinxSearch.ConfPath');
        if (!file_exists($SphinxConf) || !is_readable($SphinxConf)) {
            return false;
        }
        $Content = file_get_contents($SphinxConf);
        if (preg_match("#\bpid_file\s+=\s+(.*)\b#", $Content, $Matches)) {
            return $Matches[1];
        }
        return FALSE;
    }

    private function ReIndex($IndexName) {

        if ($Error = SphinxSearchGeneral::ValidateInstall())
            return $Error;

        $Prefix = C('Plugin.SphinxSearch.Prefix', 'vss_');
        switch ($IndexName) {
            case 'all':
                $IndexName = $Prefix . SPHINX_SEARCH_STATS_INDEX . ' ' . $Prefix . SPHINX_SEARCH_DELTA_INDEX . ' ' . $Prefix . SPHINX_SEARCH_MAIN_INDEX . ' ';
                break;
            case SPHINX_SEARCH_DELTA_INDEX:
                $IndexName = $Prefix . SPHINX_SEARCH_STATS_INDEX;
                break;
            case SPHINX_SEARCH_DELTA_INDEX:
                $IndexName = $Prefix . SPHINX_SEARCH_DELTA_INDEX;
                break;
            case SPHINX_SEARCH_MAIN_INDEX:
            default:
                $IndexName = $Prefix . SPHINX_SEARCH_DELTA_INDEX;
                break;
        }

        //if sphinx is running..add the 'rotate' command
        $Rotate = '--rotate';

        $Command = C('Plugin.SphinxSearch.IndexerPath') . " $IndexName  $Rotate  --config " . C('Plugin.SphinxSearch.ConfPath');
        if ($Error = SphinxSearchGeneral::RunCommand($Command, '/', 'Problem with Indexing'))
            return $Error;
        die;
    }

    public function ReIndexDelta() {
        $this->ReIndex(SPHINX_SEARCH_DELTA_INDEX);
    }

    public function ReIndexMain() {
        $this->ReIndex(SPHINX_SEARCH_MAIN_INDEX);
    }

    public function ReIndexStats() {
        $this->ReIndex(SPHINX_SEARCH_STATS_INDEX);
    }

    public function ReIndexAll() {
        $this->ReIndex('all');
    }

}