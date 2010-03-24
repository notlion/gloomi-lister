<?php

require 'config.php';


function starts_with($str, $needle){
    return strpos($str, $needle) === 0;
}
function ends_with($str, $sub){
    return substr($str, strlen($str) - strlen($sub)) === $sub;
}

function encode_path($path){
    return str_replace("%2F", "/", rawurlencode($path));
}
function clean_path($path){
    return preg_replace(array('/^\./', '/\.\.\//', '/\.\//'), "", $path);
}

function is_img($path){
    foreach(array('.png', '.jpg', '.jpeg', '.gif') as $ext)
        if(ends_with($path, $ext))
            return true;
    return false;
}

function is_zippable_dir($root_dir, $dir){
    return !($dir === $root_dir || $dir[0] === '/' || $dir[0] === '.');
}

function ensure_trailing_slash($path){
    if(!ends_with($path, '/'))
        return $path.'/';
    return $path;
}

function format_bytes($bytes, $precision=2){
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision).' '.$units[$pow];
}

?>