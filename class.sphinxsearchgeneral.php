<?php

class SphinxSearchGeneral{


    public static function RunCommand($Command, $Path, $PrefixMsg = '') {
        $descriptorspec = array(
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );

        $pipes = array();
        $resource = proc_open($Command, $descriptorspec, $pipes, $Path);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $status = trim(proc_close($resource));
        if ($status) {
            return ($stderr . '<br/><br/>' .
                    '<b>'.$PrefixMsg.', try to run this command manually in Terminal: </b><br/>' .
                    $Command . '<br/><br/> at directory: <br/>' .
                    $Path .
                    '<br/><br/>Try running it with sudo if it doesn\'t work
                    <br/><b>Terminal Output:</b><br/>'.
                        $stdout);
        }
        else
            return FALSE;
    }
    /**
     * Simply checks the existense of searchd/indexer/sphinx.conf
     */
    public static function ValidateInstall(){
        if(!file_exists(C('Plugin.SphinxSearch.IndexerPath')) ||
                !file_exists(C('Plugin.SphinxSearch.SearchdPath')) ||
                !file_exists(C('Plugin.SphinxSearch.ConfPath'))
                )
            return T('Must reinstall sphinx...cannot locate indexer/searchd/configuration'); //fail
        else
            return FALSE; //SUCCESS
    }
}