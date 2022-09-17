<?php

namespace Nyrok\QuazarCore\tasks;

use Nyrok\QuazarCore\managers\EventsManager;
use pocketmine\scheduler\Task;

final class StartEventTask extends Task
{
    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        foreach(EventsManager::getEvents() as $event)
        {
            if(!$event->getStart()) {
                if($event->getStartIn() - time() <= 0) {
                    EventsManager::startEvent($event);
                }
            }
        }
    }
}