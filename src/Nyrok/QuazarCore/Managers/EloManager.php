<?php

namespace Nyrok\QuazarCore\Managers;

use Nyrok\QuazarCore\Core;

abstract class EloManager
{
    public static function getDefaultElo(): int {
        return Core::getInstance()->getConfig()->getNested('elo.default', 1000);
    }

}