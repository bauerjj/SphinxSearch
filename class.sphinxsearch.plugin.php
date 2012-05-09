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
    'Version' => 'A20120420',
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
    /*
     * The following are what are indexed/stored
     *
     * # Field strings are BOTH indexed AND stored

      sql_field_string = CommentBody
      sql_field_string = DiscussionName
      sql_field_string = UserName

      # Attributes are NOT indexed, but stored

      sql_STR_timestamp = DiscussionDateUpdated
      sql_STR_string = DiscussionCountViews
      sql_STR_string = DiscussionCountComments

      sql_STR_timestamp = CommentDateInserted
      sql_STR_timestamp = CommentDateUpdated
      sql_STR_uint = CatID
      sql_STR_uint = CatCountDiscussions
      sql_STR_uint =  CatCountComments
      sql_STR_uint = CatLastCommentID


      sql_STR_string = CatName
      sql_STR_string = CatUrlCode
      sql_STR_string = CatDescription
     */

    //Wizard Install Variables

    var $AlreadySent; //hack to workaround lack of handlers to hook into for rendering this
    //Define these here to use on the view and for validation purposes
    //DON'T MODIFY THESE UNDER PAIN OF DEATH!
    var $Match = array('Any', 'All', 'Extended'); //Search Match Mode
    var $Order = array(0 => 'Relevance', 1 => 'Most Recent', 2 => 'Most Views', 3 => 'Most Replies'); //Search Order - the key corresponds to value on radio list
    var $ResultFormat = array('Threads', 'Posts');
    var $Sender;

    public function __construct() {
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchmodel.php');
        $this->SphinxSearchModel = new SphinxSearchModel();
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsettings.php');
        $this->SphinxSettings = new SphinxSettings();
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'sphinxconstants.php');
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'class.sphinxsearchgeneral.php');

    }

    public function Base_Render_Before($Sender) {

        $Sender->AddCssFile('/plugins/SphinxSearch/design/searchdropdown.css'); //dropdown menu add-on
        $Sender->AddJsFile('jquery.searchdropdown.js', 'plugins/SphinxSearch/');
    }

    public function PluginController_SphinxSearch_Create($Sender) {
        $Sender->Title('Sphinx Search');
        $Sender->Permission('Garden.Settings.Manage');
        $Sender->AddSideMenu('plugin/sphinxsearch'); //add the left side menu
        $Sender->Form = new Gdn_Form();

        $this->Dispatch($Sender, $Sender->RequestArgs);
    }

    public function Controller_Index($Sender) {
        // Prevent non-admins from accessing this page
        $Sender->Permission('Vanilla.Settings.Manage');

        $Sender->SetData('PluginDescription', $this->GetPluginKey('Description'));
        $Sender->SetData('PluginVersion', $this->GetPluginKey('Version'));

        $Validation = new Gdn_Validation();
        $ConfigurationModel = new Gdn_ConfigurationModel($Validation);
        $ConfigurationModel->SetField(array(
            'Plugin.SphinxSearch.MaxQueryTime' => 2000,
        ));

        // Set the model on the form.
        $Sender->Form->SetModel($ConfigurationModel);

        // If seeing the form for the first time...
        if ($Sender->Form->AuthenticatedPostBack() === FALSE) {
            // Apply the config settings to the form.
            $Sender->Form->SetData($ConfigurationModel->Data);
        } else {
            $ConfigurationModel->Validation->ApplyRule('Plugin.SphinxSearch.MaxQueryTime', 'Required');
            $Saved = $Sender->Form->Save();
            if ($Saved) {
                $Sender->StatusMessage = T("Your changes have been saved.");
            }
        }
        $Sender->Render($this->GetView('index.php'));
    }

    public  function Controller_SphinxFAQ($Sender){
        // Prevent non-admins from accessing this page
        $Sender->Permission('Vanilla.Settings.Manage');

        $Sender->SetData('PluginDescription', $this->GetPluginKey('Description'));
         $Sender->Render($this->GetView('faq.php'));
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


    public function SearchController_Render_Before($Sender) {
        //In order for the default search engine not to run, we will kill PHP from processing
        //after the sphinx view is loaded
        if ($this->AlreadySent != 1 && $Sender->SelfUrl != 'search/results') { //in order to elminate nesting this over and over again as well as allowing result page to render
            $this->AlreadySent = 1;
            $Sender->SetData('Match', $this->Match);
            $Sender->SetData('Order', $this->Order);
            $Sender->SetData('ResultFormat', $this->ResultFormat);
            $this->_LoadMainSearch($Sender);
            die; //necessary to prevent duplicate
        }
    }

    private function _LoadResultSearch($Result, $Inputs) {
        //print_r($Sender);
        $this->Sender->AddCssFile('/plugins/SphinxSearch/design/sphinxsearchresult.css');
        $this->Sender->SetData('Result', $Result); //Sphinx results
        $this->Sender->SetData('Inputs', $Inputs); //user input values (sanitized)
        //print_r($Result); die;
        $this->Sender->Render($this->GetView('results.php'));
    }

    private function _LoadMainSearch($Sender) {

        $Sender->AddCssFile('/plugins/SphinxSearch/design/sphinxsearch.css');
        //$Sender->AddCssFile('/plugins/SphinxSearch/design/jquery.multiselect.css'); @todo why does multiselect not work?
        //$Sender->AddJsFile('jquery.multiselect.min.js', 'plugins/SphinxSearch/');
        $Sender->Render($this->GetView('search.php'));
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

    private function _SearchUserName($Input) {
        $SearchField = 'UserName';

        $this->SphinxSearchModel->SphinxReset();
        $this->SphinxSearchModel->SphinxSetMatch(SPH_MATCH_EXTENDED2); //use this since using boolean operators
        $this->SphinxSearchModel->SetSelect('UserName'); //only need the username
        $this->SphinxSearchModel->SphinxSetSort(SPH_SORT_RELEVANCE);
        $this->SphinxSearchModel->SphinxSetGroupDistinct($SearchField); //only want one unique username
        $this->SphinxSearchModel->SphinxSetGrouping($SearchField, SPH_GROUPBY_ATTR);
        $this->SphinxSearchModel->SphinxSetLimits($Offset = 0, $Limit = 15); //limit to 15 autocompletes
        $Result = $this->SphinxSearchModel->SphinxSearch('@(UserName) ' . $Input, $index = 'vanilla'); //perform the search
        return $this->_JsonEncode($Result['Records'], 'username');
    }

    private function _MainSearch($Result) {
        $this->SphinxSearchModel->SphinxReset();      //reset grouping/indexes$t
        $this->SphinxSearchModel->SphinxSetMatch(SPH_MATCH_EXTENDED); //set matching mode - Should always use this!
        $SubQuery = '';

        if (!empty($Result['ForumList'])) {       //filster Forum categories
            if (!in_array(0, $Result['ForumList']))  //If this is TRUE, than user selected to search in "All" categories...no filtering then requried
                $this->SphinxSearchModel->SphinxSetFilter('CatID', $Result['ForumList']);
        }
        if (!empty($Result['MemberList'])) {      //filter by member
            $String = $this->_OperatorOrSearch($Result['MemberList']);
            $SubQuery .='@(UserName) ' . $String;
        }
        $Query = $this->_SetRankAndQuery($Result['Match'], $Result['Query']); //depending on selected match, need to format the query string to comply with the extended syntax
        $this->_SetSort($Result['Order']);

//        if($Result['Query'] =='*' || $Result['Query'] == '')
        $this->SphinxSearchModel->SphinxSetLimits($Offset = 0, $Limit = 1000, $MaxMatches = 0);
        if ($Result['TitlesOnly'] == 1) {
            $this->SphinxSearchModel->SphinxSetGroupDistinct('DiscussionName'); //only want one unique thread title
            $this->SphinxSearchModel->SphinxSetGrouping('DiscussionName', SPH_GROUPBY_ATTR);
            $MainSearch = $this->_FieldSearch(
                    $Query, array(
                SPHINX_FIELD_STR_DISCUSSIONNAME), $Multiple = FALSE); //perform the search
        } else {

            //$this->SphinxSearchModel->SphinxSetGroupDistinct('discussionname'); //only want one unique username
            //$this->SphinxSearchModel->SphinxSetGrouping('discussionname', SPH_GROUPBY_ATTR);
            //$this->SphinxSearchModel->SphinxSetFilter('TableID', array(1));
            $MainSearch = $this->_FieldSearch(
                    $Query, array(
                SPHINX_FIELD_STR_DISCUSSIONNAME,
                SPHINX_FIELD_STR_COMMENTBODY), $Multiple = TRUE); //perform the search
            //echo $MainSearch; echo $SubQuery;
        }
        $SearchResults = $this->SphinxSearchModel->SphinxSearch(' ' . $SubQuery . ' ' . $MainSearch, $index = SPHINX_INDEX_DIST);

        //print_r($SearchResults); die;
        //print_r($Search); die;
        $this->_RelatedThreads($Result['Query']);
        return $SearchResults;
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
    private function _HighLightResults($Result, $Query, $Limit = 0, $Offset = 0) {
        if (!isset($Result['Records']))
            return FALSE;               //no records
        $Records = $Result['Records'];
        $CommentArray = array();  //comment body
        foreach ($Records as $Record) {
            $CommentArray[] = $Record->{SPHINX_FIELD_STR_COMMENTBODY};
        }
        $HighLightedComment = $this->SphinxSearchModel->SphinxBuildExcerpts(
                $CommentArray, SPHINX_INDEX_MAIN, $Query, $options = $this->SphinxSettings->BuildExcerpts
        );
        if ($this->SphinxSettings->HighLightTitles) {
            $DiscussionArray = array(); //discussion titles
            foreach ($Records as $Record) {
                $DiscussionArray[] = $Record->{SPHINX_ATTR_STR_DISCUSSIONNAME};
            }
            $HighLightedDiscussion = $this->SphinxSearchModel->SphinxBuildExcerpts(
                    $DiscussionArray, SPHINX_INDEX_MAIN, $Query, $options = array(
                'before_match' => $this->SphinxSettings->BuildExcerpts['before_match'],
                'after_match' => $this->SphinxSettings->BuildExcerpts['after_match'])
            ); //don't put any restrictions on anything else here except the opening/closing tags
        }

        $Offset = 0;
        foreach ($Records as $Record) {
            $Record->{SPHINX_FIELD_STR_COMMENTBODY} = $HighLightedComment[$Offset];
            if ($this->SphinxSettings->HighLightTitles)
                $Record->{SPHINX_ATTR_STR_DISCUSSIONNAME} = $HighLightedDiscussion[$Offset];
            $Offset++;
        }
        $Result['Records'] = $Records;

        //print_r($Result);
        return $Result;
    }

    /**
     *
     * @param string $InputQuery the filtered input query.
     */
    private function _RelatedThreads($InputQuery) {
        $this->SphinxSearchModel->SphinxReset();
        $this->SphinxSearchModel->SphinxSetMatch(SPH_MATCH_EXTENDED2); //use this since using boolean operators
        $this->SphinxSearchModel->SetSelect(SPHINX_FIELD_STR_DISCUSSIONNAME); //only need the username
        $this->SphinxSearchModel->SphinxSetSort(SPH_SORT_RELEVANCE);
        $this->SphinxSearchModel->SphinxSetRankingMode(SPH_RANK_WORDCOUNT);
        $this->SphinxSearchModel->SphinxSetLimits($Offset = 0, $Limit = $this->SphinxSettings->ReleatedThreadsLimit); //limit to 15 autocompletes
        $Search = $this->SphinxSearchModel->SphinxSearch($this->_FieldSearch(
                        $this->_OperatorOrSearch($InputQuery), array(
                    SPHINX_FIELD_STR_DISCUSSIONNAME
                        ), $Multiples = FALSE), $index = SPHINX_INDEX_DIST); //perform the search); //perform the search
        if ($Search['NumRows'] == 0)
            $this->Sender->SetData('RelatedThreads', $Search['NumRows'] = 0);
        else
            $this->Sender->SetData('RelatedThreads', $Search);
    }

    /**
     *
     * @param string $text
     * @param int $NumMatches number of words to match in the $text
     *
     * Quorum matching operator introduces a kind of fuzzy matching.
     * It will only match those documents that pass a given threshold of given words.
     * The example above ("the world is a wonderful place"/3) will match all documents
     * that have at least 3 of the 6 specified words.
     */
    private function _QuorumSearch($Query, $NumMatches) {
        return '"' . $Query . '/' . $NumMatches . '"';
    }

    private function _PhraseSearch($Query) {
        return '"' . $Query . '"';
    }

    private function _FieldSearch($Query, $Fields = array(), $Multiple = FALSE) {
        if ($Multiple == FALSE)
            return '@(' . $Fields[0] . ') ' . $Query; //single field
        else {
            $Params = '';
            foreach ($Fields as $Field) {
                if ($Params == '')
                    $Params .= $Field;
                else
                    $Params .= ',' . $Field;
            }
            return '@(' . $Params . ') ' . $Query;
        }
    }

    private function _OperatorOrSearch($Query) {
        $Input = '';
        $Return = '';
        if (is_array($Query)) {
            foreach ($Query as $Word)
                $Input .= $Word . ' ';
            $Input = trim($Input);
            $Query = $Input;
        }
        $QueryString = explode(' ', $Query);
        foreach ($QueryString as $Word) {   //add the boolean OR operator (|)
            if (!is_string($Word) || $Word == '')
                continue;
            if ($Return != '')
                $Return .= ' | ' . $Word;
            else
                $Return = $Word;
        }
        return $Return;
    }

    private function _AllSearch($Query) {
        return '@* ' . $Query;
    }

    private function _SetRankAndQuery($Rank, $Query) {
        switch ($Rank) {
            case 0: //Any
            default:
                $this->SphinxSearchModel->SphinxSetRankingMode(SPH_RANK_MATCHANY); //match any keyword
                $Return = $this->_OperatorOrSearch($Query);
                break;
            case 1: //All
                $this->SphinxSearchModel->SphinxSetRankingMode(SPH_RANK_PROXIMITY); //requires perfect match
                $Return = '"' . $Query . '"'; //add quotes to designate a phrase match is required
                break;
            case 2: //Extended
                $this->SphinxSearchModel->SphinxSetRankingMode(SPH_RANK_PROXIMITY_BM25); //boolean operators
                $Return = $Query; //do not alter the query...allow the extended syntax to be inserted by user
                break;
        }
        return $Return;
    }

    private function _SetSort($Order) {
        switch ($Order) {
            case 0:  //relevance
                $this->SphinxSearchModel->SphinxSetSort(SPH_SORT_RELEVANCE, '');
                break;
            case 1: //most recent
                $this->SphinxSearchModel->SphinxSetSort(SPH_SORT_STR_DESC, 'CommentDateInserted');
                break;
            case 2: //most replies
                $this->SphinxSearchModel->SphinxSetSort(SPH_SORT_STR_DESC, 'DiscussionCountComments');
                break;
            case 3: //most views
                $this->SphinxSearchModel->SphinxSetSort(SPH_SORT_STR_DESC, 'DiscussionCountViews'); //relevance only..no direct field
                break;
        }
    }

    /**
     * Returns a JSON encoded string
     *
     * @param array $Result Sphinx result array
     * @param string $Field field to populate json return string from
     */
    private function _JsonEncode($Result, $Field) {
        $Return = '[';
        foreach ($Result as $Row) {
            if ($Return != '[')
                $Return .= ',';
            $Return .= json_encode(array('value' => GetValue($Field, $Row)));
        }
        $Return .= ']';
        return $Return;
    }

    /**
     * Main entry point after clicking "Search"
     */
    public function SearchController_results_Create($Sender) {
        $this->Sender = $Sender;            //grab a hold of this
//        $Validation = new Gdn_Validation();
//        $ConfigurationModel = new Gdn_ConfigurationModel($Validation);
//        $ConfigurationModel->Validation->ApplyRule($_GET['q'], 'Length');
        //First, sanitize and validate the user's input
        $Inputs = $this->_Validate();
        $Result = $this->_MainSearch($Inputs); //peform the main search
        $Result = $this->_HighLightResults($Result, $Inputs['Query']); //highlight results
        $this->_LoadResultSearch($Result, $Inputs); //load results to results screen
    }

    private function _Validate() {
        $MemberList = array();
        $TagList = array();
        $ForumList = array();
        $Query = '';

        $QueryIn = trim(filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING)); //probably overkill
        $QueryIn = explode(' ', $QueryIn);
        foreach ($QueryIn as $Word) {    //get rid of any white spaces inbetween words
            if (!is_string($Word) || $Word == '')
                continue;
            if ($Query == '')
                $Query = $Word;
            else
                $Query .= ' ' . $Word;
        }

        $TitlesOnly = (filter_input(INPUT_GET, 'titles', FILTER_SANITIZE_NUMBER_INT) == 1 ? 1 : 0); //checkbox - bool
        $Match = filter_input(INPUT_GET, 'match', FILTER_SANITIZE_NUMBER_INT);
        $Child = (filter_input(INPUT_GET, 'child', FILTER_SANITIZE_NUMBER_INT) == 1 ? 1 : 0); //checkbox - bool
        if (isset($_GET['forums'])) {
            foreach ($_GET['forums'] as $Forum) {
                if (is_numeric($Forum)) //check if int
                    $ForumList[] = $Forum;
            }
        }
        $Date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING);
        $Order = filter_input(INPUT_GET, 'or', FILTER_SANITIZE_NUMBER_INT); //bool

        $Members = filter_input(INPUT_GET, 'mem', FILTER_SANITIZE_STRING); //list seperated by comma
        if (!empty($Members)) {
            $Members = explode(',', $Members);
            foreach ($Members as $Member) {
                if (!is_string($Member) || $Member == '' || !str_word_count($Member, 0))
                    continue;
                $MemberList[] = trim($Member);
            }
        }

        $Tags = filter_input(INPUT_GET, 'tag', FILTER_SANITIZE_STRING); //list seperated by comma
        if (!empty($Tags)) {
            $Tags = explode(',', $Tags);
            foreach ($Tags as $Tag) {
                if (!is_string($Tag) || $Tag == '' || !str_word_count($Tag, 0))
                    continue;
                $TagList[] = trim($Tag);
            }
        }
        $ResultFormat = filter_input(INPUT_GET, 'res', FILTER_SANITIZE_NUMBER_INT); // 0 = thread , 1 = post

        return array(
            'Query' => $Query,
            'TitlesOnly' => $TitlesOnly,
            'Match' => $Match,
            'ForumList' => $ForumList,
            'SearchChildren' => $Child,
            'Date' => $Date,
            'MemberList' => $MemberList,
            'Order' => $Order,
            'TagList' => $TagList,
            'Order' => $Order,
            'ResultFormat' => $ResultFormat,
        );
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

    public function test(){
        $SphinxService = new SphinxSearchService($this->Sender, $this->View);
                $SphinxService->ReIndexAll();
                if($Error = $SphinxService->Start())
                    $this->Sender->Form->AddError($Error);
    }

    public function Setup() {
        $this->SphinxSettings->Setup();
    }

    public function OnDisable() {
        $this->SphinxSettings->OnDisable();
    }

}
