<?php
    require 'inc/util.php';

    # Get Directory
    $path = isset($_GET['d']) ? clean_path(stripslashes($_GET['d'])) : '';
    if(strlen($path) == 0 || !(file_exists($path) && is_dir($path)))
        $path = $root_dir;

    $path = ensure_trailing_slash($path);
    $root_dir = ensure_trailing_slash($root_dir);

    $dirs = array_filter(glob(quotemeta($path).'*'), 'is_dir');
    $files = array_filter(glob(quotemeta($path).'*'), 'is_file');

    # Sort Dirs by Date
    if($path != $root_dir){
        $abs_dirs = array_filter(glob(quotemeta(realpath($path).'/').'*'), 'is_dir');
        sort_by_mdate($abs_dirs, $dirs);
    }

    $zip_enabled = $allow_zips && is_zippable_dir(dirname($path).'/');
    $zip_children_enabled = $allow_zips && is_zippable_dir($path);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Gloomi</title>
<style type="text/css">
body{
    font: 14px Helvetica, Arial, sans-serif;
    letter-spacing: -0.1em;
    margin: 40px 30px 40px 30px;
    color: #fff;
    background: #222<?php if(strlen($bg_img_path)) echo(" url('$bg_img_path')"); ?>;
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
ol>li{
    white-space: nowrap;
}

li.dir, li.file{
    margin-bottom: 12px;
}
li.dir>a, li.file>a, li.file>span.size, li.zip>a{
    color: rgba(0,0,0,0.5);
    padding: 5px;
    -moz-border-radius: 2px;
    -webkit-border-radius: 2px;
}

li.dir>a{
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
    background-color: #315dff;
}
li.file>a:hover{
    color: #000;
    background-color: #aaff00;
}
li.file>span.size{
    background-color: #1E3AA3;
    margin-left: 2px;
}

li.zip>a{
    background-color: #C9165D;
}
li.zip>a:hover{
    color: #000;
    background-color: #aaff00;
}
</style>
</head>
<body>

<h1>
<?php
    echo('<a href="?d='.encode_path(dirname($path)).'">'.$path."</a>\n");
?>
</h1>

<?php if(count($dirs) > 0){ ?>
<ol id="dirs">
<?php
    foreach($dirs as &$dir){
        $html = '<li class="dir">'.
                '<a href="?d='.encode_path($dir).'">'.basename($dir).'</a>';

        # add a link to zip stream the dir
        if($zip_children_enabled)
            $html .= '<a class="zip" href="zip.php?d='.encode_path($dir).'">zip</a>';

        $html .= "</li>\n";

        echo($html);
    }
?>
</ol>
<?php } ?>

<?php if(count($files) > 0){ ?>
<ol id="files">
<?php
    foreach($files as &$file){
        echo(
            '<li class="file">'.
            '<a href="'.encode_path($file).'">'.basename($file).'</a>'.
            '<span class="size">'.format_bytes(filesize($file)).'</span>'.
            "</li>\n"
        );
    }
?>
</ol>
<?php } ?>

<?php
if($zip_enabled){
    echo(
        '<ol id="zip"><li class="zip">'.
        '<a href="zip.php?d='.encode_path($path).'">'.basename($path).'.zip</a>'.
        "</li></ol>\n"
    );
}
?>

</body>
</html>