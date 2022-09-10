<?php

namespace Nyrok\QuazarCore\librairies\Voltage\Api;

use pocketmine\plugin\PluginBase;
use Nyrok\QuazarCore\librairies\Voltage\Api\listener\ScoreBoardListener;
use Nyrok\QuazarCore\librairies\Voltage\Api\manager\ScoreBoardManager;

class ScoreBoardApi extends PluginBase
{
    private static ?ScoreBoardManager $manager = null;

    /**
     * @return ScoreBoardManager|null
     */
    public static function getManager() : ?ScoreBoardManager {
        return self::$manager;
    }

    public static function loadManager(): void
    {
        self::$manager = new ScoreBoardManager();
    }
}