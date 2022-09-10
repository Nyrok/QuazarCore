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
            foreach($cooldown['cooldown'] as $worldName => $worldCooldown){
                $level = Server::getInstance()->getLevelByName($worldName);
                $class = new Cooldown($cooldown['name'], (int)$id, $level, (int)$cooldown['cooldown']);
                self::$cooldowns[$id] = $class;
                Core::getInstance()->getLogger()->notice("[COOLDOWNS] Cooldown: ({$class->getName()}) ".$class->getItem()->getVanillaName()."world ".$class->getLevel()." and {$class->getCooldown()} seconds Loaded");
            }
        }
    }

    /**
     * @return array
     */
    public static function getCooldowns(): array
    {
        return self::$cooldowns;
    }

}