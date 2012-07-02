<?php

/**
 *  install debug logs
 */
define ('OUTPUT_FILE', PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'install' . DS . 'output.txt');
define ('PID_FILE', PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'install' . DS . 'pid.txt');
define ('ERROR_FILE', PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'install' . DS . 'error.txt');


/**
 *  General error handling
 *
 */
define ('SPHINX_ERROR', 1);
define ('SPHINX_SUCCESS', 2);
define ('SPHINX_WARNING', 3);
define ('SPHINX_FATAL_ERROR', 4);


define ('SPHINX_PREFIX', 'Plugin.SphinxSearch.');

/**
 * observer constants
 */
define ('SPHINX_STATUS_NAME', 'Name');



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
define ('SPHINX_INDEX_MAIN',C('Plugin.SphinxSearch.Prefix', 'vss_').'main');
define ('SPHINX_INDEX_DELTA',C('Plugin.SphinxSearch.Prefix', 'vss_').'delta');
define ('SPHINX_INDEX_STATS',C('Plugin.SphinxSearch.Prefix', 'vss_').'stats');

//distributed index - search against this, but don't update this on itself
define ('SPHINX_INDEX_DIST','vanilla');

//These are both indexed and stored
define ('SPHINX_FIELD_STR_COMMENTBODY','commentbody');
define ('SPHINX_FIELD_STR_DISCUSSIONNAME','discussionname');//don't use this, use the attr_str version - sphinx.conf uses this for other purposes
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

//latest comment in discussion (the comments will return 0 for these)
define ('SPHINX_ATTR_STR_LASTCOMMENTUSERNAME','lastcommentusername');
define ('SPHINX_ATTR_UINT_LASTCOMMENTUSERID','lastcommentuserid');
define ('SPHINX_ATTR_UINT_DISCUSSIONLASTCOMMENTID','discussionlastcommentid');
define ('SPHINX_ATTR_STAMP_DATELASTCOMMENT','datelastcomment');
