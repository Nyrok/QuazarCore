<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\managers\TimeManager;
use pocketmine\event\Listener;
use pocketmine\event\level\LevelLoadEvent as ClassEvent;

final class LevelLoadEvent implements Listener
{
    const NAME = "LevelLoadEvent";

    public function onEvent(ClassEvent $event)
    {
        TimeManager::initTime();
    }
}