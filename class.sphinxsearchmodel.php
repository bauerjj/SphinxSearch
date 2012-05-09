<?php

class SphinxSearchModel extends Gdn_Module {

    function __construct() {
        parent::__construct();
        include_once(PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'sphinxapi.php'); //load the Sphinx API file
        $this->SphinxClient = new SphinxClient();
          }

     function SphinxConnect(){

        $this->SphinxClient->SetServer(C('Plugin.SphinxSearch.Host','localhost'), C('Plugin.SphinxSearch.Port',9312)); //must start searchd in order to connect...or else conection will be refused
        $this->SphinxClient->SetMaxQueryTime(C('Plugin.SphinxSearch.MaxQueryTime',2000)); // Sets maximum search query time, in milliseconds. Default valus is 0 which means "do not limit".
        $this->SphinxClient->SetMatch(SPH_MATCH_EXTENDED2); //use this since using boolean operators
     }

     function SphinxStatus(){
         return $this->SphinxClient->Status();
     }

    /**
     * Clears all currently set filters.
     * This call is only normally required when using multi-queries.
     * You might want to set different filters for different queries in the batch.
     * To do that, you should call ResetFilters() and add new filters using the respective calls.
     */
    function SphinxReset() {
        //Prototype: function ResetFilters ()
        $this->SphinxClient->ResetFilters();
        $this->SphinxClient->ResetGroupBy();
    }


    public function SphinxBuildKeywords($query,$index,$hits = FALSE){
        /*
         * Extracts keywords from query using tokenizer settings for given index,
         * optionally with per-keyword occurrence statistics.
         * Returns an array of hashes with per-keyword information.
         *
         */
        return $this->SphinxClient->BuildKeywords($query,$index,$hits);
    }

    function SphinxSetRankingMode($ranker = SPH_RANK_PROXIMITY_BM25){
        /*
         *   SPH_RANK_PROXIMITY_BM25, the default ranking mode that uses and combines both phrase proximity and BM25 ranking.
         *   SPH_RANK_BM25, statistical ranking mode which uses BM25 ranking only (similar to most other full-text engines). This mode is faster but may result in worse quality on queries which contain more than 1 keyword.
         *   SPH_RANK_NONE, no ranking mode. This mode is obviously the fastest. A weight of 1 is assigned to all matches. This is sometimes called boolean searching that just matches the documents but does not rank them.
         *   SPH_RANK_WORDCOUNT, ranking by the keyword occurrences count. This ranker computes the per-field keyword occurrence counts, then multiplies them by field weights, and sums the resulting values.
         *   SPH_RANK_PROXIMITY, added in version 0.9.9-rc1, returns raw phrase proximity value as a result. This mode is internally used to emulate SPH_MATCH_ALL queries.
         *   SPH_RANK_MATCHANY, added in version 0.9.9-rc1, returns rank as it was computed in SPH_MATCH_ANY mode ealier, and is internally used to emulate SPH_MATCH_ANY queries.
         *   SPH_RANK_FIELDMASK, added in version 0.9.9-rc2, returns a 32-bit mask with N-th bit corresponding to N-th fulltext field, numbering from 0. The bit will only be set when the respective field has any keyword occurences satisfiying the query.
         *   SPH_RANK_SPH04, added in version 1.10-beta, is generally based on the default SPH_RANK_PROXIMITY_BM25 ranker, but additionally boosts the matches when they occur in the very beginning or the very end of a text field. Thus, if a field equals the exact query, SPH04 should rank it higher than a field that contains the exact query but is not equal to it. (For instance, when the query is "Hyde Park", a document entitled "Hyde Park" should be ranked higher than a one entitled "Hyde Park, London" or "The Hyde Park Cafe".)
         *   SPH_RANK_EXPR, added in version 2.0.2-beta, lets you specify the ranking formula in run time. It exposes a number of internal text factors and lets you define how the final weight should be computed from those factors. You can find more details about its syntax and a reference available factors in a subsection below.
         */
        $this->SphinxClient->SetRankingMode($ranker);

    }
    /**
     * LEGACY support as of >0.9.9 release
     * Should ALWAYS use the extended match mode
     *
     * @param string $mode
     */
    function SphinxSetMatch($mode = SPH_MATCH_EXTENDED) {
        /*
         *
            *   SPH_MATCH_PHRASE, matches query as a phrase, requiring perfect match;
         */

        $this->SphinxClient->SetMatchMode($mode);
    }

    function SphinxSetSort($mode = SPH_SORT_RELEVANCE, $sort = '') {
        /*
         * Avaliable Options:
         *
         *   SPH_SORT_RELEVANCE, that sorts by relevance in descending order (best matches first);
         *   SPH_SORT_ATTR_DESC, that sorts by an attribute in descending order (bigger attribute values first);
         *   SPH_SORT_ATTR_ASC, that sorts by an attribute in ascending order (smaller attribute values first);
         *   SPH_SORT_TIME_SEGMENTS, that sorts by time segments (last hour/day/week/month) in descending order, and then by relevance in descending order;
         *   SPH_SORT_EXTENDED, that sorts by SQL-like combination of columns in ASC/DESC order;
         *   SPH_SORT_EXPR, that sorts by an arithmetic expression.
         *
         */
        $this->SphinxClient->SetSortMode($mode, $sort);
    }

    /**
     * Filter the results (i.e only show results with tag_id of (1,2,6)
     *
     * @param string $field field to filter on
     * @param array $values integers used to filter results
     */
    function SphinxSetFilter($field, $values) {

        //Prototype: function SetFilter ( $attribute, $values, $exclude=false )
        $this->SphinxClient->SetFilter($field, $values);
    }

    /**
     *
     * @param string $select what is sphinx going to retrieve?
     */
    function SphinxSetSelect($select) {
        //MUST not be an array!!
        //Identical to MYSQL select statement
        $this->SphinxClient->SetSelect($select);
    }

    function SphinxSetRank($rank) {
        /*
         *       SPH_RANK_PROXIMITY_BM25, default ranking mode which uses and combines both phrase proximity and BM25 ranking.
         *       SPH_RANK_BM25, statistical ranking mode which uses BM25 ranking only (similar to most other full-text engines). This mode is faster but may result in worse quality on queries which contain more than 1 keyword.
         *       SPH_RANK_NONE, disabled ranking mode. This mode is the fastest. It is essentially equivalent to boolean searching. A weight of 1 is assigned to all matches.
         *       SPH_RANK_WORDCOUNT, ranking by keyword occurrences count. This ranker computes the amount of per-field keyword occurrences, then multiplies the amounts by field weights, then sums the resulting values for the final result.
         *       SPH_RANK_PROXIMITY, returns raw phrase proximity value as a result. This mode is internally used to emulate SPH_MATCH_ALL queries.
         *       SPH_RANK_MATCHANY, returns rank as it was computed in SPH_MATCH_ANY mode ealier, and is internally used to emulate SPH_MATCH_ANY queries.
         *       SPH_RANK_FIELDMASK, returns a 32-bit mask with N-th bit corresponding to N-th fulltext field, numbering from 0. The bit will only be set when the respective field has any keyword occurences satisfiying the query.
         *       SPH_RANK_SPH04, is generally based on the default SPH_RANK_PROXIMITY_BM25 ranker, but additionally boosts the matches when they occur in the very beginning or the very end of a text field. Thus, if a field equals the exact query, SPH04 should rank it higher than a field that contains the exact query but is not equal to it. (For instance, when the query is "Hyde Park", a document entitled "Hyde Park" should be ranked higher than a one entitled "Hyde Park, London" or "The Hyde Park Cafe".)
         */
        $this->SphinxClient->SetRankingMode($rank);
    }

    function SphinxSetLimits($offset = 0, $limit = 10) {
        //Prototype: function SetLimits ( $offset, $limit, $max_matches=0, $cutoff=0 )
        $this->SphinxClient->SetLimits($offset, $limit);
    }

    function SphinxAddQuery($search, $index) {
        /**
         * AddQuery adds additional query with current settings to multi-query batch (speeds things up)
         */
        //Prototype: function Query ( $query, $index="*", $comment="" )
        $this->SphinxClient->Query($search, $index);
    }

    function SphinxSetGrouping($attr, $func, $groupsort = "@count desc") {
        /*
         *      $attribute is a string that contains group-by attribute name.
         *      $func is a constant that chooses a function applied to the attribute value in order to compute group-by key. (see below for options)
         *      $groupsort is a clause that controls how the groups will be sorted
         *
         *       SPH_GROUPBY_DAY, extracts year, month and day in YYYYMMDD format from timestamp;
         *       SPH_GROUPBY_WEEK, extracts year and first day of the week number (counting from year start) in YYYYNNN format from timestamp;
         *       SPH_GROUPBY_MONTH, extracts month in YYYYMM format from timestamp;
         *       SPH_GROUPBY_YEAR, extracts year in YYYY format from timestamp;
         *       SPH_GROUPBY_ATTR, uses attribute value itself for grouping.
         */

        //Prototype: function SetGroupBy ( $attribute, $func, $groupsort="@group desc" )
        $this->SphinxClient->SetGroupBy($attr, $func, $groupsort);
    }

    //use this for searching thread titles
    public function SphinxSetGroupDistinct($attribute){
            $this->SphinxClient->SetGroupDistinct($attribute);
    }

    /**
     * Sets the select clause, listing specific attributes to fetch, and expressions to compute and fetch.
     * @param type $clause
     */
    public function SetSelect($clause ){
        $this->SphinxClient->SetSelect($clause);
    }

    public function SphinxBuildExcerpts($docs, $index, $words, $opts=array() ){
        return $this->SphinxClient->BuildExcerpts($docs, $index, $words, $opts);

    }

    function SphinxSearch($search, $index) {
        //Prototype: function AddQuery ( $query, $index="*", $comment="" )
        //$result = $this->SphinxClient->BuildExcerpts(array('any day with lorem will be a bad one indeed so and this is getting longer'),'pictuts','butt',array('limit_words'=>5));
        //print_r($result); die;
        //echo $search; die;
        $result = $this->SphinxClient->Query($search, $index);   //here we go
        //print_r($result);
        if ($result === false) {
            echo '<b style ="color: red">Query failed: ' .$this->SphinxClient->GetLastError() . '.\n';
        } else {
            if ($this->SphinxClient->GetLastWarning()) {
                echo '<b style ="color: red">WARNING: ' . $this->SphinxClient->GetLastWarning() . ' </b>';
            }
        }
        //Parse the returned results
        if ($result['total_found'] == 0) //check if no results were returned
            return array('records' => 0, 'NumRows' => $result['total_found']);
        else {
            $result_docs = array();
            foreach ($result['matches'] as $doc => $docinfo) {
                $result_docs[] = $this->_array_to_object($docinfo['attrs']); //convert to object to be consistenst with returned MYSQL data
            }

            if (!isset($result['words'])) //using 'SPH_MATCH_ALL will' not have this defined
                return array('Records' => $result_docs, 'NumRows' => $result['total_found'],'Time' => $result['time'], 'Words' => false); //set it to false
            else
                return array('Records' => $result_docs, 'NumRows' => $result['total_found'], 'Time' => $result['time'],'Words' => $result['words']);
        }
    }

    private function _array_to_object($array = array()) {
        if (!empty($array)) {
            $data = false;

            foreach ($array as $akey => $aval) {
                $data->{$akey} = $aval;
            }

            return $data;
        }

        return false;
    }

}