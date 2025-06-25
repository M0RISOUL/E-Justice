<?php
require_once __DIR__ . '/../config/config.php';
$txt = $_GET['t'] ?? '000000';
header('Content-Type: image/png');
$im = imagecreatetruecolor(100, 36);
$bg = imagecolorallocate($im, 250,250,250);
$fg = imagecolorallocate($im, 30,30,30);
imagefill($im,0,0,$bg);
imagestring($im, 5, 20, 8, $txt, $fg);
imagepng($im);
imagedestroy($im);
