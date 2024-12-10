<?php 

function main()
{

error_reporting(0);

$width = $_GET["width"];
$height = $_GET["height"];
$hue = $_GET["hue"];
$saturation = $_GET["saturation"];
$lightness = $_GET["lightness"];
$randomseed = $_GET["randomseed"];
$noisesize = $_GET["noisesize"];
$offsetx = $_GET["offsetx"];
$offsety = $_GET["offsety"];
if ($width == "") $width = 128;
if ($height == "") $height = 128;
if (!isset($hue)) $hue = 169;
if (!isset($saturation)) $saturation = 255;
if (!isset($lightness)) $lightness = 192;
if (!isset($randomseed)) $randomseed = make_seed();
if (!isset($noisesize)) $noisesize = 64;
$incxy = 1;
if (!isset($offsetx)) 
{
$offsetx = 0;
}
else $incxy = 2;
if (!isset($offsety))
{
$offsety = 0;
}
else $incxy = 2;
if ($incxy == 2) 
{
  $width *= $incxy;
  $height *= $incxy;
}
if ($hue >= 255) $hue = 255;
if ($saturation >= 255) $saturation = 255;
if ($lightness >= 192) $lightness = 192;
if ($noisesize >= 512) $noisesize = 512;
if ($offsetx <= 0) $offsetx = 0;
if ($offsety <= 0) $offsety = 0;
if ($offsetx >= $width/2) $offsetx = $width/2;
if ($offsety >= $height/2) $offsety = $height/2;
$w = $width;
$h = $height;


$image = imagecreatetruecolor($width, $height) or die("Cannot Initialize new GD image stream");
$background_color = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $background_color);
imagesavealpha($image, true);
$x=0;
$y=0;
$ox = $w;
$oy = $h;

srand($randomseed);

$noise = array();

for ($x=0; $x < $w; $x++)
for ($y=0; $y < $h; $y++)
{
$noise[$x][$y] = (rand() % 32768) / 32768.0;
}
for ($x=0,$ox=$w; ($x < $w),($ox > 0); $x++,$ox-=$incxy)
for ($y=0,$oy=$h; ($y < $h),($oy > 0); $y++,$oy-=$incxy)
{
$cx = $x + $offsetx;
$cy = $y + $offsety;
if ($cx >= $w) 
{
$nx = $x-($w-$ox); 
if ($nx >= $w) $nx = $w;
} else $nx = $cx;
if ($cy >= $h) 
{
$ny = $y-($h-$oy); 
if ($ny >= $h) $ny = $h;
} else $ny = $cy;
$noisecolor = intval(turbulence($nx,$ny,$w,$h,$noise,$noisesize));
if ($noisecolor >= 255) $noisecolor = 255;
$r = $noisecolor;
$g = $noisecolor;
$b = $noisecolor;
$a = 0;
$l = $lightness + ($r) / 3;
if ($l >= 255) $l = 255;
hsl2rgb($hue, $saturation, $l, $r,$g,$b);
$color = imagecolorallocatealpha($image, $r, $g, $b, $a);
imagesetpixel($image, $x, $y, $color);
}

if ($incxy == 2)
{
  $w2 = $w/2;
  $h2 = $h/2;
  $width = $w2;
  $height = $h2;
  $w = $width;
  $h = $height;
  
  $image_resized = imagecreatetruecolor($width, $height) or die("Cannot Initialize new GD image stream");
  $background_color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
  imagefill($image_resized, 0, 0, $background_color);
  imagesavealpha($image_resized, true);

  imagecopy($image_resized, $image, 0, 0, 0, 0, $w, $h); 
  $image = $image_resized;
}


header('Content-Type: image/png');
// header('Content-Disposition: attachment; filename="image.png"');

imagepng($image);
imagedestroy($image);

}


main();


function hex2rgb($hex)
{
        // Remove #.
        if (strpos($hex, '#') === 0) {
            $hex = substr($hex, 1);
        }

        if (strlen($hex) == 3) {
            $hex .= $hex;
        }

        if (strlen($hex) != 6) {
            return false;
        }

        // Convert each tuple to decimal.
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return array($r, $g, $b);
}

function rgb2hex($rgb)
{
        if(count($rgb) < 3) {
            return false;
        }

        list($r, $g, $b) = $rgb;

        // From php.net.
        $r = 0x10000 * max(0, min(255, $r));
        $g = 0x100 * max(0, min(255, $g));
        $b = max(0, min(255, $b));

        return strtoupper(str_pad(dechex($r + $g + $b), 6, 0, STR_PAD_LEFT));
}

function num2hex($num)
{
        $num=round($num * 255);
       
        if($num>15) return dechex($num);
        else return '0'.dechex($num);
}

function _HSL_2_RGB( $v1, $v2, $vH ){
                if ( $vH < 0 ) $vH += 1;
                if ( $vH > 1 ) $vH -= 1;
                if ( ( 6 * $vH ) < 1 ) return ( $v1 + ( $v2 - $v1 ) * 6 * $vH );
                if ( ( 2 * $vH ) < 1 ) return ( $v2 );
                if ( ( 3 * $vH ) < 2 ) return ( $v1 + ( $v2 - $v1 ) * ( ( 2 / 3 ) - $vH ) * 6 );
                return ( $v1 );
}

function _HSL2RGB($H, $S, $L, &$R, &$G, &$B){
                if ( $S == 0 ){
                        $R = $L * 255;
                        $G = $L * 255;
                        $B = $L * 255;
                }else{
                        $var_2 = ($L < 0.5)? ($L * ( 1 + $S )) : (( $L + $S ) - ( $S * $L ));
                        $var_1 = 2 * $L - $var_2;
                        $R = 255 * _HSL_2_RGB( $var_1, $var_2, $H + ( 1 / 3 ) ) ;
                        $G = 255 * _HSL_2_RGB( $var_1, $var_2, $H );
                        $B = 255 * _HSL_2_RGB( $var_1, $var_2, $H - ( 1 / 3 ) );
                }
}

function hsl2rgb($h, $s, $l, &$r, &$g, &$b)
{
  $f_r = 0;
  $f_g = 0;
  $f_b = 0;
  _HSL2RGB($h/255.0, $s/255.0, $l/255.0, $f_r, $f_g, $f_b);
  $r = intval($f_r);
  $g = intval($f_g);
  $b = intval($f_b);
}

function smoothNoise($x, $y,$w,$h,$n)
{  
   //get fractional part of x and y
   $fractX = $x - intval($x);
   $fractY = $y - intval($y);
   
   //wrap around
   $x1 = (intval($x) + $w) % $w;
   $y1 = (intval($y) + $h) % $h;
   
   //neighbor values
   $x2 = ($x1 + $w - 1) % $w;
   $y2 = ($y1 + $h - 1) % $h;

   //smooth the noise with bilinear interpolation
   $value = 0.0;
   $value = $value + ($fractX       * $fractY       * $n[$x1][$y1]);
   $value = $value + ($fractX       * (1 - $fractY) * $n[$x1][$y2]);
   $value = $value + ((1 - $fractX) * $fractY       * $n[$x2][$y1]);
   $value = $value + ((1 - $fractX) * (1 - $fractY) * $n[$x2][$y2]);

   return $value;
}

function turbulence($x, $y, $w, $h, $n, $size)
{
    $value = 0.0;
    $initialSize = $size;
    
    while($size >= 1)
    {
        $value = $value + smoothNoise($x / $size, $y / $size, $w, $h, $n) * $size;
        $size = $size / 2.0;
    }
    
    return (128.0 * $value / $initialSize);
}

// seed with microseconds
function make_seed()
{
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
}


?>