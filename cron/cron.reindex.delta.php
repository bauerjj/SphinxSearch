<?php

/*
 * Add the following cron job to update delta index every 5 minutes:
 */
// */5 * * * * /usr/bin/php} /srv/http/mcuhq/vanilla/plugins/SphinxSearch/cron/cron_reindex_delta.php

define('PATH_TO_SPHINX_INDEXER', '/srv/http/mcuhq/vanilla/plugins/SphinxSearch/install/bin/indexer');
define('PATH_TO_SPHINX_CONFIG', '/srv/http/mcuhq/vanilla/plugins/SphinxSearch/install/sphinx/etc/sphinx.conf');
define('SPHINX_INDEX_NAME', 'vss_');

$Command = PATH_TO_SPHINX_INDEXER." --config ".PATH_TO_SPHINX_CONFIG." ".SPHINX_INDEX_NAME."delta --rotate ";
system($Command, $Return);