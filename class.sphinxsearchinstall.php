<?php

class SphinxSearchInstall extends SphinxObservable {

    private $_LatestSphinxFileName = 'sphinx-2.0.4-release.tar.gz';    //update this on a need-to basis
    private $_Settings = array();

    public function __construct($Config) {
        $this->_Settings = $Config;
        parent::__construct();
    }

    private function _CheckPath($InstallPath, $CheckDir, $CheckWritable) {
        if ($CheckDir) {
            if (!is_dir($InstallPath)) {
                parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, 'This location is not a directory: ' . $InstallPath);
            }
        }
        if (1) {
            if (!is_readable($InstallPath)) {
                parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, 'This location is not readable: ' . $InstallPath);
            }
        }
        if ($CheckWritable) {
            if (!is_writable($InstallPath)) {
                parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, 'This location is not writable: ' . $InstallPath . ' <br/>Must CHMOD 777 this directory');
            }
        }
    }

    private function _IsEmptyDir($dir) {
        if (($files = @scandir($dir)) && count($files) <= 2) {
            return TRUE;
        }
        return FALSE;
    }

    private function _Make($Path) {

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

        $Search = array(
            '{sql_sock}' => $SQLSock,
            '{sql_host}' => $this->_Settings['Install']->Host,
            '{sql_user}' => C('Database.User'),
            '{sql_pass}' => C('Database.Password'),
            '{charset_type}' => C('Garden.Charset', 'utf-8'),
            '{charset_type_mysql}' => C('Database.CharacterEncoding', 'utf8'), //MySQL omits the hyphen
            '{install_path}' => $this->_Settings['Install']->InstallPath,
            '{DS}' => DS,
            '{searchd_port}' => $this->_Settings['Install']->Port,

            '{max_matches}' => $this->_Settings['Admin']->MaxMatches,
            '{mem_limit}' => $this->_Settings['Admin']->MemLimit,
            '{sphinx_dir}' => $this->_Settings['Install']->InstallPath, //@todo redundant with @installpath above?
            '{db_prefix}' => C('Database.Name') . '.' . C('Database.Prefix', 'GDN_'), //join these 2
            '{ss_prefix}' => $this->_Settings['Install']->Prefix, //prefix for shpinx configuration names
        );

        $ReWritedContent = str_replace(array_keys($Search), $Search, $Template);
        return $ReWritedContent;
    }

    private function _ReWriteSphinxConf($OrgFile, $FinalFile) {
        if (!is_readable($OrgFile) || !is_writable($OrgFile)) {
            parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, "Unable to Read/Write config file at: $OrgFile");
        }
        $Template = file_get_contents($OrgFile);       //get text from file
        $ReWritedContent = $this->_GenerateConfContent($Template);  //replace variables into sphinx.conf

        try {
            file_put_contents($FinalFile, $ReWritedContent);
        } catch (Exception $e) {
            //get just the exception error
            $ErrorLen = strpos($e, 'Stack trace:', 0);
            $Error = substr($e, 0, $ErrorLen);
            parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, $Error);
        }
    }

    public function SetupCron($IndexerPath, $ConfPath, $Prefix) {
        $CronFolder = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'cron';
        $InstallWizard = new SphinxSearchInstallWizard(null, null); //bad practice @todo fix this
        $Search = array(
            '{path_to_indexer}' => $IndexerPath,
            '{path_to_php}' => $InstallWizard->RunTypeCommand('', 'php'), //find where PHP is installed
            '{path_to_config}' => $ConfPath,
            '{path_to_cron}' => $CronFolder,
            '{index_prefix}' => $Prefix, //prefix for shpinx configuration names
        );


        $DeltaTemplate = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'cron.reindex.delta.php.tpl');
        $MainTemplate = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'cron.reindex.main.php.tpl');

        $ReWritedDelta = str_replace(array_keys($Search), $Search, $DeltaTemplate);
        $ReWritedMain = str_replace(array_keys($Search), $Search, $MainTemplate);

        //check if cron folder is good to go
        if (!file_exists($CronFolder)) {
            parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, "Cron folder not found at: $CronFolder");
        }
        if (!is_writable($CronFolder)) {
            parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, "This location is not writable: $CronFolder");
        }

        try {
            if (!file_put_contents($CronFolder . DS . 'cron.reindex.delta.php', $ReWritedDelta)) {
                parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, "Error writing cron file: $DeltaTemplate");
            }

            if (!file_put_contents($CronFolder . DS . 'cron.reindex.main.php', $ReWritedMain)) {
                parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, "Error writing cron file: $MainTemplate");
            }
        } catch (Exception $e) {
            //get just the exception error
            $ErrorLen = strpos($e, 'Stack trace:', 0);
            $Error = substr($e, 0, $ErrorLen);
            parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, $Error);
        }
    }

    public function InstallConfigure($SphinxInstallPath, $Dir) {
        $Command = "./configure --with-mysql --prefix=$SphinxInstallPath";
        SphinxSearchGeneral::RunCommand($Command, $Dir, 'Installation: Sphinx installation error with configure', TRUE); //run in background
    }

    public function InstallMake($Dir) {
        $Command = 'make install';
        SphinxSearchGeneral::RunCommand($Command, $Dir, 'Installation: Sphinx installation error with make', TRUE); //run in background
    }

    public function InstallMakeInstall($Dir) {
        $Command = 'make install';
        SphinxSearchGeneral::RunCommand($Command, $Dir, 'Installation: Sphinx installation error with make install', TRUE); //run in background
    }

    public function InstallWriteConfig($InstallPath) {
        //copy our config to new installation
        $SphinxConfOrgPath = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'sphinx.conf.tpl'; //local copy that ships with plugin
        $SphinxConfInstallPath = $InstallPath . DS . 'sphinx' . DS . 'etc' . DS . 'sphinx.conf'; //where sphinx is installed

        try {
            $CopySuccess = copy($SphinxConfOrgPath, $SphinxConfInstallPath);
        } catch (Exception $e) {
            //get just the exception error
            $ErrorLen = strpos($e, 'Stack trace:', 0);
            $Error = substr($e, 0, $ErrorLen);
            parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, $Error);
        }
        if (!$CopySuccess) {
            parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, 'Failed to copy: ' . $SphinxConfOrgPath . ' to: ' . $SphinxConfInstallPath);
        }
        //rewrite pre defined variables in config file to their values
        $SphinxConfOrgPath = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'sphinx.conf.tpl'; //local copy that ships with plugin
        $this->_ReWriteSphinxConf($SphinxConfOrgPath, $SphinxConfInstallPath);
    }

    public function SaveLocations() {
        //complete by saving settings
        parent::Update(SS_SUCCESS, 'IndexerPath', $this->_Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'bin' . DS . 'indexer');
        parent::Update(SS_SUCCESS, 'SearchdPath', $this->_Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'bin' . DS . 'searchd');
        parent::Update(SS_SUCCESS, 'ConfPath', $this->_Settings['Install']->InstallPath . DS . 'sphinx' . DS . 'etc' . DS . 'sphinx.conf');
    }

    public function InstallExtract() {
        set_time_limit(0);    //this install may take a while on some machines (>5 minutes...PHP defaults to max of 30 seconds)
        $InstallPath = $this->_Settings['Install']->InstallPath;
        $SphinxExtractPath = $InstallPath; //extract sphinx in here
        $SphinxInstallPath = $SphinxExtractPath . DS . 'sphinx'; //put sphinx binaries in here
        $this->_CheckPath($InstallPath, TRUE, TRUE);
        try {
            if (is_dir($SphinxInstallPath))
                $this->_rrmdir($SphinxInstallPath); //delete contents of an already existing sphinx installation in case we are updating
            if (!mkdir($SphinxInstallPath, 0777)) {
                parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, "Installation: Unable to create directory: $InstallPath");
            }
        } catch (Exception $e) {
            //get just the exception error
            $ErrorLen = strpos($e, 'Stack trace:', 0);
            $Error = substr($e, 0, $ErrorLen);
            parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, $Error);
        }

        $this->_CheckPath($SphinxInstallPath, TRUE, TRUE); //check the new folder

        try {
            $CopySuccess = copy(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . $this->_LatestSphinxFileName, $InstallPath . DS . $this->_LatestSphinxFileName);
        } catch (Exception $e) {
            //get just the exception error
            $ErrorLen = strpos($e, 'Stack trace:', 0);
            $Error = substr($e, 0, $ErrorLen);
            parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, $Error);
        }
        if (!$CopySuccess) {
            parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, 'Failed to copy: ' . $this->_LatestSphinxFileName . ' to: ' . $InstallPath);
        }

        //Now attempt to extract the Sphinx archive
        $Command = "tar xzf " . $InstallPath . DS . $this->_LatestSphinxFileName . " -C $InstallPath";
        if ($Error = SphinxSearchGeneral::RunCommand($Command, $InstallPath, 'Installation: Sphinx installation error')) //don't run in background!
            parent::Update(SPHINX_FATAL_ERROR, FALSE, FALSE, 'Failed to copy: ' . $Error);
        $InsideDir = $InstallPath . DS . str_replace('.tar.gz', '', $this->_LatestSphinxFileName); //newley created folder from extraction
        //save this in settings to retrieve during background commands
        SaveToConfig('Plugin.SphinxSearch.InsideDir', $InsideDir); //    /srv/http/mcuhq/vanilla/plugins/SphinxSearch/install/sphinx-2.0.4-release
        SaveToConfig('Plugin.SphinxSearch.InstallPath', $InstallPath); //  /srv/http/mcuhq/vanilla/plugins/SphinxSearch/install
        SaveToConfig('Plugin.SphinxSearch.Task', 'Start');

        /*         * *******************************************************************
         *
         *
         *
         * From here on down, run the following in the background
         *
         * ./configure
         * make
         * make install
         *
         *
         * ********************************************************************* */
    }

}