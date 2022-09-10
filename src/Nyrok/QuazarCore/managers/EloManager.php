<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;

abstract class EloManager
{
    /**
     * @return int
     */
    public static function getDefaultElo(): int {
        return Core::getInstance()->getConfig()->getNested('elo.default', 1000);
    }

}