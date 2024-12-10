<?php

function optimizeimage($img)
{
$dither = true;
$colors = 256;
$width = imagesx($img);
$height = imagesy($img);
$tmp = imagecreatetruecolor($width, $height) or die("Cannot Initialize new GD image stream");
$background_color = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
imagefill($tmp, 0, 0, $background_color);
imagesavealpha($tmp, true);
imagecopy($tmp, $img, 0, 0, 0, 0, $width, $height); 
imagetruecolortopalette($tmp, $dither, 256);
$image = imagecreatetruecolor($width, $height);
imagecopy($image, $tmp, 0, 0, 0, 0, $width, $height);
imagedestroy($tmp);
imagetruecolortopalette($image, $dither, $colors);
return $image;
}

?>