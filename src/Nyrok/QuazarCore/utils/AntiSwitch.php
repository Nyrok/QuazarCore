<?php

namespace Nyrok\QuazarCore\utils;

use JetBrains\PhpStorm\Pure;
use pocketmine\Player;

abstract class AntiSwitch
{

    /**
     * @var bool[]
     */
    private static array $blacklisted = [];

    /**
     * @param Player $player
     * @return bool
     */
    #[Pure] public static function isBlacklist(Player $player): bool
    {
        return (self::$blacklisted[$player->getName()] ?? microtime(true)) > microtime(true);
    }

    /**
     * @param Player $player
     * @param int $time
     */
    public static function blacklist(Player $player, float $time): void
    {
        self::$blacklisted[$player->getName()] = microtime(true) + $time;
    }

    /**
     * @param Player $player
     */
    public static function unblacklist(Player $player): void
    {
        if(isset(self::$blacklisted[$player->getName()])) unset(self::$blacklisted[$player->getName()]);
    }


}