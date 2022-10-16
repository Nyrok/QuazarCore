<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;

abstract class EloManager
{
    /**
     * @return int
     */
    public static function getDefaultElo(): int
    {
        return Core::getInstance()->getConfig()->getNested('elo.default', 1000);
    }

    public static function calculateElo(int $L, int $W): int
    {
        return (self::getEloCoefficient() * (1 - round(1 / round(1 + 10 ** (($L - $W) / 400), 4), 3)));
    }

    private static function getEloCoefficient(): int
    {
        return Core::getInstance()->getConfig()->getNested('elo.coefficient', 50);
    }
}