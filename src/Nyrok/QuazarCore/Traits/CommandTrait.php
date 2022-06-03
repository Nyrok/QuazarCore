<?php

namespace Nyrok\QuazarCore\Traits;

use pocketmine\command\Command;
use Nyrok\QuazarCore\Managers\CommandsManager;
use pocketmine\permission\Permission;

trait CommandTrait
{
    private static ?Command $command = null;

    private static function setCommand(Command $command): void {
        self::$command = $command;
    }

    public static function init(){
        if(self::$command !== null){
            self::$command->setDescription(CommandsManager::getDescription(self::$command->getName()));
            self::$command->setAliases(CommandsManager::getAliases(self::$command->getName()));
            self::$command->setUsage(CommandsManager::getUsageMessage(self::$command->getName()));
            self::$command->setPermission(CommandsManager::getPermission(self::$command->getName()) ?? Permission::DEFAULT_TRUE);
        }
    }

}