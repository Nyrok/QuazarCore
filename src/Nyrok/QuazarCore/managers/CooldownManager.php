<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\objects\Cooldown;
use pocketmine\Server;

abstract class CooldownManager
{

    /**
     * @var Cooldown[]
     */
    public static array $cooldowns = [];

    public static function initCooldowns(): void {
        foreach (Core::getInstance()->getConfig()->getNested("cooldowns") as $id => $cooldown){
            $levels = [];
            foreach($cooldown['cooldown'] as $worldName => $worldCooldown){
                $level = Server::getInstance()->getLevelByName($worldName);
                $levels[$level] = $worldCooldown;
            }
            $class = new Cooldown($cooldown['name'], (int)$id, $levels);
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