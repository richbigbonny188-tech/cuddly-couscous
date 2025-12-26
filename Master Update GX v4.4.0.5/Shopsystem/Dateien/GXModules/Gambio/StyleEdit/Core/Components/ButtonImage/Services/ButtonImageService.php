<?php
/*--------------------------------------------------------------------------------------------------
    ButtonImageService.php 2020-07-17
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------------------------------------------
 */

namespace Gambio\StyleEdit\Core\Components\ButtonImage\Services;


use Gambio\StyleEdit\Core\Components\Theme\Entities\Interfaces\CurrentThemeInterface;
use Gambio\StyleEdit\Core\Repositories\SettingsRepository;

class ButtonImageService
{
    /**
     * @var SettingsRepository
     */
    private $settingsRepository;
    /**
     * @var CurrentThemeInterface
     */
    private $theme;
    /**
     * @var string
     */
    protected $font;

    protected $namedColors = [
        'INDIANRED'            => '#CD5C5C',
        'LIGHTCORAL'           => '#F08080',
        'SALMON'               => '#FA8072',
        'DARKSALMON'           => '#E9967A',
        'LIGHTSALMON'          => '#FFA07A',
        'CRIMSON'              => '#DC143C',
        'RED'                  => '#FF0000',
        'FIREBRICK'            => '#B22222',
        'DARKRED'              => '#8B0000',
        'PINK'                 => '#FFC0CB',
        'LIGHTPINK'            => '#FFB6C1',
        'HOTPINK'              => '#FF69B4',
        'DEEPPINK'             => '#FF1493',
        'MEDIUMVIOLETRED'      => '#C71585',
        'PALEVIOLETRED'        => '#DB7093',
        'CORAL'                => '#FF7F50',
        'TOMATO'               => '#FF6347',
        'ORANGERED'            => '#FF4500',
        'DARKORANGE'           => '#FF8C00',
        'ORANGE'               => '#FFA500',
        'GOLD'                 => '#FFD700',
        'YELLOW'               => '#FFFF00',
        'LIGHTYELLOW'          => '#FFFFE0',
        'LEMONCHIFFON'         => '#FFFACD',
        'LIGHTGOLDENRODYELLOW' => '#FAFAD2',
        'PAPAYAWHIP'           => '#FFEFD5',
        'MOCCASIN'             => '#FFE4B5',
        'PEACHPUFF'            => '#FFDAB9',
        'PALEGOLDENROD'        => '#EEE8AA',
        'KHAKI'                => '#F0E68C',
        'DARKKHAKI'            => '#BDB76B',
        'LAVENDER'             => '#E6E6FA',
        'THISTLE'              => '#D8BFD8',
        'PLUM'                 => '#DDA0DD',
        'VIOLET'               => '#EE82EE',
        'ORCHID'               => '#DA70D6',
        'FUCHSIA'              => '#FF00FF',
        'MAGENTA'              => '#FF00FF',
        'MEDIUMORCHID'         => '#BA55D3',
        'MEDIUMPURPLE'         => '#9370DB',
        'BLUEVIOLET'           => '#8A2BE2',
        'DARKVIOLET'           => '#9400D3',
        'DARKORCHID'           => '#9932CC',
        'DARKMAGENTA'          => '#8B008B',
        'PURPLE'               => '#800080',
        'REBECCAPURPLE'        => '#663399',
        'INDIGO'               => '#4B0082',
        'MEDIUMSLATEBLUE'      => '#7B68EE',
        'SLATEBLUE'            => '#6A5ACD',
        'DARKSLATEBLUE'        => '#483D8B',
        'GREENYELLOW'          => '#ADFF2F',
        'CHARTREUSE'           => '#7FFF00',
        'LAWNGREEN'            => '#7CFC00',
        'LIME'                 => '#00FF00',
        'LIMEGREEN'            => '#32CD32',
        'PALEGREEN'            => '#98FB98',
        'LIGHTGREEN'           => '#90EE90',
        'MEDIUMSPRINGGREEN'    => '#00FA9A',
        'SPRINGGREEN'          => '#00FF7F',
        'MEDIUMSEAGREEN'       => '#3CB371',
        'SEAGREEN'             => '#2E8B57',
        'FORESTGREEN'          => '#228B22',
        'GREEN'                => '#008000',
        'DARKGREEN'            => '#006400',
        'YELLOWGREEN'          => '#9ACD32',
        'OLIVEDRAB'            => '#6B8E23',
        'OLIVE'                => '#808000',
        'DARKOLIVEGREEN'       => '#556B2F',
        'MEDIUMAQUAMARINE'     => '#66CDAA',
        'DARKSEAGREEN'         => '#8FBC8F',
        'LIGHTSEAGREEN'        => '#20B2AA',
        'DARKCYAN'             => '#008B8B',
        'TEAL'                 => '#008080',
        'AQUA'                 => '#00FFFF',
        'CYAN'                 => '#00FFFF',
        'LIGHTCYAN'            => '#E0FFFF',
        'PALETURQUOISE'        => '#AFEEEE',
        'AQUAMARINE'           => '#7FFFD4',
        'TURQUOISE'            => '#40E0D0',
        'MEDIUMTURQUOISE'      => '#48D1CC',
        'DARKTURQUOISE'        => '#00CED1',
        'CADETBLUE'            => '#5F9EA0',
        'STEELBLUE'            => '#4682B4',
        'LIGHTSTEELBLUE'       => '#B0C4DE',
        'POWDERBLUE'           => '#B0E0E6',
        'LIGHTBLUE'            => '#ADD8E6',
        'SKYBLUE'              => '#87CEEB',
        'LIGHTSKYBLUE'         => '#87CEFA',
        'DEEPSKYBLUE'          => '#00BFFF',
        'DODGERBLUE'           => '#1E90FF',
        'CORNFLOWERBLUE'       => '#6495ED',
        'ROYALBLUE'            => '#4169E1',
        'BLUE'                 => '#0000FF',
        'MEDIUMBLUE'           => '#0000CD',
        'DARKBLUE'             => '#00008B',
        'NAVY'                 => '#000080',
        'MIDNIGHTBLUE'         => '#191970',
        'CORNSILK'             => '#FFF8DC',
        'BLANCHEDALMOND'       => '#FFEBCD',
        'BISQUE'               => '#FFE4C4',
        'NAVAJOWHITE'          => '#FFDEAD',
        'WHEAT'                => '#F5DEB3',
        'BURLYWOOD'            => '#DEB887',
        'TAN'                  => '#D2B48C',
        'ROSYBROWN'            => '#BC8F8F',
        'SANDYBROWN'           => '#F4A460',
        'GOLDENROD'            => '#DAA520',
        'DARKGOLDENROD'        => '#B8860B',
        'PERU'                 => '#CD853F',
        'CHOCOLATE'            => '#D2691E',
        'SADDLEBROWN'          => '#8B4513',
        'SIENNA'               => '#A0522D',
        'BROWN'                => '#A52A2A',
        'MAROON'               => '#800000',
        'WHITE'                => '#FFFFFF',
        'SNOW'                 => '#FFFAFA',
        'HONEYDEW'             => '#F0FFF0',
        'MINTCREAM'            => '#F5FFFA',
        'AZURE'                => '#F0FFFF',
        'ALICEBLUE'            => '#F0F8FF',
        'GHOSTWHITE'           => '#F8F8FF',
        'WHITESMOKE'           => '#F5F5F5',
        'SEASHELL'             => '#FFF5EE',
        'BEIGE'                => '#F5F5DC',
        'OLDLACE'              => '#FDF5E6',
        'FLORALWHITE'          => '#FFFAF0',
        'IVORY'                => '#FFFFF0',
        'ANTIQUEWHITE'         => '#FAEBD7',
        'LINEN'                => '#FAF0E6',
        'LAVENDERBLUSH'        => '#FFF0F5',
        'MISTYROSE'            => '#FFE4E1',
        'GAINSBORO'            => '#DCDCDC',
        'LIGHTGRAY'            => '#D3D3D3',
        'LIGHTGREY'            => '#D3D3D3',
        'SILVER'               => '#C0C0C0',
        'DARKGRAY'             => '#A9A9A9',
        'DARKGREY'             => '#A9A9A9',
        'GRAY'                 => '#808080',
        'GREY'                 => '#808080',
        'DIMGRAY'              => '#696969',
        'DIMGREY'              => '#696969',
        'LIGHTSLATEGRAY'       => '#778899',
        'LIGHTSLATEGREY'       => '#778899',
        'SLATEGRAY'            => '#708090',
        'SLATEGREY'            => '#708090',
        'DARKSLATEGRAY'        => '#2F4F4F',
        'DARKSLATEGREY'        => '#2F4F4F',
    ];

    /**
     * ButtonImageService constructor.
     *
     * @param SettingsRepository $settingsRepository
     */
    public function __construct(
        SettingsRepository $settingsRepository
    ) {
        $this->settingsRepository = $settingsRepository;
        $this->font               = realpath(__DIR__ . "/../Assets/arial.ttf");
    }

    public function getImage(string $color, string $fallback, string $text): string
    {
        ob_start();
        $this->drawImage($this->convertColorIntoArray($color, $fallback), $text);
        $imageData = ob_get_contents();
        ob_end_clean();
        return $imageData;
    }

    protected function drawImage(array $color, string $text)
    {

        // Open input and output image
        $baseFile = __DIR__ . '/../Assets/button-type-success.png';
        $src = imagecreatefrompng($baseFile) or die('Problem with source');
        $iX = imagesx($src);
        $iY = imagesy($src);
        $out = ImageCreateTrueColor($iX, $iY) or die('Problem In Creating image');
        imageantialias($out, true);
        $alpha = imagecolorallocatealpha($out, 0, 0, 0, 127);
        imagefill($out, 0, 0, $alpha);
        // scan image pixels
        for ($x = 0; $x < $iX; $x++) {
            for ($y = 0; $y < $iY; $y++) {
                $src_pix       = imagecolorat($src, $x, $y);
                $src_pix_array = $this->rgb_to_array($src_pix);
                // check for chromakey color
                if ($src_pix_array[1] > 180) {
                    $src_pix_array[0] = $color[0];
                    $src_pix_array[1] = $color[1];
                    $src_pix_array[2] = $color[2];
                    imagesetpixel(
                        $out,
                        $x,
                        $y,
                        imagecolorallocate(
                            $out,
                            $src_pix_array[0],
                            $src_pix_array[1],
                            $src_pix_array[2]
                        )
                    );
                }

            }
        }

        $size = $this->getFontSize();

        $bbox = imagettfbbox($size, 0, $this->font, $text);

        $x = ceil(($iX - $bbox[2]) / 2);
        $y = ceil(($iY - $bbox[7]) / 2);
        $textColorArray = $this->getContrastColor(...$color);
        $textColor = ImageColorAllocate($out, $textColorArray[0], $textColorArray[1], $textColorArray[2]);
        imagettftext($out, $size, 0, $x, $y, $textColor, $this->font, $text);
        imagesavealpha($out, true);
        imagepng($out) or die('Problem saving output image');
        imagedestroy($out);

    }

    function rgb_to_array($rgb)
    {
        $a[0] = ($rgb >> 16) & 0xFF;
        $a[1] = ($rgb >> 8) & 0xFF;
        $a[2] = $rgb & 0xFF;

        return $a;
    }

    protected function convertColorIntoArray(string $color, string $fallback)
    {
        $collection = $this->settingsRepository->getAll();
        $loopCount = 0;
        do {
            $loopCount++;
            if ($collection->keyExists($color)) {
                $color = trim($collection->getValue($color)->value());
                if ($color[0] === '$') {
                    $color = substr($color, 1, strlen($color) - 1);
                }
                if (isset($this->namedColors[strtoupper($color)])) {
                    $color = $this->namedColors[strtoupper($color)];
                }
            } else {
                if ($color[0] != '#' && $this->isHexColor("#{$fallback}")) {
                    $color = "#{$fallback}";
                } else {
                   $color = $fallback;
                }
            }
        } while (!$this->isHexColor($color) and $loopCount < 10);
        list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
        return [$r, $g, $b];
    }

    function isHexColor($hex)
    {
        $hex = str_replace('#', '', $hex);
        return is_string($hex) && strlen($hex) > 1 && ctype_xdigit($hex);
    }

    protected function getFontSize()
    {
        return 20;
    }

    function getContrastColor($R1, $G1, $B1)
    {
        // Black RGB
        $blackColor = "#000000";
        $R2BlackColor = hexdec(substr($blackColor, 1, 2));
        $G2BlackColor = hexdec(substr($blackColor, 3, 2));
        $B2BlackColor = hexdec(substr($blackColor, 5, 2));

        // Calc contrast ratio
        $L1 = 0.2126 * pow($R1 / 255, 2.2) +
            0.7152 * pow($G1 / 255, 2.2) +
            0.0722 * pow($B1 / 255, 2.2);

        $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
            0.7152 * pow($G2BlackColor / 255, 2.2) +
            0.0722 * pow($B2BlackColor / 255, 2.2);

        $contrastRatio = 0;
        if ($L1 > $L2) {
            $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
        } else {
            $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
        }

        // If contrast is more than 5, return black color
        if ($contrastRatio > 5) {
            return [0,0,0];
        } else {
            // if not, return white color.
            return [255,255,255];
        }
    }


}