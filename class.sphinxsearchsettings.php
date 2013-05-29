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
            'Match' => array('Any' => T('Any'), 'All' => T('All'), 'Extended' => T('Extended')), //Search Match Mode
            'Order' => array('Relevance' => T('Relevance'), 'MostRecent' => T('Most Recent'), 'MostViews' => T('Most Views'), 'MostReplies' => T('Most Replies')), //Search Order - the key corresponds to value on radio list
            'Time' => array('All' => 'All', 'ThisWeek' => T('This Week'), 'ThisMonth' => T('This Month'), 'ThisYear' => T('Year')), //t
            'ResultFormat' => array('Classic' => T('Classic'), 'Table' => T('Table'), 'Sleek' => T('Sleek'),'Simple' => T('Simple')),
        );

        return self::$SearchOptions;
    }

    public function GetWizardSettings() {
        $Wizard = array(
            'Plugin.SphinxSearchLite.StartWizard' => FALSE,
            'Plugin.SphinxSearchLite.Config' => FALSE,
            'Plugin.SphinxSearchLite.Connection' => FALSE, //step 1
            'Plugin.SphinxSearchLite.Detection' => FALSE, //step 2
            'Plugin.SphinxSearchLite.Installed' => FALSE, //end of wizard
            'Plugin.SphinxSearchLite.AutoDetected' => FALSE,
            'Plugin.SphinxSearchLite.ManualDetected' => FALSE,
            'Plugin.SphinxSearchLite.Task' => 'Idle',
            'Plugin.SphinxSearchLite.ServicePollTask' => FALSE,
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
            'Plugin.SphinxSearchLite.IndexerFound' => FALSE,
            'Plugin.SphinxSearchLite.SearchdFound' => FALSE,
            'Plugin.SphinxSearchLite.ConfFound' => FALSE,
            'Plugin.SphinxSearchLite.SearchdRunning' => FALSE,
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
            'Plugin.SphinxSearchLite.ServicePollTask' => FALSE,
            'Plugin.SphinxSearchLite.Host' => 'localhost',
            'Plugin.SphinxSearchLite.Port' => 9312,
            'Plugin.SphinxSearchLite.Prefix' => 'vss_',
            'Plugin.SphinxSearchLite.InstallPath' => SS_INSTALL_DIR,
            'Plugin.SphinxSearchLite.IndexerPath' => 'Enter Path',
            'Plugin.SphinxSearchLite.SearchdPath' => 'Enter Path',
            'Plugin.SphinxSearchLite.ConfPath' => 'Enter Path',
            'Plugin.SphinxSearchLite.ConfText' => 'Enter Text',
            'Plugin.SphinxSearchLite.LogPath' => '',
            'Plugin.SphinxSearchLite.QueryPath' => '',
            'Plugin.SphinxSearchLite.PIDPath' => '',
            'Plugin.SphinxSearchLite.DataPath' => '',
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
            'Plugin.SphinxSearchLite.LimitResultsPage' => 30,
            'Plugin.SphinxSearchLite.MaxMatches' => 1000,
            'Plugin.SphinxSearchLite.LimitMemberMatches' => 20,
        );
        foreach ($AdminSettings as $Name => $Default) {
            $Val = explode('.', $Name);
            $ShortName = $Val[2];
            self::$Admin[$ShortName] = C($Name, $Default);
        }
        return self::$Admin;
    }

}
