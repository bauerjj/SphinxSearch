<?php

/**
 * Create a singleton class for the Settings settings
 */
class SphinxSearchSettings {

    private static $Instace;   //store the single instance
    protected static $All = array(); //contain all Settings
    protected static $SearchOptions = array();
    protected static $Wizard = array();
    protected static $Install = array();
    protected static $Status = array();
    protected static $Admin = array();

    //By making the constructor private we have prohibited objects
    //of the class from being instantiated from outside the class

    public function __construct() {

    }

    public static function GetInstance() {
        // if (!self::$Instace)
        if (1)
            self::$Instace = new SphinxSearchSettings(); //only create a new instance once!
        return self::$Instace;
    }

    public function GetAllSettings() {
        self::$All = array(
            'SearchOptions' => Gdn_Format::ArrayAsObject(self::GetSearchOptions()),
            'Wizard' => Gdn_Format::ArrayAsObject(self::GetWizardSettings()),
            'Install' => Gdn_Format::ArrayAsObject(self::GetInstall()),
            'Status' => Gdn_Format::ArrayAsObject(self::GetStatus()),
            'Admin' => Gdn_Format::ArrayAsObject(self::GetAdminSettings()),
        );
        return self::$All;
    }

    public function GetSearchOptions() {
        self::$SearchOptions = array(
            'Match' => array('Any' => 'Any', 'All' => 'All', 'Extended' => 'Extended'), //Search Match Mode
            'Order' => array('Relevance' => 'Relevance', 'MostRecent' => 'Most Recent', 'MostViews' => 'Most Views', 'MostReplies' => 'Most Replies'), //Search Order - the key corresponds to value on radio list
            'Time' => array('All' => 'All', 'ThisWeek' => 'This Week', 'ThisMonth' => 'This Month', 'ThisYear' => 'This Year'), //t
            'ResultFormat' => array('Full' => 'Full', 'Table' => 'Table', 'Sleak' => 'Sleak','Simple' => 'Simple'),
        );

        return self::$SearchOptions;
    }

    public function GetWizardSettings() {
        $Wizard = array(
            'Plugin.SphinxSearch.StartWizard' => FALSE,
            'Plugin.SphinxSearch.Connection' => FALSE,
            'Plugin.SphinxSearch.Detection' => FALSE,
            'Plugin.SphinxSearch.Detected' => FALSE,
            'Plugin.SphinxSearch.Running' => FALSE,
            'Plugin.SphinxSearch.Installed' => FALSE,
            'Plugin.SphinxSearch.Task' => FALSE,
            'Plugin.SphinxSearch.ServicePollTask' => FALSE,
        );
        foreach ($Wizard as $Name => $Default) {
            $Val = explode('.', $Name);
            $ShortName = $Val[2];
            self::$Wizard[$ShortName] = C($Name, $Default);
        }
        return self::$Wizard;
    }

    public function GetStatus() {
        $Status = array(
            'Plugin.SphinxSearch.IndexerFound' => FALSE,
            'Plugin.SphinxSearch.SearchdFound' => FALSE,
            'Plugin.SphinxSearch.ConfFound' => FALSE,
            'Plugin.SphinxSearch.Uptime' => 0,
            'Plugin.SphinxSearch.TotalQueries' => 0,
            'Plugin.SphinxSearch.MaxedOut' => 0,
            'Plugin.SphinxSearch.IndexerMainLast' => 0,
            'Plugin.SphinxSearch.MaxedOut' => 0,
            'Plugin.SphinxSearch.IndexerMainLast' => '---',
            'Plugin.SphinxSearch.IndexerDeltaLast' => '---',
            'Plugin.SphinxSearch.IndexerStatsLast' => '---',
            'Plugin.SphinxSearch.SearchdPortStatus' => FALSE,
            'Plugin.SphinxSearch.SearchdConnections' => 0,
            'Plugin.SphinxSearch.SearchdRunning' => FALSE,
            'Plugin.SphinxSearch.IndexerMainTotal' => 0,
            'Plugin.SphinxSearch.IndexerDeltaTotal' => 0,
            'Plugin.SphinxSearch.IndexerStatsTotal' => 0,
        );
        foreach ($Status as $Name => $Default) {
            $Val = explode('.', $Name);
            $ShortName = $Val[2];
            self::$Status[$ShortName] = C($Name, $Default);
        }
        return self::$Status;
    }

    public function GetInstall() {

        $Install = array(
            'Plugin.SphinxSearch.ServicePollTask' => FALSE,
            'Plugin.SphinxSearch.Host' => 'localhost',
            'Plugin.SphinxSearch.Port' => 9312,
            'Plugin.SphinxSearch.Prefix' => 'vss_',
            'Plugin.SphinxSearch.InstallPath' => SS_INSTALL_DIR,
            'Plugin.SphinxSearch.IndexerPath' => 'Not Detected',
            'Plugin.SphinxSearch.SearchdPath' => 'Not Detected',
            'Plugin.SphinxSearch.ConfPath' => 'Not Detected',
            'Plugin.SphinxSearch.ManualIndexerPath' => '',
            'Plugin.SphinxSearch.ManualSearchdPath' => '',
            'Plugin.SphinxSearch.ManualConfPath' => '',
        );
        foreach ($Install as $Name => $Default) {
            $Val = explode('.', $Name);
            $ShortName = $Val[2];
            self::$Install[$ShortName] = C($Name, $Default);
        }
        return self::$Install;
    }

    /**
     * All checkboxes MUST BE A BOOLEAN!! Or else validator will require something and a FALSE will inccur an error
     * @return type
     */
    public function GetAdminSettings() {
        $AdminSettings = array(
            'Plugin.SphinxSearch.LimitResultsPage' => 20,
            'Plugin.SphinxSearch.MainResultsFormat' => 'simple',
            'Plugin.SphinxSearch.MainHitBoxEnable' => TRUE,
            'Plugin.SphinxSearch.LimitRelatedSearches' => 20,
            'Plugin.SphinxSearch.LimitTopKeywords' => 20,
            'Plugin.SphinxSearch.LimitTopSearches' => 20,
            'Plugin.SphinxSearch.LimitRelatedThreadsSidebarDiscussion' => 20,
            'Plugin.SphinxSearch.LimitRelatedThreadsMain' => 20,
            'Plugin.SphinxSearch.LimitRelatedThreadsPost' => 20,
            'Plugin.SphinxSearch.RelatedThreadsPostFormat' => 'simple',
            'Plugin.SphinxSearch.LimitRelatedThreadsBottomDiscussion' => 20,
            'Plugin.SphinxSearch.RelatedThreadsBottomDiscussionFormat' => 'table',
            'Plugin.SphinxSearch.MaxQueryTime' => 2000,
            'Plugin.SphinxSearch.RetriesCount' => 50,
            'Plugin.SphinxSearch.RetriesDelay' => 50,
            'Plugin.SphinxSearch.ReadTimeout' => 5,
            'Plugin.SphinxSearch.ClientTimeout' => 360,
            'Plugin.SphinxSearch.MaxChildren' => 0,
            'Plugin.SphinxSearch.MaxMatches' => 1000,
            'Plugin.SphinxSearch.ReadBuffer' => '1M',
            'Plugin.SphinxSearch.Workers' => 'fork',
            'Plugin.SphinxSearch.ThreadStack' => '64K',
            'Plugin.SphinxSearch.ExpansionLimit' => 50,
            'Plugin.SphinxSearch.PreforkRotationThrottle' => 0,
            'Plugin.SphinxSearch.MemLimit' => '32M',
            'Plugin.SphinxSearch.MaxIOps' => 0,
            'Plugin.SphinxSearch.MaxIOSize' => 0,
            'Plugin.SphinxSearch.WriteBuffer' => '1M',
            'Plugin.SphinxSearch.MaxFileBuffer' => '8M',
            'Plugin.SphinxSearch.BuildExcerptsAround' => 3,
            'Plugin.SphinxSearch.BuildExcerptsLimit' => 60,
            'Plugin.SphinxSearch.BuildExcerptsTitleEnable' => TRUE,
            'Plugin.SphinxSearch.BuildExcerptsBodyEnable' => TRUE,
            'Plugin.SphinxSearch.Morphology' => 'none',
            'Plugin.SphinxSearch.Dict' => 'crc',
            'Plugin.SphinxSearch.MinStemmingLen' => 1,
            'Plugin.SphinxSearch.StopWordsEnable' => TRUE,
            'Plugin.SphinxSearch.WordFormsEnable' => FALSE,
            'Plugin.SphinxSearch.MinWordIndexLen' => 3,
            'Plugin.SphinxSearch.MinPrefixLen' => 0,
            'Plugin.SphinxSearch.MinInfixLen' => 0,
            'Plugin.SphinxSearch.StarEnable' => FALSE,
            'Plugin.SphinxSearch.NGramLen' => 0,
            'Plugin.SphinxSearch.HtmlStripEnable' => FALSE,
            'Plugin.SphinxSearch.OnDiskDictEnable' => FALSE,
            'Plugin.SphinxSearch.InPlaceEnable' => FALSE,
            'Plugin.SphinxSearch.ExpandKeywordsEnable' => FALSE,
            'Plugin.SphinxSearch.RTMemLimit' => 0,
            'Plugin.SphinxSearch.MainSearchEnable' => TRUE, //LEAVE THIS FOR BUTTONS TO DISAB/ENABLE
            'Plugin.SphinxSearch.StatsEnable' => TRUE,
            'Plugin.SphinxSearch.RelatedEnable' => TRUE,
        );
        foreach ($AdminSettings as $Name => $Default) {
            $Val = explode('.', $Name);
            $ShortName = $Val[2];
            self::$Admin[$ShortName] = C($Name, $Default);
        }
        return self::$Admin;
    }

}
