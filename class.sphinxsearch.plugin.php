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
    'Name' => 'Sphinx Search',
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

class SphinxSearchPlugin extends Gdn_Plugin {

    public $AlreadySent; //hack to workaround lack of handlers to hook into for rendering this
    //Define these here to use on the view and for validation purposes
    //DON'T MODIFY THESE UNDER PAIN OF DEATH!
    public $Match = array('Any', 'All', 'Extended'); //Search Match Mode
    public $Order = array(0 => 'Relevance', 1 => 'Most Recent', 2 => 'Most Views', 3 => 'Most Replies'); //Search Order - the key corresponds to value on radio list
    public $ResultFormat = array('Threads', 'Posts');
    public $Sender;
    public $Started = FALSE;
    public $Handle = '';
    public $SphinxClient;
    public $Queries = array(); //keep track of queries
    public $Settings = array();

    public function __construct() {

        ////////////////////////////////////////////////
        //Sphinx plugin core modules
        /////////////////////////////////////////////////
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'sphinxconstants.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchgeneral.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxfactory.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchsettings.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'sphinxapi.php'); //load the Sphinx API file
        ////////////////////////////////////////////////
        //Sphinx views used in the widgets
        /////////////////////////////////////////////////
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'widgets' . DS . 'views' . DS . 'helper_functions.php');

        foreach (glob(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'widgets' . DS . '*.php') as $filename) {
            include $filename; //inclue all of the widget classes
        }


        $this->SphinxClient = new SphinxClient(); //sphinx API
        $Settings = SphinxFactory::BuildSettings();
        $this->Settings = $Settings->GetAllSettings();
    }

    public function Base_Render_Before($Sender) {
        //'discussioncontroller','categoriescontroller','discussionscontroller','profilecontroller',
        //'activitycontroller','draftscontroller','messagescontroller', searchcontroller
        //
        //
        //Add your handlers in here
        switch ($Sender->ControllerName) {
            case 'discussioncontroller':
                //related discussion widget
                $Thread = $Sender->Discussion->Name;
                $Related = new SphinxWidgetRelatedDiscussion($this->SphinxClient);
                $RelatedQuery = $Related->AddQuery(array('DiscussionName' => $Thread));
                $Sender->AddCssFile('/plugins/SphinxSearch/design/widgetrelateddiscussion.css');
                $this->Queries[] = $RelatedQuery;
                break;
            case 'searchcontroller':
                //main search
                $Main = new SphinxWidgetMain($this->SphinxClient);
                $MainQueries = $Main->AddQuery();
                $this->Queries[] = $MainQueries;

                $Related = new SphinxWidgetRelatedMain($this->SphinxClient);
                $Related = $Related->AddQuery();
                $this->Queries[] = $Related;
                break;
            case 'postcontroller':
                $Sender->AddCssFile('/plugins/SphinxSearch/design/widgetrelateddiscussion.css');
                break;
        }
        $this->RunSearch($Sender);
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

                    foreach ($Result['matches'] as $Info) {
                        if (isset($Info['attrs']))
                            $ResultDocs[] = Gdn_Format::ArrayAsObject($Info['attrs']); //get the result documents
                    }
                    $Words = '';
                    foreach ($Result['words'] as $Word => $Info) {
                        $Words.= $Word . ' '; //get the submitted input query
                    }
                    $Results[$Index]['query'] = $Words; //add the query back into the resuls array

                    if ($Highlight) // are we to highlight some portions?
                        $ResultDocs = $this->HighLightResults($ResultDocs, $Words); //@todo repeated function in this object as well as sphinxwidgets
                    $Results[$Index]['matches'] = $ResultDocs; //replace highlighted docs back into the main results array
                }
                if ($Sender != FALSE)
                    $Sender->SetData($Name, $Results[$Index]);
                else
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
        if (isset($_GET['action'])) {
            if ($_GET['action'] == 'ViewFile') {
                if ($_GET['file'] == 'conf')
                    $File = C('Plugin.SphinxSearch.InstallPath') . DS . 'sphinx' . DS . 'etc' . DS . 'sphinx.conf';
                else if ($_GET['file'] == 'main')
                    $File = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'cron' . DS . 'cron.reindex.main.php';
                else if ($_GET['file'] == 'delta')
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

        $Sphinx = SphinxFactory::BuildSphinx($Sender, $this->GetView('index.php'));
        //$test->ReIndexMain();
        //$test->Stop();
        //$test->Start();
        $Sphinx->Status();
        $Validation = new Gdn_Validation();
        $ConfigurationModel = new Gdn_ConfigurationModel($Validation);
        $ConfigurationModel->SetField(array(
            'Plugin.SphinxSearch.MaxQueryTime' => 2000,
        ));
        // $Sender->AddJsFile('jquery.searchdropdown.js', 'plugins/SphinxSearch/');
        // Set the model on the form.
        $Sender->Form->SetModel($ConfigurationModel);
        $Sender->SetData('Settings', $Sphinx->GetSettings());
        $Sender->Render($this->GetView('index.php'));
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

        $Sphinx = SphinxFactory::BuildSphinx($Sender, $this->GetView('index.php'));
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
        $Sender->Render($this->GetView('index.php'));
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
        if ($this->AlreadySent != 1 && $Sender->SelfUrl != 'search/results') { //in order to elminate nesting this over and over again as well as allowing result page to render
            $this->AlreadySent = 1;
            $Sender->SetData('Match', $this->Match);
            $Sender->SetData('Order', $this->Order);
            $Sender->SetData('ResultFormat', $this->ResultFormat);
            $Sender->AddCssFile('/plugins/SphinxSearch/design/sphinxsearch.css');
            $Sender->Render($this->GetView('search.php'));
            die; //necessary to prevent duplicate
        }
    }

    /**
     * Adds the related threads to the top of each discussion
     * @param type $Sender
     */
    public function DiscussionController_BeforeDiscussion_Handler($Sender) {

    }

    /**
     * Adds the related threads to the top of each discussion
     * @param type $Sender
     * @pre RunSearch() ran already
     */
    public function DiscussionController_AfterDiscussion_Handler($Sender) {
        if (isset($Sender->Data['Related_Discussion'])) { //are there any related threads to display?
            $Related = new SphinxWidgetRelatedDiscussion($this->SphinxClient);
            $Results = $Sender->Data['Related_Discussion'];
            echo $Related->ToString($Results);
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
     * Interrupt the default search engine
     *
     * @param object $Sender
     *
     * @warning THE DEFAULT ENGINE WILL STILL EXECUTE QUERYIES!!
     * @todo prevent this
     *
     */
    public function SearchModel_Search_Handler($Sender) {

    }

    public function PluginController_autocompletemember_create() {
        $Match = mysql_escape_string($_GET['term']); //the text from the input field
        echo $this->_SearchUserName($Match);
        //echo '[{"value":"Nirvana"},{"value":"Pink Floyd"}]';
    }

    /**
     * Main entry point after clicking "Search"
     */
    public function SearchController_results_Create($Sender) {
        $Sender->AddCssFile('/plugins/SphinxSearch/design/sphinxsearchresult.css');
        $Sender->Render(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'widgets' . DS . 'views' . DS . 'results.php');
    }

    /**
     *
     * Highlights results based on the given string to match results to
     *
     * Cannot use the distributed index for  'BuildExcerpts'...must specify
     * one of its local parts. Since both of the local indexes use the same
     * tokenizing, exceptions, wordforms, N-grams etc, we can just select one
     *
     * @param array $Result
     * @param string $Query
     * @param inte $Limit
     * @param int $Offset
     * @return type
     */
    private function HighLightResults($Result, $Query, $Limit = 0, $Offset = 0) {
        if (!isset($Result))
            return FALSE;               //no records
        $Records = $Result;
        $CommentArray = array();  //comment body
        foreach ($Records as $Record) {
            $CommentArray[] = $Record->{SPHINX_FIELD_STR_COMMENTBODY};
        }
        $HighLightedComment = $this->SphinxClient->BuildExcerpts(
                $CommentArray, SPHINX_INDEX_MAIN, $Query, $options = $this->Settings['Admin']->BuildExcerpts
        );
        if ($this->Settings['Admin']->HighLightTitles) {
            $DiscussionArray = array(); //discussion titles
            foreach ($Records as $Record) {
                $DiscussionArray[] = $Record->{SPHINX_ATTR_STR_DISCUSSIONNAME};
            }
            $HighLightedDiscussion = $this->SphinxClient->BuildExcerpts(
                    $DiscussionArray, SPHINX_INDEX_MAIN, $Query, $options = array(
                'before_match' => $this->Settings['Admin']->BuildExcerpts['before_match'],
                'after_match' => $this->Settings['Admin']->BuildExcerpts['after_match'])
            ); //don't put any restrictions on anything else here except the opening/closing tags
        }

        $Offset = 0;
        foreach ($Records as $Record) {
            $Record->{SPHINX_FIELD_STR_COMMENTBODY} = $HighLightedComment[$Offset];
            if ($this->Settings['Admin']->HighLightTitles)
                $Record->{SPHINX_ATTR_STR_DISCUSSIONNAME} = $HighLightedDiscussion[$Offset];
            $Offset++;
        }
        return $Records;
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
        ///////////////////////////////////////////////////////////////
        //Wizard steps
        ///////////////////////////////////////////////////////////////
        SaveToConfig('Plugin.SphinxSearch.StartWizard', FALSE);
        SaveToConfig('Plugin.SphinxSearch.Connection', FALSE);
        SaveToConfig('Plugin.SphinxSearch.Detection', FALSE);
        SaveToConfig('Plugin.SphinxSearch.Detected', FALSE); //whether or not system detected existance of sphinx or not (FALSE if did not)
        SaveToConfig('Plugin.SphinxSearch.Running', FALSE); //searchd start/stopped
        SaveToConfig('Plugin.SphinxSearch.Installed', FALSE); //either found existing binaries or succesfull install


        SaveToConfig('Task', 'Configure');
        SaveToConfig('Plugin.SphinxSearch.ServicePollTask', FALSE);

        ////////////////////////////////////////////////////////////
        //status
        ///////////////////////////////////////////////////////////////
        //searchd port
        SaveToConfig('Plugin.SphinxSearch.SearchdPortStatus', FALSE); //if port is Open/Closed
        SaveToConfig('Plugin.SphinxSearch.SearchdStatus', FALSE); //if daemon is running or not
        SaveToConfig('Plugin.SphinxSearch.SearchdConnections', 0);

        //general status
        SaveToConfig('Plugin.SphinxSearch.IndexerFound', FALSE);
        SaveToConfig('Plugin.SphinxSearch.SearchdFound', FALSE);
        SaveToConfig('Plugin.SphinxSearch.ConfFound', FALSE);
        SaveToConfig('Plugin.SphinxSearch.Uptime', 0);
        SaveToConfig('Plugin.SphinxSearch.TotalQueries', 0);
        SaveToConfig('Plugin.SphinxSearch.MaxedOut', 0);

        //total number of docs indexed
        SaveToConfig('Plugin.SphinxSearch.IndexerMainTotal', 0);
        SaveToConfig('Plugin.SphinxSearch.IndexerDeltaTotal', 0);
        SaveToConfig('Plugin.SphinxSearch.IndexerStatsTotal', 0);

        //last time it was indexed
        SaveToConfig('Plugin.SphinxSearch.IndexerMainLast', '---');
        SaveToConfig('Plugin.SphinxSearch.IndexerDeltaLast', '---');
        SaveToConfig('Plugin.SphinxSearch.IndexerStatsLast', '---');


        ///////////////////////////////////////////////////////////////
        //Install settings
        ///////////////////////////////////////////////////////////////
        SaveToConfig('Plugin.SphinxSearch.Prefix', 'vss_');
        SaveToConfig('Plugin.SphinxSearch.Host', 'localhost');
        SaveToConfig('Plugin.SphinxSearch.Port', 9312);
        SaveToConfig('Plugin.SphinxSearch.InstallPath', SPHINX_SEARCH_INSTALL_DIR); //path to sphinx install directory
        SaveToConfig('Plugin.SphinxSearch.IndexerPath', 'Not Detected'); //path to indexer - use this for config purposes!
        SaveToConfig('Plugin.SphinxSearch.SearchdPath', 'Not Detected'); //path to searchd - use this for config purposes!
        SaveToConfig('Plugin.SphinxSearch.ConfPath', 'Not Detected'); //path to searchd - use this for config purposes!

        SaveToConfig('Plugin.SphinxSearch.ManualIndexerPath', ''); //manual path to indexer
        SaveToConfig('Plugin.SphinxSearch.ManualSearchdPath', ''); //manual path to searchd
        SaveToConfig('Plugin.SphinxSearch.ManualConfPath', ''); //manual path to sphinx.conf
        ///////////////////////////////////////////////////////////////
        //admin settings
        ///////////////////////////////////////////////////////////////
        SaveToConfig('Plugin.SphinxSearch.LimitRelatedMain', 20); //# of related discussion titles next to the main search results
        SaveToConfig('Plugin.SphinxSearch.LimitRelatedPost', 20);
        SaveToConfig('Plugin.SphinxSearch.LimitRelatedDiscussion', 20);

        SaveToConfig('Plugin.SphinxSearch.MinWordIndexLen', 3); //minimum characters to index a word
        SaveToConfig('Plugin.SphinxSearch.MaxMatches', 1000); //per query
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

        //for stats
        Gdn::Structure()
                ->Table('sph_stats')
                ->Column('int', 'int', FALSE) //order matters here!
                ->Column('keywords', 'varchar(255)', FALSE, 'index')
                ->Column('date_added', 'datetime', FALSE)
                ->Column('keywords_full', 'tinyint(255)', FALSE)
                ->Column('status', 'varchar(255)', FALSE)
                ->PrimaryKey('id', 'int')
                ->Set();
    }

    public function OnDisable() {
        RemoveFromSettings('Plugin.SphinxSearch.MaxQueryTime');
        RemoveFromSettings('Plugin.SphinxSearch.Host');
        RemoveFromSettings('Plugin.SphinxSearch.Port');
        RemoveFromSettings('Plugin.SphinxSearch.Timeout');
        RemoveFromSettings('Plugin.SphinxSearch.RetiresCount');
        RemoveFromSettings('Plugin.SphinxSearch.RetriesDelay');
        RemoveFromSettings('Plugin.SphinxSearch.MinWordIndexLen');


        Gdn::Structure()
                ->Table('sph_counter')
                ->Drop();
    }

}
