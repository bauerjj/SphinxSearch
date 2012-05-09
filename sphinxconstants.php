<?php

/**
 * sphinx install directory
 */


define ('SPHINX_SEARCH_INSTALL_DIR', dirname(__FILE__).'/install'); //make sure this is inside of ../plugins/SphinxSearch

define('SPHINX_SEARCH_DELTA_INDEX', 'delta'); //there is still a prefix 
define('SPHINX_SEARCH_MAIN_INDEX', 'main');
define('SPHINX_SEARCH_STATS_INDEX', 'stats');

/**
 *
 * Collection of attributes that come from sphinx.conf
 *
 */
//individual index names - rotate these by themselves
define ('SPHINX_INDEX_MAIN','main');
define ('SPHINX_INDEX_DELTA','delta');

//distributed index - search against this, but don't update this on itself
define ('SPHINX_INDEX_DIST','vanilla');

//These are both indexed and stored
define ('SPHINX_FIELD_STR_COMMENTBODY','commentbody');
define ('SPHINX_FIELD_STR_DISCUSSIONNAME','discussionname');
define ('SPHINX_FIELD_STR_USERNAME','username');

//these are only stored
define ('SPHINX_ATTR_STR_DISCUSSIONNAME','discussionnameattr');
define ('SPHINX_ATTR_STR_CATNAME','catname');
define ('SPHINX_ATTR_STR_CATULCODE','caturlcode');
define ('SPHINX_ATTR_STR_CATDESCRIPTION','catdescription');
define ('SPHINX_ATTR_STR_USERPHOTO','userphoto');

define ('SPHINX_ATTR_TSTAMP_COMMENTDATEINSERTED','dommentdateinserted');
define ('SPHINX_ATTR_TSTAMP_DISCUSSIONDATEINSERTED','discussiondateinserted');

define ('SPHINX_ATTR_UINT_DISCUSSIONVIEWS','discussioncountviews');
define ('SPHINX_ATTR_UINT_DISCUSSIONCOMENTS','discussioncountcomments');
define ('SPHINX_ATTR_UINT_DISCUSSIONID','discussionid');
define ('SPHINX_ATTR_UINT_COMMENTID','commentid');
define ('SPHINX_ATTR_UINT_TABLEID','tableid');

define ('SPHINX_ATTR_UINT_CATID','catid');
define ('SPHINX_ATTR_UINT_USERID','userid');
