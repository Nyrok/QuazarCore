<?php

namespace Nyrok\QuazarCore\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent as ClassEvent;

final class PlayerDropItemEvent implements Listener
{
    const NAME = "PlayerDropItemEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        $event->setCancelled();
    }
}