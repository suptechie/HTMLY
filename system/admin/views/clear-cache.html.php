<?php

$files = array();
$draft = array();
$files = glob('content/*/blog/*.md', GLOB_NOSORT);
$draft = glob('content/*/draft/*.md', GLOB_NOSORT);

rebuilt_cache('all');

foreach (glob('cache/page/*.cache', GLOB_NOSORT) as $file) {
    unlink($file);
}

echo 'All cache has been deleted!';

?>