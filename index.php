<html>
<head>
<title>Gloomi</title>
<style type="text/css">
body{
    font: 14px Helvetica, Arial, sans-serif;
    letter-spacing: -0.1em;
    margin: 40px 30px 40px 30px;
    color: #fff;
    background-color: #222;
}

a{
    text-decoration: none;
}

h1>a{
    font-size: 24px;
    color: #444;
}
h1>a:hover{
    color: #666;
}

ol{
    padding: 0;
    margin-bottom: 25px;
    list-style-type: none;
}
ol.li{
    float: left;
    clear: both;
}

li.dir, li.file{
    margin-bottom: 12px;
}
li.dir>a, li.file>a, li.file>span.size{
    padding: 5px;
    -moz-border-radius: 2px;
    -webkit-border-radius: 2px;
}

li.dir>a{
    color: rgba(0,0,0,0.5);
    background-color: #444;
}
li.dir>a:hover{
    color: #000;
    background-color: #aaff00;
}

li.file>a{
    color: rgba(0,0,0,0.5);
    background-color: #315dff;
}
li.file>a:hover{
    color: #000;
    background-color: #aaff00;
}
li.file>span.size{
    color: rgba(0,0,0,0.5);
    background-color: #1E3AA3;
    margin-left: 2px;
}
</style>
</head>
<body>
<?php
    function ends_with($str, $sub){
        return substr($str, strlen($str) - strlen($sub)) === $sub;
    }
    function encode_path($path){
        return str_replace("%2F", "/", rawurlencode($path));
    }
    function decode_clean_path($path){
        return preg_replace(array('/^\./', '/\.\.\//', '/\.\//'), "", rawurldecode($path));
    }
    function is_img($path){
        foreach(array('.png', '.jpg', '.jpeg', '.gif') as $ext)
            if(ends_with($path, $ext))
                return true;
        return false;
    }
    function format_bytes($bytes, $precision=2){
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }

    // Get Directory
    $path = isset($_GET['dir']) ? decode_clean_path($_GET['dir']) : '';
    if(strlen($path) == 0 || !(file_exists($path) && is_dir($path)))
        $path = '_data/';

    if(!ends_with($path, '/'))
        $path .= '/';

    $dirs = array_filter(glob(quotemeta($path).'*'), 'is_dir');
    $files = array_filter(glob(quotemeta($path).'*'), 'is_file');
    //$imgs = array_filter($files, 'is_img');
?>

<h1>
<?php
    echo('<a href="?dir='.preg_replace('/^\./', '', dirname($path)).'">'.$path.'</a>');
?>
</h1>

<ol id="dirs">
<?php
    foreach($dirs as &$dir){
        echo('<li class="dir"><a href="?dir='.encode_path($dir).'">'.basename($dir)."</a></li>\n");
    }
?>
</ol>

<ol id="files">
<?php
    foreach($files as &$file){
        echo(
            '<li class="file">'.
            '<a href="/'.encode_path($file).'">'.basename($file).'</a>'.
            '<span class="size">'.format_bytes(filesize($file)).'</span>'.
            "</li>\n"
        );
    }
?>
</ol>

<ol id="imgs">
<?php
    //foreach($imgs as &$img){
    //    print('<li class="img"><a href="/'.encode_path($img).'"><img src="'.encode_path($img).'"/></a></li>');
    //}
?>
</ol>

</body>
</html>