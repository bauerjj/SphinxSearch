<?php

if (!defined('APPLICATION'))
    exit();

class SphinxSettings {

    public function __construct() {
        $this->Debug = C('Plugin.SphinxSearch.Debug', TRUE);      //whether or not to show any searchd/indxer errors/warnings
        $this->HighLightTitles = C('Plugin.SphinxSearch.HighLightTitles', TRUE);
        $this->ReleatedThreadsLimit = C('Plugin.SphinxSearch.ReleatedThreadsLimit', 15);

        $this->BuildExcerpts = array(
            'before_match' => C('Plugin.SphinxSearch.BuildExcerpts.BeforeMatch', '<span class="SphinxExcerpts">'),
            'after_match' => C('Plugin.SphinxSearch.BuildExcerpts.BeforeMatch', '</span>'),
            'chunk_separator' => C('Plugin.SphinxSearch.BuildExcerpts.ChunkSeparator', '...'),
            'limit' => C('Plugin.SphinxSearch.BuildExcerpts.Limit', 100), //Maximum snippet size, in symbols (codepoints). Integer, default is 256.
            'around' => C('Plugin.SphinxSearch.BuildExcerpts.Around', 20), //words around the matched word
        );
    }

    public function Setup() {
        //Wizard steps
        SaveToConfig('Plugin.SphinxSearch.StartWizard',FALSE);
        SaveToConfig('Plugin.SphinxSearch.Connection',FALSE);
        SaveToConfig('Plugin.SphinxSearch.Detection',FALSE);
        SaveToConfig('Plugin.SphinxSearch.Detected',FALSE); //whether or not system detected existance of sphinx or not (FALSE if did not)

        SaveToConfig('Plugin.SphinxSearch.Running',FALSE); //searchd start/stopped
        SaveToConfig('Plugin.SphinxSearch.Installed',FALSE); //either found existing binaries or succesfull install


        //Status variables
        SaveToConfig('InsideDir',FALSE);
        SaveToConfig('InsidePath',FALSE);
        SaveToConfig('Task','Configure');

        //Install parameters
        SaveToConfig('Plugin.SphinxSearch.Host', 'localhost');
        SaveToConfig('Plugin.SphinxSearch.Port', 9312);
        SaveToConfig('Plugin.SphinxSearch.InstallPath', SPHINX_SEARCH_INSTALL_DIR); //path to sphinx install directory
        SaveToConfig('Plugin.SphinxSearch.IndexerPath', 'Not Detected'); //path to indexer - use this for config purposes!
        SaveToConfig('Plugin.SphinxSearch.SearchdPath', 'Not Detected'); //path to searchd - use this for config purposes!
        SaveToConfig('Plugin.SphinxSearch.ConfPath', 'Not Detected'); //path to searchd - use this for config purposes!
        SaveToConfig('Plugin.SphinxSearch.ManualIndexerPath', ''); //manual path to indexer
        SaveToConfig('Plugin.SphinxSearch.ManualSearchdPath', ''); //manual path to searchd
        SaveToConfig('Plugin.SphinxSearch.ManualConfPath', ''); //manual path to sphinx.conf



        //general search parameters
        SaveToConfig('Plugin.SphinxSearch.MinWordIndexLen', 3); //minimum characters to index a word
        SaveToConfig('Plugin.SphinxSearch.Prefix','vss_');


        //debug parameters
        SaveToConfig('Plugin.SphinxSearch.Timeout', 3312); //units of ms
        SaveToConfig('Plugin.SphinxSearch.RetriesCount', 50); //On temporary failures searchd will attempt up to $count retries per agent
        SaveToConfig('Plugin.SphinxSearch.RetriesDelay', 50); //$delay is the delay between the retries, in ms

        //BuildExcerpts
        SaveToConfig('Plugin.SphinxSearch.BuildExcerpts.BeforeMatch', '<span class="SphinxExcerpts">');
        SaveToConfig('Plugin.SphinxSearch.BuildExcerpts.AfterMatch', '</span>');
        SaveToConfig('Plugin.SphinxSearch.BuildExcerpts.ChunkSeparator', '...');
        SaveToConfig('Plugin.SphinxSearch.BuildExcerpts.Limit', 60);
        SaveToConfig('Plugin.SphinxSearch.BuildExcerpts.Around', 3);
        SaveToConfig('Plugin.SphinxSearch.MaxQueryTime', 2000); //units of ms





        //create shpinx table to tell the indexes what to index
        Gdn::Structure()
                ->Table('sph_counter')
                ->Column('counter_id', 'int', FALSE) //order matters here!
                ->Column('max_doc_id', 'int', FALSE)
                ->PrimaryKey('counter_id', 'int')
                ->Set();

        //create stats table
    }

    public function OnDisable() {
        RemoveFromConfig('Plugin.SphinxSearch.MaxQueryTime');
        RemoveFromConfig('Plugin.SphinxSearch.Host');
        RemoveFromConfig('Plugin.SphinxSearch.Port');
        RemoveFromConfig('Plugin.SphinxSearch.Timeout');
        RemoveFromConfig('Plugin.SphinxSearch.RetiresCount');
        RemoveFromConfig('Plugin.SphinxSearch.RetriesDelay');
        RemoveFromConfig('Plugin.SphinxSearch.MinWordIndexLen');


        Gdn::Structure()
                ->Table('sph_counter')
                ->Drop();
    }

}