<?php

class SphinxSearchGeneral {

    public static function isRunning($PID) {
        try {
            $Result = shell_exec(sprintf("ps %d", $PID));
            if (count(preg_split("/\n/", $Result)) > 2) {
                return true;
            }
        } catch (Exception $e) {

        }

        return false;
    }

    public static function ClearLogFiles() {
        //first empty output file
        file_put_contents(OUTPUT_FILE, '');
        //Output PID file
        file_put_contents(PID_FILE, '');
        //empty error file
        file_put_contents(ERROR_FILE, '');
    }

    public static function RunCommand($Command, $Path, $PrefixMsg = '', $Background = FALSE) {
        $pipes = array();
        if ($Background) {
            SaveToConfig('Plugin.SphinxSearch.PIDBackgroundWorker', $Command);
            self::ClearLogFiles();
            try {
                chdir($Path);
                exec(sprintf("%s > %s 2>%s & echo $! >> %s", $Command, OUTPUT_FILE, ERROR_FILE, PID_FILE));
            } catch (Exception $e) {
                return $e;
            }


            return FALSE;  //return successfully
        } else {
            $descriptorspec = array(
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w'),
            );


            $resource = proc_open($Command, $descriptorspec, $pipes, $Path);

            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }

            $status = trim(proc_close($resource));
            if ($status) {
                $Error = $stderr . '<br/><br/>' .
                        '<b>' . $PrefixMsg . ', try to run this command manually in Terminal: </b><br/>' .
                        $Command . '<br/><br/> at directory: <br/>' .
                        $Path .
                        '<br/><br/>Try running it with sudo if it doesn\'t work. Check that apache has the correct permissions to read/write files on your
                            webserver
                    <br/><b>Terminal Output:</b><br/>' .
                        $stdout;
                $Error = str_replace('%', '%%', $Error);//THIS IS IMPORTANT! Must escape strings in case of percent signs since this is eventually used
                                                 //ins sprintf in gdn_form
                return $Error;
            }
            else
                return FALSE;
        }
    }




}