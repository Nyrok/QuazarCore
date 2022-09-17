<?php

namespace Nyrok\QuazarCore\traits;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\managers\CosmeticsManager;

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
}