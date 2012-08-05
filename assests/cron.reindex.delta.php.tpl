<?php

/*
 * Add the following cron job to update delta index every 5 minutes:
 */
// */5 * * * * {path_to_php} {path_to_cron}/cron.reindex.delta.php

define('PATH_TO_SPHINX_INDEXER', '{path_to_indexer}');
define('PATH_TO_SPHINX_CONFIG', '{path_to_config}');
define('SPHINX_INDEX_NAME', '{index_prefix}');

$Command = PATH_TO_SPHINX_INDEXER." --config ".PATH_TO_SPHINX_CONFIG." ".SPHINX_INDEX_NAME."delta --rotate >> {path_to_cron}sphinx_cron.log 2>&1";
exec($Command);