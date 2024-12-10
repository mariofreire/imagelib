<?php 

function main()
{
$width = $_GET["width"];
$height = $_GET["height"];
if ($width == "") $width = 128;
if ($height == "") $height = 128;
$w = $width;
$h = $height;

header('Content-Type: image/png');
// header('Content-Disposition: attachment; filename="image.png"');

$image = imagecreatetruecolor($width, $height) or die("Cannot Initialize new GD image stream");
$background_color = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $background_color);
imagesavealpha($image, true);
$x=0;
$y=0;

srand(make_seed());

$noise = array();

for ($x=0; $x < $w; $x++)
for ($y=0; $y < $h; $y++)
{
$noise[$x][$y] = (rand() % 32768) / 32768.0;
}

for ($x=0; $x < $w; $x++)
for ($y=0; $y < $h; $y++)
{
$noisecolor = intval(turbulence($x,$y,$w,$h,$noise,64));
if ($noisecolor >= 255) $noisecolor = 255;
$r = $noisecolor;
$g = $noisecolor;
$b = $noisecolor;
$a = 0;
$l = 192 + ($r) / 3;
if ($l >= 255) $l = 255;
hsl2rgb(169, 255, $l, $r,$g,$b);
$color = imagecolorallocatealpha($image, $r, $g, $b, $a);
imagesetpixel($image, $x, $y, $color);
}

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