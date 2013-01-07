<?php

/*
 * Add the following cron job to update the stats index every 2 hours:
 */
// 0 */2 * * * {path_to_php} {path_to_cron}{DS}cron.reindex.stats.php

define('PATH_TO_SPHINX_INDEXER', '{path_to_indexer}');
define('PATH_TO_SPHINX_CONFIG', '{path_to_config}');
define('SPHINX_INDEX_NAME', '{index_prefix}');

$Command = PATH_TO_SPHINX_INDEXER." --config ".PATH_TO_SPHINX_CONFIG." ".SPHINX_INDEX_NAME."stats --rotate >> {path_to_cron}{DS}sphinx_cron.log 2>&1";
exec($Command);
