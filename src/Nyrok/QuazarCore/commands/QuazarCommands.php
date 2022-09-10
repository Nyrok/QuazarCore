<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\traits\CommandTrait;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\permission\Permission;
use pocketmine\plugin\Plugin;

abstract class QuazarCommands extends Command implements PluginIdentifiableCommand
{
    use CommandTrait;

    /**
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     * @param array|null $overloads
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [], ?array $overloads = null)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $overloads);
        self::setCommand($this);
        self::init();
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return Core::getInstance();
    }

    public function getUsage(): string
    {
        return LanguageProvider::getPrefix() . parent::getUsage();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)){
            return;
        }
    }

    public function testPermissionSilent(CommandSender $target): bool
    {
        return parent::testPermissionSilent($target) or $target->hasPermission(Permission::DEFAULT_OP);
    }

}