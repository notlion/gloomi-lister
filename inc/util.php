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
    return preg_replace(array('/^\./', '/\.\.\//', '/\.\//'), '', $path);
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

function xxxx_name($name)
{
    $name = preg_replace('/[a-z]/', 'x', $name);
    $name = preg_replace('/[A-Z]/', 'X', $name);
    $name = preg_replace('/[0-9]/', '#', $name);
    return $name;
}

function sort_by_mdate(&$abs_files, &$files){  // Not really generic, but oh well
    $mtimes = Array();
    foreach($files as $file){
        if(is_dir($file))
            $mtimes[] = dir_get_mtime($file);
        else
            $mtimes[] = filemtime($file);
    }
    array_multisort(
        $mtimes,    SORT_DESC, SORT_NUMERIC,
        $files,     SORT_ASC,  SORT_STRING,
        $abs_files, SORT_ASC,  SORT_STRING
    );
}

function dir_get_files($dir, $recursive=true){
    $dir = ensure_trailing_slash($dir);
    $files = array();
    $handle = opendir($dir);
    if($handle){
        while($file = readdir($handle)){
            if(($file != '.') && ($file != '..')){
                $file = $dir.$file;
                if(is_dir($file)){
                    if($recursive){
                        $files = array_merge($files, dir_get_files($file.'/'));
                    }
                }
                else{
                    $files[] = $file;
                }
            }
        }
        closedir($handle);
    }
    return $files;
}

function dir_get_mtime($dir, $recursive=true){
    $files = dir_get_files($dir, $recursive);
    $highest_mtime = 0;
    foreach($files as $file){
        $file_mtime = filemtime($file);
        if($file_mtime > $highest_mtime){
            $highest_mtime = $file_mtime;
        }
    }
    return $highest_mtime;
}

?>