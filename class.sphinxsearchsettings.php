<?php

/**
 * Create a singleton class for the Settings settings
 */
class SphinxSearchSettings {

    private static $_Instace;   //store the single instance
    protected static $_All = array(); //contain all Settings settings
    protected static $_Wizard = array(); //contains all admin settings
    protected static $_Install = array(); //contains all settings
    protected static $_Status = array();
    protected static $_Admin = array(); //contains all settings

    //By making the constructor private we have prohibited objects
    //of the class from being instantiated from outside the class

    public function __construct() {
    }

    public static function GetInstance() {
       // if (!self::$_Instace)
        if (1)
            self::$_Instace = new SphinxSearchSettings(); //only create a new instance once!
        return self::$_Instace;
    }

    public function GetAllSettings() {
        self::$_All = array(
            'Wizard' => Gdn_Format::ArrayAsObject(self::GetWizardSettings()),
            'Install' => Gdn_Format::ArrayAsObject(self::GetInstall()),
            'Status' => Gdn_Format::ArrayAsObject(self::GetStatus()),
            'Admin' => Gdn_Format::ArrayAsObject(self::GetAdminSettings()),
        );
        return self::$_All;
    }

    public function GetWizardSettings() {
        //Wizard steps
        self::$_Wizard['Start'] = C('Plugin.SphinxSearch.StartWizard', FALSE);
        self::$_Wizard['Connection'] = C('Plugin.SphinxSearch.Connection', FALSE);
        self::$_Wizard['Detection'] = C('Plugin.SphinxSearch.Detection', FALSE);
        self::$_Wizard['Detected'] = C('Plugin.SphinxSearch.Detected', FALSE); //whether or not system detected existance of sphinx or not (FALSE if did not)

        self::$_Wizard['Running'] = C('Plugin.SphinxSearch.Running', FALSE); //searchd start/stopped
        self::$_Wizard['Installed'] = C('Plugin.SphinxSearch.Installed', FALSE); //either found existing binaries or succesfull install

        self::$_Wizard['Task'] = C('Task', 'Settingsure');

        return self::$_Wizard;
    }

    public function GetStatus() {
        //general status
        self::$_Status['IndexerFound'] = C('Plugin.SphinxSearch.IndexerFound', FALSE);
        self::$_Status['SearchdFound'] = C('Plugin.SphinxSearch.SearchdFound', FALSE);
        self::$_Status['ConfFound'] = C('Plugin.SphinxSearch.ConfFound', FALSE);
        self::$_Status['Uptime'] = C('Plugin.SphinxSearch.Uptime', 0);
        self::$_Status['TotalQueries'] = C('Plugin.SphinxSearch.TotalQueries', 0);
        self::$_Status['MaxedOut'] = C('Plugin.SphinxSearch.MaxedOut', 0);

        self::$_Status['IndexerMainLast'] = C('Plugin.SphinxSearch.IndexerMainLast', 0);
        self::$_Status['IndexerDeltaLast'] = C('Plugin.SphinxSearch.IndexerDeltaLast', 0);
        self::$_Status['IndexerStatsLast'] = C('Plugin.SphinxSearch.IndexerStatsLast', 0);

        self::$_Status['SearchdPortStatus'] = C('Plugin.SphinxSearch.PortStatus'); //if port is Open/Closed
        self::$_Status['SearchdConnections'] = C('Plugin.SphinxSearch.SearchdConnections', 0);
        self::$_Status['SearchdStatus'] = C('Plugin.SphinxSearch.SearchdStatus', FALSE);

        self::$_Status['IndexerMainTotal'] = C('Plugin.SphinxSearch.IndexerMainTotal');
        self::$_Status['IndexerDeltaTotal'] = C('Plugin.SphinxSearch.IndexerDeltaTotal');
        self::$_Status['IndexerStatsTotal'] = C('Plugin.SphinxSearch.IndexerStatsTotal');


        return self::$_Status;
    }

    public function GetInstall() {
        //used for polling background tasks
        self::$_Install['ServicePollTask'] = C('Plugin.SphinxSearch.ServicePollTask', FALSE);


        self::$_Install['Host'] = C('Plugin.SphinxSearch.Host', 'localhost');
        self::$_Install['Port'] = C('Plugin.SphinxSearch.Port', 9312);
        self::$_Install['Prefix'] = C('Plugin.SphinxSearch.Prefix', 'vss_');

        self::$_Install['InstallPath'] = C('Plugin.SphinxSearch.InstallPath', SPHINX_SEARCH_INSTALL_DIR); //path to sphinx install directory
        self::$_Install['IndexerPath'] = C('Plugin.SphinxSearch.IndexerPath', 'Not Detected'); //path to indexer - use this for Settings purposes!
        self::$_Install['SearchdPath'] = C('Plugin.SphinxSearch.SearchdPath', 'Not Detected'); //path to searchd - use this for Settings purposes!
        self::$_Install['ConfPath'] = C('Plugin.SphinxSearch.ConfPath', 'Not Detected'); //path to searchd - use this for Settings purposes!

        self::$_Install['ManualIndexerPath'] = C('Plugin.SphinxSearch.ManualIndexerPath', ''); //manual path to indexer
        self::$_Install['ManualSearchdPath'] = C('Plugin.SphinxSearch.ManualSearchdPath', ''); //manual path to searchd
        self::$_Install['ManualConfPath'] = C('Plugin.SphinxSearch.ManualConfPath', ''); //manual path to sphinx.conf

        return self::$_Install;
    }

    public function GetAdminSettings() {
        //searchd settings
        self::$_Admin['Timeout'] = C('Plugin.SphinxSearch.Timeout', 3312); //units of ms
        self::$_Admin['RetriesCount'] = C('Plugin.SphinxSearch.RetriesCount', 50); //On temporary failures searchd will attempt up to $count retries per agent
        self::$_Admin['RetriesDelay'] = C('Plugin.SphinxSearch.RetriesDelay', 50); //$delay is the delay between the retries, in ms
        self::$_Admin['MinWordIndexLen'] = C('Plugin.SphinxSearch.MinWordIndexLen', 50); //$delay is the delay between the retries, in ms
        self::$_Admin['MemLimit'] = C('Plugin.SphinxSearch.MemLimit', '32M'); // must keep the 'M' designator
        self::$_Admin['MaxQueryTime'] = C('Plugin.SphinxSearch.MaxQueryTime', 2000); //units of ms
        self::$_Admin['MaxMatches'] = C('Plugin.SphinxSearch.MaxMatches', 1000);
        

        //Add an offset to what you really want (want 20 => put in 21) due to stripping out
        //the first result found of related content (omit first given query result)
        self::$_Admin['LimitRelatedMain'] = C('Plugin.SphinxSearch.LimitRelatedMain', 21);      //# of related discussion titles next to the main search results
        self::$_Admin['LimitRelatedPost'] = C('Plugin.SphinxSearch.LimitRelatedPost', 21);      //# of related discussion titles that pops up when user searching for related threads
        self::$_Admin['LimitRelatedDiscussion'] = C('Plugin.SphinxSearch.LimitRelatedDiscussion', 21);      //# of related discussion titles on bottom of each discussion
        self::$_Admin['LimitRelatedSearches'] = C('Plugin.SphinxSearch.LimitRelatedSearches', 21);      //# of related
        self::$_Admin['LimitRelatedKeywords'] = C('Plugin.SphinxSearch.LimitRelatedKeywords', 21);      //# of related
        self::$_Admin['LimitResultsPage'] = C('Plugin.SphinxSearch.LimitResultsPage', 10);      //# of docs on the main results page
        self::$_Admin['HighLightTitles'] = C('Plugin.SphinxSearch.HighLightTitles', TRUE);
        self::$_Admin['HighLightText'] = C('Plugin.SphinxSearch.HighLightText', TRUE);
        self::$_Admin['ReleatedThreadsLimit'] = C('Plugin.SphinxSearch.ReleatedThreadsLimit', 15);


        self::$_Admin['BuildExcerpts'] = array(
            'before_match' => '<span class="SphinxExcerpts">',
            'after_match' => '</span>',
            'chunk_separator' => '...',
            'limit' => C('Plugin.SphinxSearch.BuildExcerptsLimit', 100), //Maximum snippet size, in symbols (codepoints). Integer, default is 256.
            'around' => C('Plugin.SphinxSearch.BuildExcerptsAround', 20), //words around the matched word
        );
        //Enable/Disable widgets
        self::$_Admin['MainSearchEnable'] = C('Plugin.SphinxSearch.MainSearchEnable', TRUE);
        self::$_Admin['MainHitBoxEnable'] = C('Plugin.SphinxSearch.MainHitBoxEnable', TRUE);
        self::$_Admin['StatsEnable'] = C('Plugin.SphinxSearch.StatsEnable', TRUE);
        self::$_Admin['RelatedEnable'] = C('Plugin.SphinxSearch.RelatedEnable', TRUE);




        return self::$_Admin;
    }

}
