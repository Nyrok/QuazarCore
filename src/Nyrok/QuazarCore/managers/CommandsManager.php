<?php
namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\commands\DiscordCommand;
use Nyrok\QuazarCore\commands\DuelCommand;
use Nyrok\QuazarCore\commands\EventCommand;
use Nyrok\QuazarCore\commands\MuteCommand;
use Nyrok\QuazarCore\commands\PingCommand;
use Nyrok\QuazarCore\commands\QuazarCommands;
use Nyrok\QuazarCore\commands\RekitCommand;
use Nyrok\QuazarCore\commands\ReportCommand;
use Nyrok\QuazarCore\commands\SanctionsCommand;
use Nyrok\QuazarCore\commands\SpawnCommand;
use Nyrok\QuazarCore\commands\StaffCommand;
use Nyrok\QuazarCore\commands\StatsCommand;
use Nyrok\QuazarCore\commands\TempBanCommand;
use Nyrok\QuazarCore\commands\TopCommand;
use Nyrok\QuazarCore\commands\TPSCommand;
use Nyrok\QuazarCore\commands\UnBanCommand;
use Nyrok\QuazarCore\commands\UnMuteCommand;
use Nyrok\QuazarCore\commands\WarnCommand;
use Nyrok\QuazarCore\Core;
use pocketmine\permission\Permission;

abstract class CommandsManager
{
    const BLACKLISTED_COMMANDS = ["help", "about", "checkperm", "ppinfo", "kill" , "suicide", "me"];
    const ADMIN_ONLY_COMMANDS = ["version", "region", "kit", "multiworld", "kill" , "suicide", "me",];
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
            new RekitCommand('rekit'),
            new StaffCommand('staff'),
            new TempBanCommand('tempban'),
            new MuteCommand('mute'),
            new SanctionsCommand('sanctions'),
            new WarnCommand('warn'),
            new UnMuteCommand('unmute'),
            new UnBanCommand('unban'),
            new PingCommand('ping'),
            new TPSCommand('tps'),
            new DuelCommand('duel'),
            new EventCommand('event'),
        ];
    }

    public static function initCommands(): void {
        # UNREGISTER POCKETMINE COMMANDS
        foreach (Core::getInstance()->getServer()->getCommandMap()->getCommands() as $command){
            foreach (self::getCommands() as $cmd){
                if($command->getName() === $cmd->getName()){
                    Core::getInstance()->getServer()->getCommandMap()->unregister($command);
                }
            }
        }

        # REGISTER OUR COMMANDS
        foreach(self::getCommands() as $command){
            Core::getInstance()->getServer()->getCommandMap()->register($command->getName(), $command);
            Core::getInstance()->getLogger()->notice("[COMMANDS] Command: {$command->getName()} Loaded");
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

    /**
     * @param string $name
     * @return string
     */
    public static function getDescription(string $name): string {
        return Core::getInstance()->getConfig()->getNested("commands.$name", ['description' => ""])['description'] ?? "";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getUsageMessage(string $name): string {
        return Core::getInstance()->getConfig()->getNested("commands.$name", ['usage' => "/$name"])['usage'] ?? "";
    }

    /**
     * @param string $name
     * @return array
     */
    public static function getAliases(string $name): array {
        return Core::getInstance()->getConfig()->getNested("commands.$name", ['aliases' => []])['aliases'] ?? [];
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getPermission(string $name): string {
        return Core::getInstance()->getConfig()->getNested("commands.$name", ['permission' => "core.commands.$name"])['permission'] ?? "core.commands.$name";
    }

}
