<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\objects\Cooldown;
use pocketmine\Player;
use pocketmine\Server;

abstract class CooldownManager
{
    /**
     * @var Cooldown[]
     */
    public static array $cooldowns = [];

    public static function initCooldowns(): void {
        $config = Core::getInstance()->getConfig();
        
        foreach ($config->getNested("cooldowns") as $id => $cooldown){
            $class = new Cooldown($cooldown['name'], (int)$id, $cooldown["levels"]);
            self::$cooldowns[$id] = $class;
            Core::getInstance()->getLogger()->notice("[COOLDOWNS] Cooldown: ({$class->getName()}) ".$class->getItem()->getVanillaName()." seconds Loaded");
        }
    }

    /**
     * @return array
     */
    public static function getCooldowns(): array
    {
        return self::$cooldowns;
    }
    
    /**
     * @param Player $player
     * @return void
     */
    public static function resetPlayerCooldown(Player $player): void
    {
        foreach (self::getCooldowns() as $cooldown){
            $cooldown->resetCooldown($player);
        }
    }

}