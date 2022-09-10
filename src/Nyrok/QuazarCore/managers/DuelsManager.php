<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\objects\Duel;
use pocketmine\Player;

abstract class DuelsManager
{
    private static array $duels = [];

    /**
     * @return array
     */
    public static function getDuels(): array
    {
        return self::$duels;
    }

    public static function getDuel(string $name): ?Duel
    {
        return self::$duels[$name];
    }

    public static function addDuel(Duel $duel): void
    {
        self::$duels[$duel->getHost()->getName()] = $duel;
    }

    public static function removeDuel(Duel $duel): void
    {
        unset(self::$duels[$duel->getHost()->getName()]);
    }

}