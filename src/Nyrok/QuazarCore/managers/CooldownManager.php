<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\objects\Cooldown;

abstract class CooldownManager
{

    /**
     * @var Cooldown[]
     */
    public static array $cooldowns = [];

    public static function initCooldowns(): void {
        foreach (Core::getInstance()->getConfig()->getNested("cooldowns") as $id => $cooldown){
            $class = new Cooldown($cooldown['name'], (int)$id, (int)$cooldown['cooldown']);
            self::$cooldowns[$id] = $class;
            Core::getInstance()->getLogger()->notice("[COOLDOWNS] Cooldown: ({$class->getName()}) ".$class->getItem()->getVanillaName()." and {$class->getCooldown()} seconds Loaded");
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