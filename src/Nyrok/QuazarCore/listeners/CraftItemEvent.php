<?php

namespace Nyrok\QuazarCore\listeners;

use pocketmine\event\inventory\CraftItemEvent as ClassEvent;
use pocketmine\event\Listener;

final class CraftItemEvent implements Listener
{
    const NAME = "CraftItemEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event)
    {
        $event->cancel();
    }
}