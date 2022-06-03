<?php
namespace Nyrok\QuazarCore\Managers;

use Nyrok\QuazarCore\Commands\DiscordCommand;
use Nyrok\QuazarCore\Commands\QuazarCommands;
use Nyrok\QuazarCore\Commands\RekitCommand;
use Nyrok\QuazarCore\Commands\ReportCommand;
use Nyrok\QuazarCore\Commands\SpawnCommand;
use Nyrok\QuazarCore\Commands\StatsCommand;
use Nyrok\QuazarCore\Commands\TopCommand;
use Nyrok\QuazarCore\Core;
use pocketmine\permission\Permission;

abstract class CommandsManager
{
    const BLACKLISTED_COMMANDS = ["help", "about", "checkperm", "ppinfo"];
    const ADMIN_ONLY_COMMANDS = ["version", "region", "kit", "multiworld"];
    const NO_ALIASES_COMMANDS = ["msg"];


    /**
     * @return QuazarCommands[]
     */
    public static function getCommands(): array {
        return [
            new SpawnCommand('spawn'),
            new StatsCommand('stats'),
            new TopCommand('top'),
            new DiscordCommand('discord'),
            new ReportCommand('report'),
            new RekitCommand('rekit')
        ];
    }

    public static function initCommands(): void {
        foreach(self::getCommands() as $command){
            Core::getInstance()->getServer()->getCommandMap()->register($command->getName(), $command);
            Core::getInstance()->getLogger()->alert("[COMMANDS] Command: {$command->getName()} Loaded");
        }
        self::blacklistCommands();
        self::deleteAliasesCommands();
        self::setAdminCommands();
    }


    public static function blacklistCommands(): void {
        foreach(self::BLACKLISTED_COMMANDS as $command){
            $cmd = Core::getInstance()->getServer()->getCommandMap()->getCommand($command);
            if($cmd) Core::getInstance()->getServer()->getCommandMap()->unregister($cmd);
        }
    }

    public static function deleteAliasesCommands(): void {
        foreach(self::NO_ALIASES_COMMANDS as $command){
            $cmd = Core::getInstance()->getServer()->getCommandMap()->getCommand($command);
            if($cmd){
                $cmd->setAliases([]);
                Core::getInstance()->getServer()->getCommandMap()->register($cmd->getName(), $cmd);
            }
        }
    }

    public static function setAdminCommands(): void {
        foreach(self::ADMIN_ONLY_COMMANDS as $command){
            $cmd = Core::getInstance()->getServer()->getCommandMap()->getCommand($command);
            if($cmd){
                $cmd->setPermission(Permission::DEFAULT_OP);
                Core::getInstance()->getServer()->getCommandMap()->register($cmd->getName(), $cmd);
            }
        }
    }

    public static function getDescription(string $name): string {
        return Core::getInstance()->getConfig()->getNested("commands.$name", ['description' => ""])['description'] ?? "";
    }

    public static function getUsageMessage(string $name): string {
        return Core::getInstance()->getConfig()->getNested("commands.$name", ['usageMessage' => "/$name"])['usageMessage'] ?? "";
    }

    public static function getAliases(string $name): array {
        return Core::getInstance()->getConfig()->getNested("commands.$name", ['aliases' => []])['aliases'] ?? [];
    }

    public static function getPermission(string $name): string {
        return Core::getInstance()->getConfig()->getNested("commands.$name", ['permission' => "core.commands.$name"])['permission'] ?? "core.commands.$name";
    }

}
