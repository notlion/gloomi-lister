<?php

require 'inc/util.php';
require 'inc/zipstream.php';

# get the directory to zip
$dir = isset($_GET['d']) ? clean_path(stripslashes($_GET['d'])) : '';

if(strlen($dir) > 0 && is_dir($dir)){
    date_default_timezone_set('America/Los_Angeles');

    if(!ends_with($dir, '/'))
        $dir .= '/';

    $pwd = dirname(__FILE__);

    # create new zip stream object
    $zip = new ZipStream(basename($dir).'.zip', array(
      'comment' => 'found in a cenote deep in the jungle.'
    ));
    
    $file_opt = array('time' => time());

    # add files
    $files = array_filter(glob(quotemeta($dir).'*'), 'is_file');
    foreach($files as $file){
        # build absolute path and get file data
        $path = ($file[0] == '/') ? $file : $pwd.'/'.$file;
        $data = file_get_contents($path);

        # add file to archive
        $zip->add_file(basename($file), $data, $file_opt);
    }

    # finish archive
    $zip->finish();
}

?>