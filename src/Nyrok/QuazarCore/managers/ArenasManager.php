<?php

namespace Nyrok\QuazarCore\managers;

use AndreasHGK\EasyKits\manager\KitManager;
use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\objects\Arena;
use Nyrok\QuazarCore\objects\Mode;
use pocketmine\level\Level;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\GameMode;
use pocketmine\Server;

abstract class ArenasManager
{
    private static array $modes = [];

    public static function initArenas(): void
    {
        foreach (Core::getInstance()->getServer()->getLevels() as $level) {
            if (str_starts_with($level->getName(), "duel.")) {
                Core::getInstance()->getServer()->removeLevel($level);
            }
        }
        Core::getInstance()->getLogger()->notice("[DUELS] Loading Modes and Arenas..");
        foreach (Core::getInstance()->getConfig()->get('duels', []) as $name => $data) {
            Core::getInstance()->getLogger()->notice("[MODES] Loading Mode: $name");
            if (!KitManager::get($data['kit'])) {
                Core::getInstance()->getLogger()->error("[MODES] Kit: {$data['kit']} doesn't exist.");
                continue;
            }
            $mode = new Mode($name, KitManager::get($data['kit']) ?? null, $data["gamemode"] ?? GameMode::ADVENTURE);
            $count = 0;
            foreach ($data['arenas'] as $world => $arena) {
                if (Server::getInstance()->isLevelGenerated($world)) {
                    if (!Server::getInstance()->loadLevel($world)) {
                        goto error;
                    }
                    $mode->addArena(new Arena([
                        $arena['player1'], $arena['player2']
                    ],
                        Server::getInstance()->getLevelByName($world),
                        $arena['blocks'] ?? [],
                    ));
                    Core::getInstance()->getLogger()->notice("[ARENAS] Arena $world added to mode: $name");
                    $count++;
                } else {
                    error:
                    Core::getInstance()->getLogger()->warning("[ARENAS] Arena $world doesn't exist");
                }
            }
            if (!$count) {
                Core::getInstance()->getLogger()->error("[MODES] Mode $name doesn't have any Arenas.");
                return;
            }
            self::$modes[] = $mode;
            Core::getInstance()->getLogger()->notice("[MODES] Mode $name Loaded.");
        }
    }

    /**
     * @return array
     */
    public static function getModes(): array
    {
        return self::$modes;
    }


    public static function copyWorld(Level $level, string $name): ?string
    {
        $server = Server::getInstance();
        @mkdir($server->getDataPath() . "/worlds/$name/");
        @mkdir($server->getDataPath() . "/worlds/$name/region/");
        copy($server->getDataPath() . "/worlds/" . $level->getFolderName() . "/level.dat", $server->getDataPath() . "/worlds/$name/level.dat");
        $newPath = $server->getDataPath() . "/worlds/$name/level.dat";
        $levelPath = $server->getDataPath() . "/worlds/" . $level->getFolderName() . "/level.dat";
        $nbt = new BigEndianNBTStream();
        try {
            $levelData = $nbt->readCompressed(file_get_contents($levelPath));
            $levelData = $levelData->getCompoundTag("Data");
            $oldName = $levelData->getString("LevelName");
            $levelData->setString("LevelName", $name);
            $nbt = new BigEndianNBTStream();
            file_put_contents($levelPath, $nbt->writeCompressed(new CompoundTag("", [$levelData])));
            self::copy_directory($server->getDataPath() . "/worlds/" . $level->getFolderName() . "/region/", $server->getDataPath() . "/worlds/$name/region/");
            return $name;
        }
        catch (UnexpectedValueException $exception){
            return null;
        }
    }

    private static function copy_directory($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::copy_directory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}