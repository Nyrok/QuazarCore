<?php

namespace Nyrok\QuazarCore\managers;

use AndreasHGK\EasyKits\manager\KitManager;
use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\objects\Arena;
use Nyrok\QuazarCore\objects\Mode;
use pocketmine\level\Level;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;

abstract class ArenasManager
{
    private static array $modes = [];

    public static function initArenas(): void
    {
        foreach (Core::getInstance()->getServer()->getLevels() as $level){
            if(str_starts_with($level->getName(), "duel.")){
                Core::getInstance()->getServer()->removeLevel($level);
            }
        }
        foreach (Core::getInstance()->getConfig()->get('duels', []) as $name => $data) {
            $mode = new Mode($name, KitManager::get($data['kit']) ?? array_values(KitManager::getAll())[0]);
            foreach ($data['arenas'] as $world => $arena) {
                if (Server::getInstance()->loadLevel($world)) {
                    $mode->addArena(new Arena([
                        $arena['player1'], $arena['player2']
                    ],
                        Server::getInstance()->getLevelByName($world)
                    ));
                }
            }
            self::$modes[] = $mode;
        }
    }

    /**
     * @return array
     */
    public static function getModes(): array
    {
        return self::$modes;
    }


    public static function copyWorld(Level $level, string $name ): string {
        $server = Server::getInstance();
        @mkdir($server->getDataPath() . "/worlds/$name/");
        @mkdir($server->getDataPath() . "/worlds/$name/region/");
        copy($server->getDataPath() . "/worlds/" . $level->getFolderName() . "/level.dat", $server->getDataPath() . "/worlds/$name/level.dat");
        $levelPath = $server->getDataPath() . "/worlds/" . $level->getFolderName() . "/level.dat";
        $levelPath = $server->getDataPath() . "/worlds/$name/level.dat";

        $nbt = new BigEndianNBTStream();
        $levelData = $nbt->readCompressed(file_get_contents($levelPath));
        $levelData = $levelData->getCompoundTag("Data");
        $oldName = $levelData->getString("LevelName");
        $levelData->setString("LevelName", $name);
        $nbt = new BigEndianNBTStream();
        file_put_contents($levelPath, $nbt->writeCompressed(new CompoundTag("", [$levelData])));
        self::copy_directory($server->getDataPath() . "/worlds/" . $level->getFolderName() . "/region/", $server->getDataPath() . "/worlds/$name/region/");
        return $name;
    }

    private static function copy_directory($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::copy_directory($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}