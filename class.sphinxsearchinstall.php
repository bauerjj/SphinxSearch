<?php

class SphinxSearchInstall extends SphinxObservable {

    private $LatestSphinxFileName = 'sphinx-2.0.6-release.tar.gz';    //update this on a need-to basis
    private $Settings = array();

    public function __construct($Config) {
        $this->Settings = $Config;
        parent::__construct();
    }

    public function NewSettings($Settings) {
        $this->Settings = $Settings;
    }

    public function CheckDebugFiles() {
        //check if running in background - if so, requrie that these files are writable for poller
        $this->_CheckPath(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'install' . DS . 'output.txt', FALSE, TRUE);
        $this->_CheckPath(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'install' . DS . 'error.txt', FALSE, TRUE);
        $this->_CheckPath(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'install' . DS . 'pid.txt', FALSE, TRUE);
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
            '{assests_path}' => PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS, //for stopwords.txt
            '{searchd_port}' => $this->Settings['Install']->Port,
            '{log_path}' => $this->Settings['Install']->LogPath,
            '{query_path}' => $this->Settings['Install']->QueryPath,
            '{PID_path}' => $this->Settings['Install']->PIDPath,
            '{data_path}' => $this->Settings['Install']->DataPath,
            '{mem_limit}' => $this->Settings['Admin']->MemLimit,
            '{db_prefix}' => $DBPrefix,
            '{ss_prefix}' => $this->Settings['Install']->Prefix, //prefix for shpinx configuration names
            //searchd settings
            '{read_timeout}' => $this->Settings['Admin']->ReadTimeout,
            '{client_timeout}' => $this->Settings['Admin']->ClientTimeout,
            '{max_children}' => $this->Settings['Admin']->MaxChildren,
            '{max_matches}' => $this->Settings['Admin']->MaxMatches,
            '{read_buffer}' => $this->Settings['Admin']->ReadBuffer,
            '{workers}' => $this->Settings['Admin']->Workers,
            '{thread_stack}' => $this->Settings['Admin']->ThreadStack,
            '{expansion_limit}' => $this->Settings['Admin']->ExpansionLimit,
            '{prefork_rotation_throttle}' => $this->Settings['Admin']->PreforkRotationThrottle,
            //indexer settings
            '{mem_limit}' => $this->Settings['Admin']->MemLimit,
            '{max_iops}' => $this->Settings['Admin']->MaxIOps,
            '{max_iosize}' => $this->Settings['Admin']->MaxIOSize,
            '{write_buffer}' => $this->Settings['Admin']->WriteBuffer,
            '{max_file_field_buffer}' => $this->Settings['Admin']->MaxFileBuffer,
            //index settings
            '{morphology}' => $this->Settings['Admin']->Morphology,
            '{dict}' => $this->Settings['Admin']->Dict,
            '{min_stemming_len}' => $this->Settings['Admin']->MinStemmingLen,
            '{stopwords}' => $this->Settings['Admin']->StopWordsEnable == TRUE ? STOP_WORDS_FILE : '',
            '{wordforms}' => $this->Settings['Admin']->WordFormsEnable == TRUE ? WORD_FORMS_FILE : '',
            '{min_word_len}' => $this->Settings['Admin']->MinWordIndexLen,
            '{min_prefix_len}' => $this->Settings['Admin']->MinPrefixLen,
            '{min_infix_len}' => $this->Settings['Admin']->MinInfixLen,
            '{enable_star}' => $this->Settings['Admin']->StarEnable == TRUE ? 1 : 0,
            '{ngram_len}' => $this->Settings['Admin']->NGramLen,
            '{html_strip}' => $this->Settings['Admin']->HtmlStripEnable == TRUE ? 1 : 0,
            '{ondisk_dict}' => $this->Settings['Admin']->OnDiskDictEnable == TRUE ? 1 : 0,
            '{inplace_enable}' => $this->Settings['Admin']->InPlaceEnable == TRUE ? 1 : 0,
            '{expand_keywords}' => $this->Settings['Admin']->ExpandKeywordsEnable == TRUE ? 1 : 0,
            '{rt_mem_limit}' => $this->Settings['Admin']->RTMemLimit == 'none' ? '' : $this->Settings['Admin']->RTMemLimit, //default is empty!

            '{tag_select}' => $Tags == TRUE ? 'td.TagID as TagName,\\' : '\\',
            '{tag_join}' => $Tags == TRUE ? 'LEFT OUTER JOIN '.$DBPrefix.'TagDiscussion as td ON d.DiscussionID = td.DiscussionID\\' : '\\',
            '{tag_attr}' => $Tags == TRUE ? 'sql_attr_multi = uint TagID from query; SELECT t.DiscussionID, t.TagID FROM '.$DBPrefix.'TagDiscussion as t' : ''
            );
        /* '{tag_select}' => $Tags == TRUE ? '(SELECT td.TagID as TagName\
                FROM '.$DBPrefix.'TagDiscussion td\
                WHERE pic.GDN_Comment.DiscussionID = td.DiscussionID),\\' : '\\',*/
        $ReWritedContent = str_replace(array_keys($Search), $Search, $Template);
        //echo nl2br($ReWritedContent); die;
        return $ReWritedContent;
    }

    private function _ReWriteSphinxConf($OrgFile, $FinalFile) {
        if (!is_readable($OrgFile)) {
            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, "(Permissions) Unable to Read config file at: $OrgFile");
        }
        $Template = file_get_contents($OrgFile);       //get text from file
        $ReWritedContent = $this->_GenerateConfContent($Template);  //replace variables into sphinx.conf
        try {
            file_put_contents($FinalFile, $ReWritedContent);
        } catch (Exception $e) {
            //get just the exception error
            $ErrorLen = strpos($e, 'Stack trace:', 0);
            $Error = substr($e, 0, $ErrorLen);
            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, $Error);
        }
    }

    public function SetupCron() {
        $CronFolder = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'cron';
        $InstallWizard = new SphinxSearchInstallWizard(null, null); //bad practice @todo fix this
        $Search = array(
            '{path_to_indexer}' => $this->Settings['Install']->IndexerPath,
            '{path_to_php}' => $InstallWizard->RunTypeCommand('', 'php'), //find where PHP is installed
            '{path_to_config}' => $this->Settings['Install']->ConfPath,
            '{path_to_cron}' => $CronFolder,
            '{DS}' => DS,
            '{index_prefix}' => $this->Settings['Install']->Prefix, //prefix for shpinx configuration names
        );

        $MainTemplate = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'cron.reindex.main.php.tpl');
        $DeltaTemplate = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'cron.reindex.delta.php.tpl');
        $StatsTemplate = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'cron.reindex.stats.php.tpl');

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

    public function InstallConfigure($SphinxInstallPath, $Dir, $Background = FALSE) {
        $Command = "./configure --with-mysql --prefix=$SphinxInstallPath";
        SphinxSearchGeneral::RunCommand($Command, $Dir, 'Installation: Sphinx installation error with configure', $Background);
    }

    public function InstallMake($Dir, $Background = FALSE) {
        $Command = 'make install';
        SphinxSearchGeneral::RunCommand($Command, $Dir, 'Installation: Sphinx installation error with make', $Background);
    }

    public function InstallMakeInstall($Dir, $Background = FALSE) {
        $Command = 'make install';
        SphinxSearchGeneral::RunCommand($Command, $Dir, 'Installation: Sphinx installation error with make install', $Background);
    }

    public function InstallWriteConfig() {
        //copy our config to new installation
        $SphinxConfOrgPath = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'sphinx.conf.tpl'; //local copy that ships with plugin
        $SphinxConfInstallPath = $this->Settings['Install']->ConfPath; //where sphinx is installed

        try {
            $CopySuccess = copy($SphinxConfOrgPath, $SphinxConfInstallPath);
        } catch (Exception $e) {
            //get just the exception error
            $ErrorLen = strpos($e, 'Stack trace:', 0);
            $Error = substr($e, 0, $ErrorLen);
            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, $Error);
        }
        if (!$CopySuccess) {
            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, '(Permissions) Failed to copy: ' . $SphinxConfOrgPath . ' to: ' . $SphinxConfInstallPath);
        }
        //rewrite pre defined variables in config file to their values
        $SphinxConfOrgPath = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'sphinx.conf.tpl'; //local copy that ships with plugin
        $this->_ReWriteSphinxConf($SphinxConfOrgPath, $SphinxConfInstallPath);
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

    public function InstallExtract($Background) {
        set_time_limit(0);    //this install may take a while on some machines (>5 minutes...PHP defaults to max of 30 seconds)
        $InstallPath = $this->Settings['Install']->InstallPath;
        $SphinxExtractPath = $InstallPath; //extract sphinx in here
        $SphinxInstallPath = $SphinxExtractPath . DS . 'sphinx'; //put sphinx binaries in here
        $this->_CheckPath($InstallPath, TRUE, TRUE);
        try {
            if (is_dir($SphinxInstallPath))
                $this->_rrmdir($SphinxInstallPath); //delete contents of an already existing sphinx installation in case we are updating
            if (!mkdir($SphinxInstallPath, 0777)) {
                parent::Update(SS_FATAL_ERROR, FALSE, FALSE, "Installation: Unable to create directory: $InstallPath");
            }
        } catch (Exception $e) {
            //get just the exception error
            $ErrorLen = strpos($e, 'Stack trace:', 0);
            $Error = substr($e, 0, $ErrorLen);
            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, $Error);
        }

        $this->_CheckPath($SphinxInstallPath, TRUE, TRUE); //check the new folder

        try {
            $CopySuccess = copy(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . $this->LatestSphinxFileName, $InstallPath . DS . $this->LatestSphinxFileName);
        } catch (Exception $e) {
            //get just the exception error
            $ErrorLen = strpos($e, 'Stack trace:', 0);
            $Error = substr($e, 0, $ErrorLen);
            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, $Error);
        }
        if (!$CopySuccess) {
            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, '(Permissions) Failed to copy: ' . $this->LatestSphinxFileName . ' to: ' . $InstallPath);
        }

        //Now attempt to extract the Sphinx archive
        $Command = "tar xzf " . $InstallPath . DS . $this->LatestSphinxFileName . " -C $InstallPath";
        if ($Error = SphinxSearchGeneral::RunCommand($Command, $InstallPath, 'Installation: Sphinx installation error')) //don't run in background!
            parent::Update(SS_FATAL_ERROR, FALSE, FALSE, '(Permissions) Failed to copy: ' . $Error);
        $InsideDir = $InstallPath . DS . str_replace('.tar.gz', '', $this->LatestSphinxFileName); //newley created folder from extraction

        /*         * *******************************************************************
         *
         *
         *
         * From here on down, run the following in the background if so desired
         *
         * ./configure
         * make
         * make install
         *
         *
         * ********************************************************************* */

        if ($Background) {
            //save this in settings to retrieve during background commands
            parent::Update(SS_SUCCESS, 'InsideDir', $InsideDir); //    /srv/http/mcuhq/vanilla/plugins/SphinxSearch/install/sphinx-2.0.4-release
            parent::Update(SS_SUCCESS, 'InstallPath', $InstallPath); //  /srv/http/mcuhq/vanilla/plugins/SphinxSearch/install
            parent::Update(SS_SUCCESS, 'Task', 'Start');
        } else { //don't do this in the background
            if ($Error = $this->InstallConfigure($InstallPath . DS . 'sphinx', $InsideDir, FALSE))
                parent::Update(SS_FATAL_ERROR, FALSE, FALSE, 'Failed during ./configure: ' . $Error);
            if ($Error = $this->InstallMake($InsideDir, FALSE))
                parent::Update(SS_FATAL_ERROR, FALSE, FALSE, 'Failed during make: ' . $Error);
            if ($Error = $this->InstallMakeInstall($InsideDir, FALSE))
                parent::Update(SS_FATAL_ERROR, FALSE, FALSE, 'Failed during make install ' . $Error);
        }
    }

}