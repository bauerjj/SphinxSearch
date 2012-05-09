<?php

class SphinxSearchInstall {

    var $LatestSphinxFileName = 'sphinx-2.0.4-release.tar.gz';    //update this on a need-to basis
    var $Errors = FALSE; //keeps track of errors

    public function __construct() {

    }

    private function _CheckPath($InstallPath, $CheckDir, $CheckWritable) {
        if ($CheckDir) {
            if (!is_dir($InstallPath)) {
                return (T('This location is not a directory: ' . $InstallPath));
            }
        }
        if (1) {
            if (!is_readable($InstallPath)) {
                return (T('This location is not readable: ' . $InstallPath . ''));
            }
        }
        if ($CheckWritable) {
            if (!is_writable($InstallPath)) {
                return (T('This location is not writable: ' . $InstallPath . ' <br/>Must CHMOD 777 this directory'));
            }
        }

        return FALSE; //SUCCESS
    }

    private function _CheckSearchd($Path) {
        if (!file_exists($Path . DS . 'bin' . DS . 'searchd')) {
            return ("Installation: searchd daemon at: ({$Path}/bin/searchd) was not found.");
        }
        else
            return FALSE; //SUCCESS
    }

    private function _CheckIndexer($Path) {
        //now check if the sphinx installation was successfull by seeing if indexer and searchd exist
        if (!file_exists($Path . DS . 'bin' . DS . 'indexer')) {
            return ("Installation: indexer at: ({$Path}/bin/searchd) was not found.");
        }
        else
            return FALSE; //SUCCESS
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
            '{sql_host}' => C('Database.Host'),
            '{sql_user}' => C('Database.User'),
            '{sql_pass}' => C('Database.Password'),
            '{charset_type}' => C('Database.CharacterEncoding', 'utf-8'),
            '{charset_type_mysql}' => C('Garden.Charset', 'utf8'), //MySQL omits the hyphen
            '{install_path}' => C('Plugin.SphinxSearch.InstallPath'),
            '{DS}'=>DS,
            '{searchd_port}' => C('Plugin.SphinxSearch.Port', 9312),
            '{max_matches}' => C('Plugin.SphinxSearch.MaxMatches', 1000),
            '{mem_limit}' => C('Plugin.SphinxSearch.MemLimit', 32),
            '{sphinx_dir}' => C('Plugin.SphinxSearch.InstallPath', 'testttt'),
            '{db_prefix}' => C('Database.Prefix', 'GDN_') . C('Database.Name') . '.', //join these 2
            '{ss_prefix}' => C('Plugin.SphinxSearch.Prefix', 'vss_'), //prefix for shpinx configuration names
        );

        $ReWritedContent = str_replace(array_keys($Search), $Search, $Template);
        //echo $ReWritedContent; die;
        return $ReWritedContent;
    }

    private function _ReWriteSphinxConf($OrgFile, $FinalFile) {
        if (!is_readable($OrgFile) || !is_writable($OrgFile)) {
            return ("Unable to Read/Write config file at: $OrgFile");
        }
        $Template = file_get_contents($OrgFile);       //get text from file
        $ReWritedContent = $this->_GenerateConfContent($Template);  //replace variables into sphinx.conf

        try {
            file_put_contents($FinalFile, $ReWritedContent);
        } catch (Exception $e) {
            //get just the exception error
            $ErrorLen = strpos($e, 'Stack trace:', 0);
            $Error = substr($e, 0, $ErrorLen);
            return ($Error);
        }
        return FALSE; //sucess
    }

    public function SetupCron() {
        $CronFolder = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'cron';
        $InstallWizard = new SphinxSearchInstallWizard(null, null); //bad practice @todo fix this
        $Search = array(
            '{path_to_indexer}' => C('Plugin.SphinxSearch.IndexerPath'),
            '{path_to_php' => $InstallWizard->RunTypeCommand('', 'php'), //find where PHP is installed
            '{path_to_config}' => C('Plugin.SphinxSearch.ConfPath', 'testtt'),
            '{path_to_cron}' => $CronFolder,
            '{index_prefix}' => C('Plugin.SphinxSearch.Prefix', 'vss_'), //prefix for shpinx configuration names
        );


        $DeltaTemplate = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'cron.reindex.delta.php.tpl');
        $MainTemplate = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'cron.reindex.main.php.tpl');

        $ReWritedDelta = str_replace(array_keys($Search), $Search, $DeltaTemplate);
        $ReWritedMain = str_replace(array_keys($Search), $Search, $MainTemplate);

        //check if cron folder is good to go
        if (!file_exists($CronFolder)) {
            return ("Cron folder not found at: $CronFolder");
        }
        if (!is_writable($CronFolder)) {
            return ("This location is not writable: $CronFolder");
        }

        try {
            if (!file_put_contents($CronFolder . DS . 'cron.reindex.delta.php', $ReWritedDelta)) {
                return ("Error writing cron file: $DeltaTemplate");
            }

            if (!file_put_contents($CronFolder . DS . 'cron.reindex.main.php', $ReWritedMain)) {
                return ("Error writing cron file: $MainTemplate");
            }
        } catch (Exception $e) {
            //get just the exception error
            $ErrorLen = strpos($e, 'Stack trace:', 0);
            $Error = substr($e, 0, $ErrorLen);
            return ($Error);
        }

        return FALSE; //SUCCESS
    }

    public function Install() {
        set_time_limit(0);    //this install may take a while on some machines (>20 minutes...PHP defaults to max of 30 seconds)

//        $InstallPath = C('Plugin.SphinxSearch.InstallPath', '');
//        $SphinxExtractPath = $InstallPath; //extract sphinx in here
//        $SphinxInstallPath = $SphinxExtractPath . DS . 'sphinx'; //put sphinx binaries in here
//        if ($Error = $this->_CheckPath($InstallPath, TRUE, TRUE))
//            return $Error;
//        try {
//            if (is_dir($SphinxInstallPath))
//                $this->_rrmdir($SphinxInstallPath); //delete contents of an already existing sphinx installation in case we are updating
//            if (!mkdir($SphinxInstallPath, 0777)) {
//                return ("Installation: Unable to create directory: $InstallPath");
//            }
//        } catch (Exception $e) {
//            //get just the exception error
//            $ErrorLen = strpos($e, 'Stack trace:', 0);
//            $Error = substr($e, 0, $ErrorLen);
//            return ($Error);
//        }
//
//        if ($Error = $this->_CheckPath($SphinxInstallPath, TRUE, TRUE)) //check the new folder
//            return $Error;
//
//
//        try {
//            $CopySuccess = copy(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . $this->LatestSphinxFileName, $InstallPath . DS . $this->LatestSphinxFileName);
//        } catch (Exception $e) {
//            //get just the exception error
//            $ErrorLen = strpos($e, 'Stack trace:', 0);
//            $Error = substr($e, 0, $ErrorLen);
//            return $Error;
//        }
//        if (!$CopySuccess) {
//            return (T('Failed to copy: ' . $this->LatestSphinxFileName . ' to: ' . $InstallPath));
//        }
//        //Now attempt to extract the Sphinx archive
//        $Command = "tar xzf " . $InstallPath . DS . $this->LatestSphinxFileName . " -C $InstallPath";
//        if ($Error = SphinxSearchGeneral::RunCommand($Command, $InstallPath, 'Installation: Sphinx installation error'))
//            return $Error;
//        $InsideDir = $InstallPath . DS . str_replace('.tar.gz', '', $this->LatestSphinxFileName); //newley created folder from extraction
//        //Now do the following:
//        // ./configure
//        //make & make install
//        //configure
//        $Command = "./configure --with-mysql --prefix=$SphinxInstallPath";
//        if ($Error = SphinxSearchGeneral::RunCommand($Command, $InsideDir, 'Installation: Sphinx installation error'))
//            return $Error; //error has occured...exiting installer
//
//
//
//
////making - this could take a lonnnnnnng time (20 minutes)
//        $Command = 'make';
//        if ($Error = SphinxSearchGeneral::RunCommand($Command, $InsideDir, 'Installation: Sphinx installation error'))
//            return $Error; //error has occured...exiting installer
//
//
//
//
////make install
//        $Command = 'make install';
//        if ($Error = SphinxSearchGeneral::RunCommand($Command, $InsideDir, 'Installation: Sphinx installation error'))
//            return $Error; //error has occured...exiting installer
//
//        if ($Error = $this->_CheckIndexer($SphinxInstallPath))
//            return $Error;
//        if ($Error = $this->_CheckSearchd($SphinxInstallPath))
//            return $Error;
//
//        //////////////////
//        //copy our config to
//        //new installation
//        //////////////////
//        $SphinxConfOrgPath = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'sphinx.conf.tpl'; //local copy that ships with plugin
//        $SphinxConfInstallPath = $InstallPath . DS . 'sphinx' . DS . 'etc' . DS . 'sphinx.conf'; //where sphinx is installed
//
//        try {
//            $CopySuccess = copy($SphinxConfOrgPath, $SphinxConfInstallPath);
//        } catch (Exception $e) {
//            //get just the exception error
//            $ErrorLen = strpos($e, 'Stack trace:', 0);
//            $Error = substr($e, 0, $ErrorLen);
//            return ($Error);
//        }
//        if (!$CopySuccess) {
//            return (T('Failed to copy: ' . $SphinxConfOrgPath . ' to: ' . $SphinxConfInstallPath));
//        }

        //////////////////
        //rewrite pre defined
        //variables in config
        //file to their values
        //////////////////
        $SphinxConfOrgPath = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'assests' . DS . 'sphinx.conf.tpl'; //local copy that ships with plugin
        $SphinxConfFinalPath = C('Plugin.SphinxSearch.ConfPath'); //write to here
        if ($Error = $this->_ReWriteSphinxConf($SphinxConfOrgPath, $SphinxConfFinalPath))
            return $Error;

        if ($Error = $this->SetupCron())     //setup cron files
            return $Error;


        SaveToConfig('Plugin.SphinxSearch.Installed', TRUE);
        return FALSE;        //complete installation
    }

}