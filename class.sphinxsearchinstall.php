<?php

class SphinxSearchInstall extends SphinxObservable {

    private $Settings = array();

    public function __construct($Config) {
        $this->Settings = $Config;
        parent::__construct();
    }

    public function NewSettings($Settings) {
        $this->Settings = $Settings;
    }

    private function _CheckPath($InstallPath, $CheckDir, $CheckWritable) {
        if ($CheckDir) {
            if (!is_dir($InstallPath)) {
                parent::Update(SS_FATAL_ERROR, FALSE, FALSE, 'This location is not a directory: ' . $InstallPath);
            }
        }
        if (1) { //ALWAYS check if readable
            if (!is_readable($InstallPath)) {
                parent::Update(SS_FATAL_ERROR, FALSE, FALSE, 'This location is not readable: ' . $InstallPath);
            }
        }
        if ($CheckWritable) {
            if (!IsWritable($InstallPath)) {
                parent::Update(SS_FATAL_ERROR, FALSE, FALSE, 'This location is not writable: ' . $InstallPath . ' <br/>Try chmod 777 this directory');
            }
        }
    }

    private function _IsEmptyDir($dir) {
        if (($files = @scandir($dir)) && count($files) <= 2) {
            return TRUE;
        }
        return FALSE;
    }

    private function _rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        $this->_rrmdir($dir . "/" . $object); else
                        unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    private function _GenerateConfContent($Template) {
        $SQLSock = '';
        if ('' != trim(ini_get('mysql.default_socket'))) {
            $SQLSock = 'sql_sock = ' . ini_get('mysql.default_socket');
        }
        //Check to see if grabing any of the tags
        ///@todo fix this stopgap solution with something more robust
        //Get list of tags
        $SQL = Gdn::SQL();
        if (Gdn::Structure()->TableExists('TagDiscussion'))
            $Tags = TRUE;
        else
            $Tags = FALSE;

        $DBPrefix = C('Database.Name') . '.' . C('Database.DatabasePrefix', 'GDN_'); //join these 2
        $Search = array(
            '{sql_sock}' => $SQLSock,
            '{sql_host}' => $this->Settings['Install']->Host,
            '{sql_user}' => C('Database.User'),
            '{sql_pass}' => C('Database.Password'),
            '{sql_db}' => C('Database.Name'),
            '{charset_type}' => C('Garden.Charset', 'utf-8'),
            '{charset_type_mysql}' => C('Database.CharacterEncoding', 'utf8'), //MySQL omits the hyphen
            '{install_path}' => $this->Settings['Install']->InstallPath,
            '{assests_path}' => PATH_PLUGINS . DS . 'SphinxSearchLite' . DS . 'assests' . DS, //for stopwords.txt
            '{searchd_port}' => $this->Settings['Install']->Port,
            '{log_path}' => $this->Settings['Install']->LogPath,
            '{query_path}' => $this->Settings['Install']->QueryPath,
            '{PID_path}' => $this->Settings['Install']->PIDPath,
            '{data_path}' => $this->Settings['Install']->DataPath,
            '{db_prefix}' => $DBPrefix,
            '{ss_prefix}' => $this->Settings['Install']->Prefix, //prefix for shpinx configuration names
            );
        /* '{tag_select}' => $Tags == TRUE ? '(SELECT td.TagID as TagName\
                FROM '.$DBPrefix.'TagDiscussion td\
                WHERE pic.GDN_Comment.DiscussionID = td.DiscussionID),\\' : '\\',*/
        $ReWritedContent = str_replace(array_keys($Search), $Search, $Template);
        //echo nl2br($ReWritedContent); die;
        return $ReWritedContent;
    }

    private function _ReWriteSphinxConf($OrgFile) {
//        if (!is_readable($OrgFile)) {
//            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, "(Permissions) Unable to Read config file at: $OrgFile");
//        }
        $Template = file_get_contents($OrgFile);       //get text from file
        $ReWritedContent = $this->_GenerateConfContent($Template);  //replace variables into sphinx.conf
        parent::Update(SS_SUCCESS, 'ConfText', $ReWritedContent); // Save this text

//        try {
//            file_put_contents($FinalFile, $ReWritedContent);
//        } catch (Exception $e) {
//            //get just the exception error
//            $ErrorLen = strpos($e, 'Stack trace:', 0);
//            $Error = substr($e, 0, $ErrorLen);
//            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, $Error);
//        }
    }

    public function SetupCron() {
        $CronFolder = PATH_PLUGINS . DS . 'SphinxSearchLite' . DS . 'cron';
        $InstallWizard = new SphinxSearchInstallWizard(null, null); //bad practice @todo fix this
        $Search = array(
            '{path_to_indexer}' => $this->Settings['Install']->IndexerPath,
            '{path_to_php}' => $InstallWizard->RunTypeCommand('', 'php'), //find where PHP is installed
            '{path_to_config}' => $this->Settings['Install']->ConfPath,
            '{path_to_cron}' => $CronFolder,
            '{DS}' => DS,
            '{index_prefix}' => $this->Settings['Install']->Prefix, //prefix for shpinx configuration names
        );

        $MainTemplate = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearchLite' . DS . 'assests' . DS . 'cron.reindex.main.php.tpl');
        $DeltaTemplate = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearchLite' . DS . 'assests' . DS . 'cron.reindex.delta.php.tpl');
        $StatsTemplate = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearchLite' . DS . 'assests' . DS . 'cron.reindex.stats.php.tpl');

        $ReWritedMain = str_replace(array_keys($Search), $Search, $MainTemplate);
        $ReWritedDelta = str_replace(array_keys($Search), $Search, $DeltaTemplate);
        $ReWritedStats = str_replace(array_keys($Search), $Search, $StatsTemplate);

        //check if cron folder is good to go
        if (!file_exists($CronFolder)) {
            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, "Cron folder not found at: $CronFolder");
        }
        if (!IsWritable($CronFolder)) {
            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, "This location is not writable: $CronFolder");
        }

        try {
            if (!file_put_contents($CronFolder . DS . 'cron.reindex.main.php', $ReWritedMain)) {
                parent::Update(SS_FATAL_ERROR, FALSE, FALSE, "(Permissions) Error writing cron file: $MainTemplate");
            }
            if (!file_put_contents($CronFolder . DS . 'cron.reindex.delta.php', $ReWritedDelta)) {
                parent::Update(SS_FATAL_ERROR, FALSE, FALSE, "(Permissions) Error writing cron file: $DeltaTemplate");
            }
            if (!file_put_contents($CronFolder . DS . 'cron.reindex.stats.php', $ReWritedStats)) {
                parent::Update(SS_FATAL_ERROR, FALSE, FALSE, "(Permissions) Error writing cron file: $StatsTemplate");
            }
        } catch (Exception $e) {
            //get just the exception error
            $ErrorLen = strpos($e, 'Stack trace:', 0);
            $Error = substr($e, 0, $ErrorLen);
            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, $Error);
        }
    }

    public function InstallWriteConfig() {
        //copy our config to new installation
        $SphinxConfOrgPath = PATH_PLUGINS . DS . 'SphinxSearchLite' . DS . 'assests' . DS . 'sphinx.conf.tpl'; //local copy that ships with plugin
        $SphinxConfInstallPath = $this->Settings['Install']->ConfPath; //where sphinx is installed

//        try {
//            $CopySuccess = copy($SphinxConfOrgPath, $SphinxConfInstallPath);
//        } catch (Exception $e) {
//            //get just the exception error
//            $ErrorLen = strpos($e, 'Stack trace:', 0);
//            $Error = substr($e, 0, $ErrorLen);
//            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, $Error);
//        }
//        if (!$CopySuccess) {
//            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, '(Permissions) Failed to copy: ' . $SphinxConfOrgPath . ' to: ' . $SphinxConfInstallPath);
//        }
        //rewrite pre defined variables in config file to their values
        $SphinxConfOrgPath = PATH_PLUGINS . DS . 'SphinxSearchLite' . DS . 'assests' . DS . 'sphinx.conf.tpl'; //local copy that ships with plugin
        $this->_ReWriteSphinxConf($SphinxConfOrgPath);
    }

    /**
     * this is called after the poller has successfully installed
     */
    public function SaveLocations() {
        //complete by saving settings
        parent::Update(SS_SUCCESS, 'IndexerPath', $this->Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'bin' . DS . 'indexer');
        parent::Update(SS_SUCCESS, 'SearchdPath', $this->Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'bin' . DS . 'searchd');
        parent::Update(SS_SUCCESS, 'ConfPath', $this->Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'etc' . DS . 'sphinx.conf');
    }

}