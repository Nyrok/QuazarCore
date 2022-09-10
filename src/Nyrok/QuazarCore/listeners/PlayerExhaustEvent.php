<?php

namespace Nyrok\QuazarCore\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent as ClassEvent;

final class PlayerExhaustEvent implements Listener
{
        const NAME = "PlayerExhaustEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        $event->getPlayer()->setFood(20);
        $event->getPlayer()->setSaturation(20);
        $event->setAmount(0);
        $event->setCancelled();
    }
}