<?php

namespace Nyrok\QuazarCore\utils;

use JetBrains\PhpStorm\Pure;
use pocketmine\Player;

abstract class AntiGlitchPearl
{    /**
 * @var string[]
 */
    private static array $blacklisted = [];

    /**
     * @param Player $player
     * @return bool
     */
    #[Pure] public static function canTeleport(Player $player): bool
    {
        return ($player->getLocation()->getLevel()->getName() !== (self::$blacklisted[$player->getName()] ?? ""));
    }

    /**
     * @param Player $player
     */
    public static function blacklist(Player $player): void
    {
        self::$blacklisted[$player->getName()] = $player->getLocation()->getLevel()->getName();
    }

    /**
     * @param Player $player
     */
    public static function unblacklist(Player $player): void
    {
        if(isset(self::$blacklisted[$player->getName()])) unset(self::$blacklisted[$player->getName()]);
    }
}