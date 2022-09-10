<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;

abstract class TimeManager
{
    public static function initTime(): void {
        foreach (Core::getInstance()->getServer()->getLevels() as $level){
            $level->setTime(0);
            $level->stopTime();
        }
    }
}