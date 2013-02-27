<?php

if (!defined('APPLICATION'))
    exit();
/*
 * This overrides the model that ships with Vanilla. Used to place some hooks so the sphinx search plugin
 * can superseede the factory search method
 */

class SearchModel extends Gdn_Model {

    /// PROPERTIES ///
    protected $_Parameters = array();
    protected $_SearchSql = array();
    protected $_SearchMode = 'match';
    public $ForceSearchMode = '';
    protected $_SearchText = '';

    /// METHODS ///
    public function AddSearch($Sql) {
        $this->_SearchSql[] = $Sql;
    }

    /** Add the sql to perform a search.
     *
     * @param Gdn_SQLDriver $Sql
     * @param string $Columns a comma seperated list of columns to search on.
     */
    public function AddMatchSql($Sql, $Columns, $LikeRelavenceColumn = '') {
        if ($this->_SearchMode == 'like') {
            if ($LikeRelavenceColumn)
                $Sql->Select($LikeRelavenceColumn, '', 'Relavence');
            else
                $Sql->Select(1, '', 'Relavence');

            $Sql->BeginWhereGroup();

            $ColumnsArray = explode(',', $Columns);
            foreach ($ColumnsArray as $Column) {
                $Column = trim($Column);

                $Param = $this->Parameter();
                $Sql->OrWhere("$Column like $Param", NULL, FALSE, FALSE);
            }

            $Sql->EndWhereGroup();
        } else {
            $Boolean = $this->_SearchMode == 'boolean' ? ' in boolean mode' : '';

            $Param = $this->Parameter();
            $Sql->Select($Columns, "match(%s) against($Param{$Boolean})", 'Relavence');
            $Param = $this->Parameter();
            $Sql->Where("match($Columns) against ($Param{$Boolean})", NULL, FALSE, FALSE);
        }
    }

    public function Parameter() {
        $Parameter = ':Search' . count($this->_Parameters);
        $this->_Parameters[$Parameter] = '';
        return $Parameter;
    }

    public function Reset() {
        $this->_Parameters = array();
        $this->_SearchSql = '';
    }

    public function Search($Search, $Offset = 0, $Limit = 20) {

        $AllSettings = SphinxFactory::BuildSettings();
        $Settings = $AllSettings->GetAllSettings();
       // if (($Settings['Status']->SearchdRunning == 1) && ($Settings['Status']->EnableSphinxSearch == 1))
         if(true) // Force this to be true while the sphinxsearch plugin is enabled!
             return FALSE; //sphinx is running...don't use default search
        else {
            // If there are no searches then return an empty array.
            if (trim($Search) == '')
                return array();
            // Figure out the exact search mode.
            if ($this->ForceSearchMode)
                $SearchMode = $this->ForceSearchMode;
            else
                $SearchMode = strtolower(C('Garden.Search.Mode', 'matchboolean'));

            if ($SearchMode == 'matchboolean') {
                if (strpos($Search, '+') !== FALSE || strpos($Search, '-') !== FALSE)
                    $SearchMode = 'boolean';
                else
                    $SearchMode = 'match';
            } else {
                $this->_SearchMode = $SearchMode;
            }
            $this->_SearchMode = $SearchMode;
            $this->FireEvent('Search');
            //print_r($this->_SearchSql);

            if (count($this->_SearchSql) == 0)
                return array();

            // Perform the search by unioning all of the sql together.
            $Sql = $this->SQL
                    ->Select()
                    ->From('_TBL_ s')
                    ->OrderBy('s.DateInserted', 'desc')
                    ->Limit($Limit, $Offset)
                    ->GetSelect();

            $Sql = str_replace($this->Database->DatabasePrefix . '_TBL_', "(\n" . implode("\nunion all\n", $this->_SearchSql) . "\n)", $Sql);

            $this->EventArguments['Search'] = $Search;
            $this->FireEvent('AfterBuildSearchQuery');

            if ($this->_SearchMode == 'like')
                $Search = '%' . $Search . '%';

            foreach ($this->_Parameters as $Key => $Value) {
                $this->_Parameters[$Key] = $Search;
            }

            $Result = $this->Database->Query($Sql, $this->_Parameters)->ResultArray();
            $this->Reset();
            $this->SQL->Reset();

            foreach ($Result as $Key => $Value) {
                if (isset($Value['Summary'])) {
                    $Value['Summary'] = Gdn_Format::Text(Gdn_Format::To($Value['Summary'], $Value['Format']));
                    $Result[$Key] = $Value;
                }
            }

            return $Result;
        }
    }

}