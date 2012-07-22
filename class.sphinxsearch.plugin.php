<?php

if (!defined('APPLICATION'))
    exit();
/*
 * This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

$PluginInfo['SphinxSearch'] = array(
    'Name' => 'SphinxSearch',
    'Description' => 'A much improved search experience based on the Sphinx Search Engine',
    'Version' => '20120420',
    'RequiredApplications' => array('Vanilla' => '2.0'),
    'RequiredTheme' => FALSE,
    'RequiredPlugins' => FALSE,
    'HasLocale' => TRUE,
    'SettingsUrl' => '/plugin/sphinxsearch',
    'SettingsPermission' => 'Garden.AdminUser.Only',
    'Author' => "mcuhq",
    'AuthorEmail' => 'info@mcuhq.com',
    'AuthorUrl' => 'http://mcuhq.com'
);

class SphinxSearchPlugin extends Gdn_Plugin implements SplSubject {

    public $AlreadySent; //hack to workaround lack of handlers to hook into for rendering this
    public $Started = FALSE;
    public $Handle = '';
    public $Widgets = array();
    public $SphinxClient;
    public $Queries = array(); //keep track of queries
    public $Settings = array();
    public $_Status = array(); //log of stuff that is happening
    public $_Storage = array(); //storage of objects that are subscribed

    public function __construct() {
        error_reporting(E_ALL);

        ////////////////////////////////////////////////
        //Sphinx plugin core modules
        /////////////////////////////////////////////////
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'sphinxconstants.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchgeneral.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxfactory.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchsettings.php');
        //include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.searchmodel.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'sphinxapi.php'); //load the Sphinx API file
        ////////////////////////////////////////////////
        //Sphinx views used in the widgets
        /////////////////////////////////////////////////
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'widgets' . DS . 'views' . DS . 'helper_functions.php'); //widgets use this

        $this->SphinxClient = new SphinxClient(); //sphinx API
        $Settings = SphinxFactory::BuildSettings();
        $this->Settings = $Settings->GetAllSettings();
        //create subclasses
        $this->_Storage = new SplObjectStorage();

//        foreach($this->Settings['Admin'] as $Name=>$Default){
//            SaveToConfig('Plugin.SphinxSearch.'.$Name,$Default);
//        }

        if ($this->Settings['Status']->SearchdRunning && $this->Settings['Status']->SearchdPortStatus) { //check if sphinx is running
            $this->RegisterWidgets();
            $this->RegisterModules();
            $Service = new SphinxSearchService($this->Settings);
            $Service->Status(); //update the status
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
        $Sender->AddCssFile('/plugins/SphinxSearch/design/widgetrelateddiscussion.css'); //for the tooltip as well
        $this->QueryWidgets($Sender);
        $Sender->AddJsFile('jquery.hoverintent.js', 'plugins' . DS . 'SphinxSearch' . DS); //tooltip
        $Sender->AddJsFile('jquery.tooltip.js', 'plugins' . DS . 'SphinxSearch' . DS); //tooltip
        ///////////////This runs sphinx !!!////////////////////
        $Results = $this->RunSearch($Sender); //only 1 search call is made...use sphinxclient->AddQuery(...)
        ///////////////////////////////////////////////////////

        $this->Update($Results, $Sender); //update the widgets notifiying of results
        $Sender->SetData('Settings', $this->Settings);
    }

    private function RunSearch($Sender = FALSE) {
        $FinalResults = array();
        $Results = $this->SphinxClient->RunQueries(); //perform all of the queries
        //print_r($Results); die;
        if ($Results) {
            foreach ($this->Queries as $Query) {
                $ResultDocs = array();
                $Index = $Query['Index'];
                $Name = $Query['Name'];
                $Highlight = $Query['Highlight'];
                $IgnoreFirst = $Query['IgnoreFirst']; //useful for finding related content given the title (don't return the searched for title)

                $Result = $Results[$Index]; //get the individual query result from the results tree

                if ($Result['error'] == FALSE && $Result['total_found'] != 0) { //no errors
                    if ($IgnoreFirst)
                        array_shift($Result['matches']);

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
                //if ($Sender != FALSE)
                //    $Sender->SetData($Name, $Results[$Index]);
                // else
                $FinalResults[$Name] = $Results[$Index];
            }
        }
        //PRINT_R($Results); die;
        return $FinalResults;
    }

    public function PluginController_SphinxSearch_Create($Sender) {
        $Sender->Title('Sphinx Search');
        $Sender->Permission('Garden.Settings.Manage');
        $Sender->AddSideMenu('plugin/sphinxsearch'); //add the left side menu
        $Sender->Form = new Gdn_Form();

        $this->Dispatch($Sender, $Sender->RequestArgs);
    }

    /**
     * Enter here to view sphinx.conf or cron files
     */
    public function Controller_ViewFile($Sender) {
        $Sender->Permission('Garden.Settings.Manage');
        if (isset($_GET['action'])) {
            if ($_GET['action'] == 'viewfile') {
                if ($_GET['file'] == 'conf')
                    $File = C('Plugin.SphinxSearch.InstallPath') . DS . 'sphinx' . DS . 'etc' . DS . 'sphinx.conf';
                else if ($_GET['file'] == 'maincron')
                    $File = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'cron' . DS . 'cron.reindex.main.php';
                else if ($_GET['file'] == 'deltacron')
                    $File = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'cron' . DS . 'cron.reindex.delta.php';
                if (!file_exists($File))
                    echo ('File does not exist here: ' . $File);
                else {
                    echo nl2br(file_get_contents($File));
                }
            }
        }
    }

    public function Controller_ServicePoll() {
        $Return = array();
        $Output = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'install' . DS . 'output.txt');
        $Return['Terminal'] = nl2br($Output);
        $Error = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'install' . DS . 'error.txt');
        $Return['Terminal'] .= nl2br('<span class="TermWarning">' . $Error) . '</span>';
        $PID = explode("\n", trim(file_get_contents(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'install' . DS . 'pid.txt')));
        $PID = $PID[0]; //get only the first PID

        $Task = C('Plugin.SphinxSearch.ServicePollTask');
        switch ($Task) {
            case 'Idle':
                $Return['Status'] = 'Idle';
                break;
            case 'IndexMain':
                $Return['Status'] = 'Index Main';
                break;
            case 'IndexDelta':
                $Return['Status'] = 'Index Delta';
                break;
            case 'IndexStats':
                $Return['Status'] = 'Index Stats';
                break;
        }

        if (!SphinxSearchGeneral::isRunning($PID)) { //check if finished
            //yes it is finished
            if ($Return['Status'] != 'Idle')
                $Return['Status'] .= ' - Finished!';
            else {
                SphinxSearchGeneral::ClearLogFiles();
                $Return['Terminal'] = 'Ready';
            }
            SaveToConfig('Plugin.SphinxSearch.ServicePollTask', 'Idle');
        }

        echo json_encode($Return);
    }

    /**
     * Entry point to request status on any commands running in the background
     *
     * This is used
     */
    public function Controller_InstallPoll() {
        $Return = array();
        $Output = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'install' . DS . 'output.txt');
        $Return['Terminal'] = nl2br($Output);
        $Error = file_get_contents(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'install' . DS . 'error.txt');
        $Return['Terminal'] .= nl2br('<span class="TermWarning">' . $Error) . '</span>';
        $PID = explode("\n", trim(file_get_contents(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'install' . DS . 'pid.txt')));
        $PID = $PID[0]; //get only the first PID
        if (!SphinxSearchGeneral::isRunning($PID)) { //check if finished
            //yes it is finished
            $Task = C('Plugin.SphinxSearch.Task');
            $InstallPath = C('Plugin.SphinxSearch.InstallPath');
            $Dir = C('Plugin.SphinxSearch.InsideDir');
            if ($Task == 'FinishLibraries' && ($Task == FALSE || !file_exists($InstallPath) || !is_dir($Dir))) //check if paths are OK
                $Return['Terminal'] .= '<span class="TermWarning">Error locating install folders </span>';
            else {
                $Wizard = new SphinxSearchInstall();
                switch ($Task) {
                    case 'Idle':
                        $Return['Status'] = 'Idling';
                        break;
                    case 'Start':
                        if ($Error != '') {
                            $Return['Terminal'] .= '<span class="TermWarning">Unable to Proceed due to the following errors:</span> <br/>';
                            break;
                        }
                        $Wizard->InstallConfigure($InstallPath . DS . 'sphinx', $Dir);
                        SaveToConfig('Plugin.SphinxSearch.Task', 'Configure');
                        break;
                    case 'Configure':
                        if ($Error != '') {
                            $Return['Terminal'] .= '<span class="TermWarning">Unable to Proceed due to the above errors</span> <br/>';
                            break;
                        }
                        $Wizard->InstallMake($Dir);
                        SaveToConfig('Plugin.SphinxSearch.Task', 'Make');
                        break;
                    case 'Make':
                        $Wizard->InstallMakeInstall($Dir);
                        SaveToConfig('Plugin.SphinxSearch.Task', 'Make Install');
                        break;
                    case 'Make Install':
                        if (!$Wizard->CheckIndexer($InstallPath . DS . 'sphinx' . DS . 'bin')) {
                            $Return['Terminal'] .= '<span class="TermWarning">Unable to Find instance of Indexer</span> <br/>';
                            break;
                        }
                        if (!$Wizard->CheckSearchd($InstallPath . DS . 'sphinx' . DS . 'bin')) {
                            $Return['Terminal'] .= '<span class="TermWarning">Unable to Find instance of Searchd</span> <br/>';
                            break;
                        }
                        $Wizard->SaveLocations(); //save indexer/searchd/conf locations

                        SaveToConfig('Plugin.SphinxSearch.Task', 'FinishLibraries');
                        break;
                    case 'FinishLibraries':
                        if (C('Plugin.SphinxSearch.Config') == FALSE)
                            $Return['Terminal'] = 'reload'; //tell Jquery to reload the page
                        else
                            SaveToConfig('Plugin.SphinxSearch.Task', 'InstallConfig');
                        break;
                    case 'InstallConfig':
                        if (C('Plugin.SphinxSearch.Installed'))
                            $Return['Status'] = '<span style="color: green"> Finished Installing libraries/config/cron for Sphinx!</span>';
                        else
                            $Return['Status'] = 'Waiting to install configuration';
                        break;
                }
                if (C('Plugin.SphinxSearch.Task') == 'FinishLibraries')
                    $Return['Status'] = '<span style="color: green"> Finished Installing libraries for Sphinx!</span>';
            }
        } else {
            $Return['Status'] = 'Please Wait For: ' . '<b>' . C('Plugin.SphinxSearch.Task') . '</b><br/>' . C('Plugin.SphinxSearch.PIDBackgroundWorker');
        }

        echo json_encode($Return);
    }

    public function Controller_Index($Sender) {
        $Sender->Permission('Vanilla.Settings.Manage');

        $Sender->SetData('PluginDescription', $this->GetPluginKey('Description'));
        $Sender->SetData('PluginVersion', $this->GetPluginKey('Version'));

        $Sphinx = SphinxFactory::BuildSphinx($Sender, $this->getview('sphinxsearch.php'));
        //$test->ReIndexMain();
        //$test->Stop();
        //$test->Start();
        $Sphinx->Status();
        $Sender->SetData('Settings', $Sphinx->GetSettings());
        $Sender->Render($this->getview('sphinxsearch.php'));
    }

    public function Controller_SphinxFAQ($Sender) {
        // Prevent non-admins from accessing this page
        $Sender->Permission('Vanilla.Settings.Manage');

        $Sender->SetData('PluginDescription', $this->GetPluginKey('Description'));
        $Sender->Render($this->GetView('faq.php'));
    }

    /**
     * main entry point to poll the status of sphinx such
     * as reindexing/start/stop/rotate/etc
     *
     * @param object $Sender
     */
    public function Controller_Service($Sender) {
        $Sender->Permission('Vanilla.Settings.Manage');
        $Sender->SetData('PluginDescription', $this->GetPluginKey('Description'));
        $Sender->SetData('PluginVersion', $this->GetPluginKey('Version'));

        $Sphinx = SphinxFactory::BuildSphinx($Sender, $this->getview('sphinxsearch.php'));
        $Sphinx->ValidateInstall();
        switch ($_GET['Action']) {
            case 'IndexMain':
                $Sphinx->ReIndexMain();
                break;
            case 'IndexDelta':
                $Sphinx->ReIndexDelta();
                break;
            case 'IndexStats':
                $Sphinx->ReIndexStats();
                break;
            case 'StartSearchd':
                $Sphinx->Start();
                break;
            case 'StopSearchd':
                $Sphinx->Stop();
                break;
        }
        $Sphinx->Status();
        $Sender->SetData('Settings', $Sphinx->GetSettings());
        $Sender->Render($this->getview('sphinxsearch.php'));
    }

    /**
     * main entry point for the install wizard
     * input parameters for wizard are in the $_GET buffer
     * @param type $Sender
     */
    public function Controller_InstallWizard($Sender) {
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchinstallwizard.php');
        $Wizard = new SphinxSearchInstallWizard($Sender, $this->GetView('wizard.php'));
        $Wizard->Index();
    }

    /**
     *
     * @return type
     */
    public function Controller_NewDiscussion() {
        $Return = array();
        if (isset($_POST['Query']))
            $Query = $_POST['Query'];
        else
            return;

        $RelatedPost = new SphinxWidgetRelatedPost($this->SphinxClient);
        $Related = $RelatedPost->AddQuery(array('Query' => $Query)); //now actually adding the query
        $this->Queries[] = $Related;
        $Results = $this->RunSearch();

        $Results = $Results['Related_Post'];
        $Return['Text'] = $RelatedPost->ToString($Results);

        echo json_encode($Return);
    }

    public function SearchController_Render_Before($Sender) {
        //In order for the default search engine not to run, we will kill PHP from processing
        //after the sphinx view is loaded
        if ($this->Settings['Status']->SearchdRunning && $this->Settings['Status']->SearchdPortStatus) { //exit if sphinx is not running
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
                    $Sender->AddCssFile('/plugins/SphinxSearch/design/sphinxsearch.css');

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
                    $Sender->AddCssFile('/plugins/SphinxSearch/design/sphinxsearchresult.css');
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
        // include $Sender->FetchViewLocation('QnAPost', '', 'plugins/QnA');
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
     * Enable/Disable Flagging.
     */
    public function Controller_Toggle($Sender) {

        // Enable/Disable Content Flagging
        if (Gdn::Session()->ValidateTransientKey(GetValue(1, $Sender->RequestArgs))) {
            $Option = $_GET['action'];
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
            if ($Saved) {
                //print_r($SettingsInt); die;
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
     * Populates the dropdown list for searching in certain categories
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
        //@todo do this all in one sweep with $this->Settings
        //admin settings
        foreach ($this->Settings['Admin'] as $Name => $Default) {
            SaveToConfig('Plugin.SphinxSearch.' . $Name, $Default);
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
                ->Column('keywords', 'varchar(255)', FALSE, 'index')
                ->Column('keywords_full', 'varchar(255)', FALSE)
                ->Column('date_added', 'datetime', FALSE)
                ->PrimaryKey('id', 'int')
                ->Set();
    }

    public function OnDisable() {
        foreach ($this->Settings['Admin'] as $Name => $Default) {
            RemoveFromSettings($Name);
        }
        Gdn::Structure()
                ->Table('sph_counter')
                ->Drop();
        Gdn::Structure()
                ->Table('sph_stats')
                ->Drop();
    }

}
