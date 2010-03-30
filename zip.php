<?php

require 'inc/util.php';
require 'inc/zipcreate.cls.lib.php';
require 'inc/zipcreate.cls.php';

# get the directory to zip
$dir = isset($_GET['d']) ? clean_path(stripslashes($_GET['d'])) : '';

if(strlen($dir) > 0 && is_dir($dir)){
    date_default_timezone_set('America/Los_Angeles');

    if(!ends_with($dir, '/'))
        $dir .= '/';

    # create new zip stream object
    $zip = new ZipCreate('gzip', true, basename($dir).'.zip');

    # add files
    $files = dir_get_files($dir);
    $dir_len = strlen($dir);
    foreach($files as $file){
        # get file data
        $data = file_get_contents($file);

        # add file to archive
        $zip->add_file($data, substr($file, $dir_len), filemtime($file));
    }

    # finish archive
    $zipcontent = $zip->finish_stream();
}

?>