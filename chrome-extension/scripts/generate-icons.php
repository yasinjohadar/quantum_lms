<?php

$sizes = [16, 48, 128];
$dir = dirname(__DIR__).'/icons';

if (! is_dir($dir)) {
    mkdir($dir, 0777, true);
}

foreach ($sizes as $s) {
    $img = imagecreatetruecolor($s, $s);
    $bg = imagecolorallocate($img, 13, 110, 253);
    $fg = imagecolorallocate($img, 255, 255, 255);
    imagefilledrectangle($img, 0, 0, $s, $s, $bg);
    imagestring($img, 2, max(1, (int) ($s / 4)), max(1, (int) ($s / 3)), 'Q', $fg);
    imagepng($img, $dir.'/icon'.$s.'.png');
    imagedestroy($img);
}

echo "Generated icons in {$dir}\n";
