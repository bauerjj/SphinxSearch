<?php

if (!defined('APPLICATION'))
    exit();
/*
 * This software is licensed under GPLv2 - see the license in the root of this folder
 *
 *
 */

$PluginInfo['SphinxSearch'] = array(
    'Description' => 'A much improved search experience with widgets based on the Sphinx Search Engine',
    'Version' => '20140114',
    'RequiredApplications' => array('Vanilla' => '2.0.18.4'),
    'RequiredTheme' => FALSE,
    'RequiredPlugins' => FALSE,
    'HasLocale' => TRUE,
    'SettingsUrl' => '/plugin/sphinxsearch',
    'SettingsPermission' => 'Garden.AdminUser.Only',
    'Author' => "mcuhq",
    'AuthorEmail' => 'kapotchy@gmail.com',
    'AuthorUrl' => 'http://mcuhq.com/forums'
);

class SphinxSearchPlugin extends Gdn_Plugin implements SplSubject {

    private $PostPrefix = 'Configuration/'; //not sure why this is inside of the $_POST.....
    public $AlreadySent; //hack to workaround lack of handlers to hook into for rendering this
    public $Started = FALSE;    //use this to interrupt main search from executing
    public $Widgets = array();  //list of registered widgets
    public $SphinxClient;       //only have one instance of this
    public $Queries = array(); //keep track of queries
    public $Settings = array(); //all settings related to sphinx are kept in here (READ ONLY)
    public $_Status = array(); //log of stuff that is happening
    public $_Storage = array(); //storage of objects that are subscribed

    public function __construct() {
        //@todo for testing
        //error_reporting(E_ALL);
        ////////////////////////////////////////////////
        //Sphinx plugin core modules
        /////////////////////////////////////////////////
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'sphinxconstants.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxfactory.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxobservable.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchadmin.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchgeneral.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchinstall.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchinstallwizard.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchservice.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchsettings.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxstatuslogger.php');
        //include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.searchmodel.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'sphinxapi.php'); //load the Sphinx API file
        ////////////////////////////////////////////////
        //Sphinx views used in the widgets
        /////////////////////////////////////////////////
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'widgets' . DS . 'views' . DS . 'helper_functions.php'); //widgets use this

        $SphinxAdmin = SphinxFactory::BuildSphinx(null, null); // @todo Should fix this angry call...
        // This only works because the logger needs the sender and view to generate an error message. Since 'Status()' will not cause
        // Any errors, both of the paparemts are not needed and so a null value will do. This is still ugly tho
        $SphinxAdmin->Status(); //call status from here so that admin can register observers

        $this->SphinxClient = new SphinxClient(); //sphinx API
        $Settings = SphinxFactory::BuildSettings();
        $this->Settings = $Settings->GetAllSettings();
        //create subclasses
        $this->_Storage = new SplObjectStorage();
        //if($this->Settings['Status']->SearchdRunning && $this->Settings['Status']->EnableSphinxSearch) //check if sphinx is running and if not manually overriden
        if (true) {
            $this->RegisterWidgets();
            $this->RegisterModules();
        }
    }

    public function Attach(SplObserver $Observer) {
        $this->_Storage->attach($Observer);
    }

    public function Detach(SplObserver $Observer) {
        $this->_Storage->detach($Observer);
    }

    public function Notify() {
        foreach ($this->_Storage as $Observer) {
            $Observer->update($this);
        }
    }

    public function GetStatus() {
        return $this->_Status; //classic get operation
    }

    public function Update($Results, $Sender) {
        $this->_Status = array(
            'Results' => $Results,
            'Sender' => $Sender,
        ); //notify widgets that the sphinx search has been performed
        $this->Notify(); //let the subscribers know of this
    }

    public function RegisterWidgets() {
        $Path = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'widgets' . DS;
        include($Path . 'class.widgets.php'); //must include this abstract class first!
        foreach (glob($Path . '*.php') as $filename) {
            $File = str_replace($Path, '', $filename);
            $Class = explode('.', $File);
            $Class = $Class[2];
            if ($Class == 'php')
                $ClassName = 'widgets'; //for the abstract class, 'class.widgets.php'
            else
                $ClassName = 'widget' . $Class;

            if ($ClassName != 'widgets') { //can't instantiate abstract class
                include $filename; //inclue all of the widget classes
                $Obj = new $ClassName($this->SphinxClient, $this->Settings);
                $this->Widgets[$ClassName] = $Obj; //keep track of registered widgets
                $this->Attach($Obj);
            }
        }
    }

    public function RegisterModules() {
        $Path = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'modules' . DS;
        foreach (glob($Path . '*.php') as $filename) {
            include $filename; //inclue all of the modules
        }
    }

    public function QueryWidgets($Sender) {
        if (empty($_GET) || isset($_GET['tar']))
            $Landing = TRUE; //whether or not on main search page or on the results page
        else
            $Landing = FALSE;
        foreach ($this->Widgets as $Class => $Obj) {
            if (method_exists($Obj, 'AddQuery'))
                $Query = $Obj->{'AddQuery'}($Sender, array('Landing' => $Landing));
            if ($Query != FALSE)
                $this->Queries = array_merge($this->Queries, $Query);
        }
    }

    public function Base_Render_Before(&$Sender) {
        //'discussioncontroller','categoriescontroller','discussionscontroller','profilecontroller',
        //'activitycontroller','draftscontroller','messagescontroller', searchcontroller
        $Sender->AddCssFile('/plugins/SphinxSearch/design/result.css'); //for the tooltip as well
        $this->QueryWidgets($Sender);
        $Sender->AddJsFile('jquery.hoverintent.js', 'plugins' . DS . 'SphinxSearch' . DS); //tooltip
        $Sender->AddJsFile('jquery.tooltip.js', 'plugins' . DS . 'SphinxSearch' . DS); //tooltip
        //
        //// Add defnitions for the javascript to pick up the 'more/less' options button
        $Sender->AddDefinition('More', T('More Options'));
        $Sender->AddDefinition('Less', T('Less Options'));


        ///////////////This runs sphinx !!!////////////////////
        $Results = $this->RunSearch($Sender); //only 1 search call is made...use sphinxclient->AddQuery(...)
        ///////////////////////////////////////////////////////
        $this->Update($Results, $Sender); //update the widgets notifiying of results
        //get the updated settings
        $Settings = SphinxFactory::BuildSettings();
        $this->Settings = $Settings->GetAllSettings();
        $Sender->SetData('Settings', $this->Settings);
    }

    private function RunSearch($Sender = FALSE) {
        $FinalResults = array();
        if (sizeof($this->Queries) == 0)
            return $FinalResults; //don't run queries since no queries qued up
        else if (true) { //if($this->Settings['Status']->SearchdRunning)
            //print_r($this->Queries); die;
            $Results = $this->SphinxClient->RunQueries(); //perform all of the queries
            //print_r($Results); die;

            /**
             * This will publicly announce any errors or warnings to the main search page. If this is unwarranted, simply
             * comment the following few lines. However this if usually the first place to look for any errors in the install
             */
            if ($Results === false) {
                echo "Query failed: " . $this->SphinxClient->GetLastError() . ".\n";
            } else {
                if ($this->SphinxClient->GetLastWarning()) {
                    echo "WARNING: " . $this->SphinxClient->GetLastWarning() . ".\n";
                }
            }


            if ($Results) {
                foreach ($this->Queries as $Query) {
                    $ResultDocs = array();
                    $Index = $Query['Index'];
                    $Name = $Query['Name'];
                    $Result = $Results[$Index]; //get the individual query result from the results tree

                    if ($Result['error'] == FALSE && $Result['total_found'] != 0 && isset($Result['matches'])) { //no errors
                        foreach ($Result['matches'] as $Id => $Info) { //preserve the returned Doc ID
                            if (isset($Info['attrs']))
                                $ResultDocs[$Id] = Gdn_Format::ArrayAsObject($Info['attrs']); //get the result documents
                        }
                        $Words = '';
                        if (isset($Result['words'])) {
                            foreach ($Result['words'] as $Word => $Info) {
                                $Words.= $Word . ' '; //get the submitted input query
                            }
                            $Results[$Index]['query'] = $Words; //add the query back into the resuls array
                        }

                        $Results[$Index]['matches'] = $ResultDocs; //replace highlighted docs back into the main results array
                    }
                    $FinalResults[$Name] = $Results[$Index];
                }
            }
        }
        //PRINT_R($FinalResults); die;
        return $FinalResults;
    }

    /**
     * Enter here after clicking the little 'help' anchor next to the search button
     * @param type $Sender
     */
    public function Controller_Help($Sender) {
        $SearchHelpModule = new SearchHelpModule();
        echo $SearchHelpModule->ToString(); //for now just print out same as the left sidebar
    }

    public function PluginController_SphinxSearch_Create($Sender) {
        $Sender->Title('Sphinx Search');

        $Sender->AddSideMenu('plugin/sphinxsearch'); //add the left side menu
        $Sender->Form = new Gdn_Form();

        $this->Dispatch($Sender, $Sender->RequestArgs);
    }


    /**
     * main entry point for control panel as well as to poll the status of sphinx such
     * as reindexing/start/stop/rotate/etc
     *
     * @param object $Sender
     */
    public function Controller_Index($Sender) {
        $Sender->Permission('Vanilla.Settings.Manage');


        // Currently, only pretty URLs will work with Sphinx. This is due to how the GET query string is constructed
        if (C('Garden.RewriteUrls') != TRUE)
            $Sender->Form->AddError("Must enable Pretty URLs for Sphinx to work properly! <br> Do so in your config.php file; Configuration['Garden']['RewriteUrls'] = TRUE;");

        $Sender->SetData('PluginDescription', $this->GetPluginKey('Description'));
        $Sender->SetData('PluginVersion', $this->GetPluginKey('Version'));
        $SphinxAdmin = SphinxFactory::BuildSphinx($Sender, $this->getview('sphinxsearch.php'));

        $SphinxAdmin->Status();
        $Sender->SetData('Settings', $SphinxAdmin->GetSettings());
        $Sender->Render($this->getview('sphinxsearch.php'));
    }

    public function Controller_FAQ($Sender) {
        // Prevent non-admins from accessing this page
        $Sender->Permission('Vanilla.Settings.Manage');

        $Sender->SetData('PluginDescription', $this->GetPluginKey('Description'));
        $Sender->SetData('PluginVersion', $this->GetPluginKey('Version'));

        $Sender->Render($this->GetView('faq.php'));
    }

    /**
     * main entry point for the install wizard
     * input parameters for wizard are in the $_GET buffer
     * @param type $Sender
     */
    public function Controller_InstallWizard($Sender) {
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchinstallwizard.php'); //include the install wizard
        $SphinxAdmin = SphinxFactory::BuildSphinx($Sender, $this->getview('wizard.php'));

        //SaveToConfig('Plugin.SphinxSearch.SearchdPath', '/usr/bin/sphinx-searchd');

        $Sender->SetData('PluginDescription', $this->GetPluginKey('Description'));
        $Sender->SetData('PluginVersion', $this->GetPluginKey('Version'));

        //create validation
        $Validation = new Gdn_Validation();
        $this->ConfigurationModel = new Gdn_ConfigurationModel($Validation);
        $this->ConfigurationModel->SetField(array(
                //validate individual fields depending on what step they are on
        ));
        $Sender->Form->SetModel($this->ConfigurationModel);   //set model on form
        $Sender->SetData('NextAction', 'Detection');
        $Sender->SetData('InstallSphinx', FALSE);
        $Sender->Form->SetData($this->ConfigurationModel->Data);
        $Sender->Form->InputPrefix = 'Configuration';
        //Wizard State Machine (SM)
        if ((GetIncomingValue('action') == 'ToggleWizard')) {
            // The validation transient keys don't work in 2.1b for some reason
           // if (Gdn::Session()->ValidateTransientKey(GetValue(1, $Sender->RequestArgs))) {
            if (true) {
                $SphinxAdmin->ToggleWizard();             //stop/start wizard
                redirect('plugin/sphinxsearch/installwizard'); //load the wizard page again
            }
        } else if (isset($_POST[$this->PostPrefix . 'NextAction'])) {
            $Background = GetIncomingValue($this->PostPrefix . 'Background');
            switch ($_POST[$this->PostPrefix . 'NextAction']) {
                case 'Detection':
                    $Sender->SetData('NextAction', 'Detection'); //in case it fails
                    $SphinxAdmin->Detect();
                    $this->ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.Prefix', 'Required');
                    $this->ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.Port', 'Required');
                    $this->ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.Host', 'Required');
                    $this->ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.Port', 'Integer'); //PORT must be an int
                    $Sender->Form->Save();
                    if (!$Sender->Form->Errors()) {
                        //@todo don't check port just yet
                        $Sender->StatusMessage = T("Your changes have been saved.");
                        SaveToConfig('Plugin.SphinxSearch.Connection', TRUE); //complete this step
                        $Sender->SetData('NextAction', 'Install'); //next step
                    }
                    else
                        SaveToConfig('Plugin.SphinxSearch.Connection', FALSE); //don't continue
                    break;
                case 'Install':              //Install Sphinx
                    $Sender->SetData('NextAction', 'Install'); //in case it fails
                    $InstallAction = GetValue($this->PostPrefix . 'Plugin-dot-SphinxSearch-dot-Detected', $_POST);

                   /*
                    *  The paths are no longer required inputs since only the creation of the cron files require it!
                    */
                    $this->ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.ConfText', 'Required'); // This is the only input required!
                    $Sender->Form->Save();
                    if (!$Sender->Form->Errors())  {
                        //refresh settings after save by getting new instance @todo pretty janky
                        $SphinxAdmin = SphinxFactory::BuildSphinx($Sender, $this->getview('wizard.php'));
                        $SphinxAdmin->InstallAction($InstallAction, $Background); //Read info from the pasted sphinx.conf
                        //refresh settings (PID/log/query/ path) after save by getting new instance @todo pretty janky
                        $SphinxAdmin = SphinxFactory::BuildSphinx($Sender, $this->getview('wizard.php'));
                        $SphinxAdmin->InstallConfig(); // Write the new sphinx.conf file
                        SaveToConfig('Plugin.SphinxSearch.Config', TRUE); //next step
                        $Sender->SetData('NextAction', 'Config'); //next step is Cron Config
                        // Sphinx is technically all set. Cron file generation is optional
                        $Sender->StatusMessage = T("Your changes have been saved.");
                    } else {
                        //return FALSE;
                    }
                    break;
                case 'Config': // AKA cron setup
                    $Sender->SetData('NextAction', 'Config'); //in case it fails
                    $this->ConfigurationModel->Validation->AddValidationField('Plugin.SphinxSearch.IndexerPath',$_POST);
                    $this->ConfigurationModel->Validation->AddValidationField('Plugin.SphinxSearch.ConfPath',$_POST);
                    $Sender->Form->Save();
                    if (!$Sender->Form->Errors()) {
                        $SphinxAdmin->InstallCron();
                        SaveToConfig('Plugin.SphinxSearch.Installed', TRUE); // Sphinx is technically all set. Cron file generation is optional
                        $Sender->SetData('NextAction', 'Finish'); //complete this step
                    }
                    break;
                default:
                    break;
            }
        }
        //get new settings that may have changed
        $Settings = SphinxFactory::BuildSettings();
        $Sender->SetData('Settings', $Settings->GetAllSettings());
        $Sender->Render($this->GetView('wizard.php')); //render wizard view
    }

    /**
     * enter here when enterin a new discussion. sphinx will attempt to find
     * related threads and post them above the proposed discussion title
     *
     *
     * @return type
     */
    public function Controller_NewDiscussion() {
        $Return = array();
        $Query = GetIncomingValue('Query'); //grab the POSTED query
        if ($Query) {
            $RelatedPost = new WidgetRelatedPost($this->SphinxClient, $this->Settings);
            $Related = $RelatedPost->AddQuery(null, array('Query' => $Query)); //now actually adding the query
            $this->Queries = array_merge($this->Queries, $Related);
            $Results = $this->RunSearch();
            $Results = $Results['RelatedPost']; //name of related post query class name
            $Return['Text'] = $RelatedPost->ToString($Results, $Query);

            echo json_encode($Return);
        }
    }

    /**
     * Enter here to interrupt the normal search page from showing.
     *
     * First checks if sphinx is running. If it is, then continue with sphinx stuff. If not, then
     * load up the typical search engine that is shipped with Vanilla
     *
     * @param type $Sender
     */
    public function SearchController_Render_Before($Sender) {
        //In order for the default search engine not to run, we will kill PHP from processing
        //after the sphinx view is loaded
        //  if ($this->Settings['Status']->SearchdRunning && $this->Settings['Status']->EnableSphinxSearch) { //exit if sphinx is not running or if manullay overridden
        if (true) { // Always have this enabled as long as plugin is enabled
            if ($this->AlreadySent != 1) { //in order to elminate nesting this over and over again as well as allowing result page to render
                $this->AlreadySent = 1;

                //get rid of that nasty guest module
                if (isset($Sender->Assets['Panel']['GuestModule']))
                    unset($Sender->Assets['Panel']['GuestModule']);

                $Sender->SetData('Settings', $this->Settings); //put settings on view
                //check which file to load
                if (empty($_GET) || isset($_GET['tar'])) {
                    //Load main search page
                    $SearchHelpModule = new SearchHelpModule();
                    $Sender->AddAsset('LeftPanel', $SearchHelpModule, 'LeftPanel');

                    $Sender->AddAsset('BottomPanel', '', 'BottomPanel');
                    $Sender->AddCssFile('/plugins/SphinxSearch/design/mainsearch.css');

                    //Get list of tags
                    $SQL = Gdn::SQL();
                    if (Gdn::Structure()->TableExists('Tag')) {
                        $Tags = $SQL->Select('t.Name')
                                ->From('Tag as t')
                                ->Get()
                                ->ResultArray();
                        $Sender->SetData('Tags', implode(',', ConsolidateArrayValuesByKey($Tags, 'Name')));
                    } else {
                        $Sender->SetData('Tags', ''); //empty
                    }

                    $Sender->Render($this->GetView('search' . DS . 'search.php'));
                } else {
                    //Load results page
                    $Sender->AddCssFile('/plugins/SphinxSearch/design/result.css');
                    $Sender->Render($this->GetView('search' . DS . 'index.php'));
                }

                die; //necessary to prevent duplicate
            }
        }
    }

    /**
     * Adds the related threads to the top of each discussion
     * @param type $Sender
     */
    public function DiscussionController_BeforeDiscussion_Handler($Sender) {

    }

    /**
     * Adds the related threads to the bottom of each discussion
     * @param type $Sender
     * @pre RunSearch() ran already
     */
    public function DiscussionController_AfterDiscussion_Handler($Sender) {
        if (isset($Sender->Data['RelatedDiscussion'])) { //are there any related threads to display?
            $Results = $Sender->Data['RelatedDiscussion'];
            $this->Update($Results, $Sender);
        }
    }

    /**
     * Adds the related threads above "ask a new question/thread"
     * @param type $Sender
     * @param type $Args
     */
    public function PostController_BeforeFormInputs_Handler($Sender, $Args) {
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'widgets' . DS . 'views' . DS . 'relatedpost.php');
    }

    /**
     * Enter here when polling for username on main search page
     */
    public function Controller_AutoCompleteMember() {
        if (!isset($_GET['term']))
            return '';
        $Match = mysql_escape_string($_GET['term']); //the text from the input field
        $Member = new WidgetMember($this->SphinxClient, $this->Settings);
        $MemberSearch = $Member->AddQuery(null, array('MemberName' => $Match));

        $this->Queries = $MemberSearch;
        $Results = $this->RunSearch();
        $Results = GetValue('MemberSearch', $Results); //name of query
        $Return = $Member->ToString(GetValue('matches', $Results));

        echo json_encode($Return);
    }

    /**
     * Enable/Disable certain settings
     */
    public function Controller_Toggle($Sender) {
        if (Gdn::Session()->ValidateTransientKey(GetValue(1, $Sender->RequestArgs))) {
            $Option = GetValue('action', $_GET);
            switch ($Option) {
                case 'mainsearch':
                    if ($this->Settings['Admin']->MainSearchEnable) {
                        SaveToConfig('Plugin.SphinxSearch.MainSearchEnable', FALSE);
                        SaveToConfig('Plugin.SphinxSearch.LimitResultsPage', 0);
                        SaveToConfig('Plugin.SphinxSearch.MainHitBoxEnable', FALSE);
                    }
                    else
                        SaveToConfig('Plugin.SphinxSearch.MainSearchEnable', TRUE);

                    break;
                case 'stats':
                    if ($this->Settings['Admin']->StatsEnable) {
                        SaveToConfig('Plugin.SphinxSearch.StatsEnable', FALSE);
                        SaveToConfig('Plugin.SphinxSearch.LimitRelatedSearches', 0);
                        SaveToConfig('Plugin.SphinxSearch.LimitTopKeywords', 0);
                        SaveToConfig('Plugin.SphinxSearch.LimitTopSearches', 0);
                    }
                    else
                        SaveToConfig('Plugin.SphinxSearch.StatsEnable', TRUE);

                    break;
                case 'related':
                    if ($this->Settings['Admin']->RelatedEnable) {
                        SaveToConfig('Plugin.SphinxSearch.RelatedEnable', FALSE);
                        SaveToConfig('Plugin.SphinxSearch.LimitRelatedThreadsSidebarDiscussion', 0);
                        SaveToConfig('Plugin.SphinxSearch.LimitRelatedThreadsMain', 0);
                        SaveToConfig('Plugin.SphinxSearch.LimitRelatedThreadsPost', 0);
                        SaveToConfig('Plugin.SphinxSearch.LimitRelatedThreadsBottomDiscussion', 0);
                    }
                    else
                        SaveToConfig('Plugin.SphinxSearch.RelatedEnable', TRUE);

                    break;
                default:
                    break;
            }
            Redirect('plugin/sphinxsearch/settings');
        }
    }

    /**
     * enter here to view settings file
     *
     * @param type $Sender
     */
    public function Controller_Settings($Sender) {
        $Sender->Permission('Vanilla.Settings.Manage');
        $Sender->SetData('PluginDescription', $this->GetPluginKey('Description'));
        $Sender->SetData('PluginVersion', $this->GetPluginKey('Version'));
        $Sender->SetData('Settings', $this->Settings); //FOR DISABLEING/ENABLING BUTTONS (STATS/MAIN/RELATED)
        $Validation = new Gdn_Validation();
        $ConfigurationModel = new Gdn_ConfigurationModel($Validation);

        //Have to do the following for settings page to save in the right full name
        foreach ($this->Settings['Admin'] as $Name => $Value) {
            $Settings['Plugin.SphinxSearch.' . $Name] = $Value;
            $SettingsInt['Plugin.SphinxSearch.' . $Name] = $Value; //use this to save integer values back to int since Form->Save will turn it
            //into a string which sphinx CANNOT use. It will issue warnings
        }
        $ConfigurationModel->SetField($Settings);
        // Set the model on the form.
        $Sender->Form->SetModel($ConfigurationModel);

        // If seeing the form for the first time...
        if ($Sender->Form->AuthenticatedPostBack() === FALSE) {
            // Apply the config settings to the form.
            $Sender->Form->SetData($ConfigurationModel->Data);
        } else {
            foreach ($this->Settings['Admin'] as $Name => $Default) {
                if (!strrpos($Name, 'Enable')) //don't need to require booleans
                    $ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.' . $Name, 'Required');
                if (is_numeric(C('Plugin.SphinxSearch.' . $Name))) { //if default value was an integer, we should expect any modification to it to also be an int
                    $ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.' . $Name, 'Integer');
                }
            }
            $Saved = $Sender->Form->Save();
            if (!$Sender->Form->Errors()) {
                foreach ($SettingsInt as $Name => $Value) {
                    if (is_int($Value)) {
                        SaveToConfig($Name, intval(C($Name)));
                    }
                }
                $Sender->StatusMessage = T("Your changes have been saved.");
            }
        }
        $Sender->Render($this->GetView('settings.php'));
    }

    /**
     * Populates the dropdown list for searching in certain categories -direct copy pretty
     * much out of the core of vanilla
     *
     * @param string $FieldName
     * @param mixed $Options
     * @return mixed dropdown list
     */
    public function CategoryDropDown($FieldName = 'forums[]', $Options = FALSE) {
        $Form = new Gdn_Form(); //get form object
        $Value = ArrayValueI('Value', $Options); // The selected category id
        $CategoryData = GetValue('CategoryData', $Options, CategoryModel::Categories());
        // Sanity check
        if (is_object($CategoryData))
            $CategoryData = (array) $CategoryData;
        else if (!is_array($CategoryData))
            $CategoryData = array();

        // Respect category permissions (remove categories that the user shouldn't see).
        $SafeCategoryData = array();
        foreach ($CategoryData as $CategoryID => $Category) {
            if ($Value != $CategoryID) {
                if ($Category['CategoryID'] < 0 || !$Category['PermsDiscussionsView'])
                    continue;
            }

            $SafeCategoryData[$CategoryID] = $Category;
        }
        // Opening select tag
        $Return = '<select';
        $Return .= ' multiple="multiple"';
        $Return .= ' id="SearchDropdown"';
        $Return .= ' name="' . $FieldName . '"';
        $Return .= ' size="7"';
        $Return .= ">\n";

        // Get value from attributes
        if ($Value === FALSE) {
            $Value = $Form->GetValue($FieldName);
            $Return .= '<option value="0" selected="selected">All</option>';
            $All = TRUE;
        } else {
            $Return .= '<option value="0">All</option>';
            $All = FALSE;
        }
        if (!is_array($Value)) {
            $Value = array($Value);
        }

        // Prevent default $Value from matching key of zero
        $HasValue = ($Value !== array(FALSE) && $Value !== array('')) ? TRUE : FALSE;

        // Start with null option?
        //$Return .= '<option value="0" selected="selected">All</option>';    //Put an "All" categories option
        // Show root categories as headings (ie. you can't post in them)?
        $DoHeadings = C('Vanilla.Categories.DoHeadings');

        // If making headings disabled and there was no default value for
        // selection, make sure to select the first non-disabled value, or the
        // browser will auto-select the first disabled option.
        $ForceCleanSelection = ($DoHeadings && !$HasValue);

        // Write out the category options
        if (is_array($SafeCategoryData)) {
            foreach ($SafeCategoryData as $CategoryID => $Category) {
                $Depth = GetValue('Depth', $Category, 0);
                $Disabled = $Depth == 1 && $DoHeadings;
                $Selected = in_array($CategoryID, $Value) && $HasValue;
                if ($ForceCleanSelection && $Depth > 1) {
                    $Selected = TRUE;
                    $ForceCleanSelection = FALSE;
                }

                $Return .= '<option value="' . $CategoryID . '"';
                if ($Disabled)
                    $Return .= ' disabled="disabled"';
                else if ($Selected && $All == FALSE)
                    $Return .= ' selected="selected"'; // only allow selection if NOT disabled

                $Name = GetValue('Name', $Category, 'Blank Category Name');
                if ($Depth > 1) {
                    $Name = str_pad($Name, strlen($Name) + $Depth - 1, ' ', STR_PAD_LEFT);
                    $Name = str_replace(' ', '&#160;', $Name);
                }

                $Return .= '>' . $Name . "</option>\n";
            }
        }
        return $Return . '</select>';
    }

    /**
     * These must correlate with class.sphinxsearchconfig.php
     */
    public function Setup() {
        //all of the settings
        foreach ($this->Settings as $Type) {
            foreach ($Type as $Name => $Default) {
                if (is_array($Default))
                    continue; //no arrays
                SaveToConfig('Plugin.SphinxSearch.' . $Name, $Default);
            }
        }


        //create shpinx table to tell the indexes what to index
        Gdn::Structure()
                ->Table('sph_counter')
                ->Column('counter_id', 'int', FALSE) //order matters here!
                ->Column('max_doc_id', 'int', FALSE)
                ->PrimaryKey('counter_id', 'int')
                ->Set();

        //for stats
        Gdn::Structure()
                ->Table('sph_stats')
                ->Column('id', 'int(11)', FALSE) //order matters here!
                ->Column('mode', 'int(11)', FALSE)
                ->Column('keywords', 'varchar(255)', FALSE, 'index')
                ->Column('keywords_full', 'varchar(255)', FALSE)
                ->Column('date_added', 'datetime', FALSE)
                ->PrimaryKey('id', 'int')
                ->Set();
    }

    public function OnDisable() {
        foreach ($this->Settings as $Type) {
            foreach ($Type as $Name => $Default) {
                if (is_array($Default))
                    continue; //no arrays
                RemoveFromConfig('Plugin.SphinxSearch.' . $Name);
            }
        }
        Gdn::Structure()
                ->Table('sph_counter')
                ->Drop();
        Gdn::Structure()
                ->Table('sph_stats')
                ->Drop();
    }

}
