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
li.dir>a:hover, li.dir>a.zip:hover{
    color: #000;
    background-color: #aaff00;
}
li.dir>a.zip{
    background-color: #333;
    margin-left: 2px;
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
    require 'inc/util.php';
    
    $root_dir = '_data/';

    // Get Directory
    $path = isset($_GET['d']) ? decode_clean_path($_GET['d']) : '';
    if(strlen($path) == 0 || !(file_exists($path) && is_dir($path)))
        $path = $root_dir;

    if(!ends_with($path, '/'))
        $path .= '/';

    $dirs = array_filter(glob(quotemeta($path).'*'), 'is_dir');
    $files = array_filter(glob(quotemeta($path).'*'), 'is_file');
    //$imgs = array_filter($files, 'is_img');
?>

<h1>
<?php
    echo('<a href="?d='.preg_replace('/^\./', '', dirname($path)).'">'.$path.'</a>');
?>
</h1>

<ol id="dirs">
<?php
    $zip_enabled = !($path === $root_dir || $path[0] === '/');
    foreach($dirs as &$dir){
        $html = '<li class="dir">'.
                '<a href="?d='.encode_path($dir).'">'.basename($dir).'</a>';
        
        # add a link to zip stream the dir
        if($zip_enabled)
            $html .= '<a class="zip" href="zip.php?d='.encode_path($dir).'">zip</a>';
        
        $html .= "</li>\n";
        
        echo($html);
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