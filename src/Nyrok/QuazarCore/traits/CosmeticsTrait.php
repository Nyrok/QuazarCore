<?php

namespace Nyrok\QuazarCore\traits;

use GdImage;
use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\managers\CosmeticsManager;
use pocketmine\entity\Skin;
use pocketmine\Player;

trait CosmeticsTrait
{
    private static function checkCosmetique(): void {
        $checkFileAvailable = [];
        $path = Core::getInstance()->getDataFolder();
        $allDirs = scandir($path);
        foreach ($allDirs as $foldersName) {
            if(is_dir($path.$foldersName)){
                array_push(CosmeticsManager::$cosmeticsTypes,$foldersName);
                $allFiles = scandir($path.$foldersName);
                foreach ($allFiles as $allFilesName) {
                    if(strpos($allFilesName, ".json")) {
                        array_push($checkFileAvailable, str_replace('.json', '', $allFilesName));
                    }
                }
                foreach ($checkFileAvailable as $value) {
                    if(!in_array($value.".png", $allFiles)) {
                        unset($checkFileAvailable[array_search($value, $checkFileAvailable)]);
                    }
                }
                CosmeticsManager::$cosmeticsDetails[$foldersName] = $checkFileAvailable;
                sort(CosmeticsManager::$cosmeticsDetails[$foldersName]);
                $checkFileAvailable = [];
            }
        }
        unset(CosmeticsManager::$cosmeticsTypes[0]);
        unset(CosmeticsManager::$cosmeticsTypes[1]);
        unset(CosmeticsManager::$cosmeticsTypes[array_search("saveskin",CosmeticsManager::$cosmeticsTypes)]);
        unset(CosmeticsManager::$cosmeticsDetails["."]);
        unset(CosmeticsManager::$cosmeticsDetails[".."]);
        unset(CosmeticsManager::$cosmeticsDetails["saveskin"]);
        sort(CosmeticsManager::$cosmeticsTypes);
    }

    private static function checkRequirement() {
        $main = Core::getInstance();
        if(!extension_loaded("gd")) {
            $main->getServer()->getLogger()->info("§6Uncomment gd2.dll (remove symbol ';' in ';extension=php_gd2.dll') in bin/php/php.ini to make the plugin working");
            $main->getServer()->getPluginManager()->disablePlugin($main);
            return;
        }
        if(!class_exists(SimpleForm::class)) {
            $main->getServer()->getLogger()->info("§6EasyUI class missing,pls use .phar from poggit!");
            $main->getServer()->getPluginManager()->disablePlugin($main);
            return;
        }
        if(!file_exists($main->getDataFolder()."steve.json") || !file_exists($main->getDataFolder()."config.yml")) {
            if(file_exists(str_replace("config.yml", "", $main->getResources()["config.yml"]))) {
                self::recurse_copy(str_replace("config.yml","",$main->getResources()["config.yml"]),$main->getDataFolder());
            }else {
                $main->getServer()->getLogger()->info("§6Something wrong with the resources");
                $main->getServer()->getPluginManager()->disablePlugin($main);
            }
        }
    }

    private static function recurse_copy($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * @param string $path
     * @param int $size
     * @return string
     */
    private static function getSkinBytes(string $path, int $size): string
    {
        $img = @imagecreatefrompng($path);
        $skinbytes = "";
        $s = (int)@getimagesize($path)[1];

        for ($y = 0; $y < $s; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $colorat = @imagecolorat($img, $x, $y);
                $a = ((~($colorat >> 24)) << 1) & 0xff;
                $r = ($colorat >> 16) & 0xff;
                $g = ($colorat >> 8) & 0xff;
                $b = $colorat & 0xff;
                $skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        return $skinbytes;
    }

    public function resetSkin(Player $player)
    {
        $skin = $player->getSkin();
        $name = $player->getName();
        $path = Core::getInstance()->getDataFolder() . "saveskin/" . $name . ".png";
        $path2 = Core::getInstance()->getDataFolder() . "saveskin/" . $name . ".txt";
        if (filesize($path2) == 65536) {
            $size = 128;
        } else {
            $size = 64;
        }
        $skinbytes = self::getSkinBytes($path, $size);
        $player->setSkin(new Skin($skin->getSkinId(), $skinbytes, "", "geometry.humanoid.custom", file_get_contents(Core::getInstance()->getDataFolder() . "steve.json")));
        $player->sendSkin();
    }

    public static function saveSkin(Skin $skin, $name)
    {
        $path = Core::getInstance()->getDataFolder();

        if (!file_exists($path . "saveskin")) {
            mkdir($path . "saveskin");
        }

        if (file_exists($path . "saveskin/" . $name . ".txt")) {
            unlink($path . "saveskin/" . $name . ".txt");
        }

        file_put_contents($path . "saveskin/" . $name . ".txt", $skin->getSkinImage()->getData());

        if (filesize($path . "saveskin/" . $name . ".txt") == 65536) {
            $img = self::toImage($skin->getSkinImage()->getData(), 128, 128);
        } else {
            $img = self::toImage($skin->getSkinImage()->getData(), 64, 64);
        }
        imagepng($img, $path . "saveskin/" . $name . ".png");
    }

    public static function toImage($data, $height, $width): GdImage|bool
    {
        $pixelarray = str_split(bin2hex($data), 8);
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $position = count($pixelarray) - 1;
        while (!empty($pixelarray)) {
            $x = $position % $width;
            $y = ($position - $x) / $height;
            $walkable = str_split(array_pop($pixelarray), 2);
            $color = array_map(function ($val) {
                return hexdec($val);
            }, $walkable);
            $alpha = array_pop($color);
            $alpha = ((~((int)$alpha)) & 0xff) >> 1;
            array_push($color, $alpha);
            imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, ...$color));
            $position--;
        }
        return $image;
    }

    public static function setSkin(Player $player, string $stuffName, string $locate)
    {
        $skin = $player->getSkin();
        $name = $player->getName();
        $path = Core::getInstance()->getDataFolder() . "saveskin/" . $name . ".txt";
        if (filesize($path) == 65536) {
            $path = self::imgTricky($name, $stuffName, $locate, 128);
            $size = 128;
        } else {
            $size = 64;
            $path = self::imgTricky($name, $stuffName, $locate, 64);
        }

        $skinbytes = self::getSkinBytes($path, $size);
        $player->setSkin(new Skin($skin->getSkinId(), $skinbytes, "", "geometry." . $locate, file_get_contents(Core::getInstance()->getDataFolder() . $locate . "/" . $stuffName . ".json")));
        $player->sendSkin();
    }

    public static function imgTricky(string $name, string $stuffName, string $locate, $size): string
    {
        $path = Core::getInstance()->getDataFolder();

        $down = imagecreatefrompng($path . "saveskin/" . $name . ".png");
        if ($size == 128) {
            if (file_exists($path . $locate . "/" . $stuffName . "_" . $size . ".png")) {
                $upper = imagecreatefrompng($path . $locate . "/" . $stuffName . "_" . $size . ".png");
            } else {
                $upper = self::resize_image($path . $locate . "/" . $stuffName . ".png", 128, 128);
            }
        } else {
            $upper = imagecreatefrompng($path . $locate . "/" . $stuffName . ".png");
        }
        //Remove black color out of the png
        imagecolortransparent($upper, imagecolorallocatealpha($upper, 0, 0, 0, 127));

        imagealphablending($down, true);
        imagesavealpha($down, true);

        imagecopymerge($down, $upper, 0, 0, 0, 0, $size, $size, 100);

        imagepng($down, $path . 'do_not_delete.png');
        return $path . 'do_not_delete.png';
    }

    public static function resize_image($file, $w, $h, $crop = FALSE): GdImage|bool
    {
        list($width, $height) = getimagesize($file);
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width - ($width * abs($r - $w / $h)));
            } else {
                $height = ceil($height - ($height * abs($r - $w / $h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w / $h > $r) {
                $newwidth = $h * $r;
                $newheight = $h;
            } else {
                $newheight = $w / $r;
                $newwidth = $w;
            }
        }
        $src = imagecreatefrompng($file);
        $dst = imagecreatetruecolor($w, $h);
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        return $dst;
    }
}