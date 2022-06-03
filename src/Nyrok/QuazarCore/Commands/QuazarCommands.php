<?php

namespace Nyrok\QuazarCore\Commands;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\Traits\CommandTrait;
use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;

abstract class QuazarCommands extends Command implements PluginIdentifiableCommand
{
    use CommandTrait;

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [], ?array $overloads = null)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $overloads);
        self::setCommand($this);
        self::init();
    }

    public function getPlugin(): Plugin
    {
        return Core::getInstance();
    }

}