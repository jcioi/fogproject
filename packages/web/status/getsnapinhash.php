<?php
if (!isset($_REQUEST['filepath'])) {
    echo '|0';
    exit;
}
$req = htmlentities($_REQUEST['filepath'],ENT_QUOTES,'utf-8');
$dir = dirname($req);
$name = basename($req);
$file = sprintf('%s/%s',$dir,$name);
$fileexist = file_exists($file);
printf('%s|%s',$fileexist ? hash_file('sha512',$file) : '',$fileexist ? filesize($file) : 0);
