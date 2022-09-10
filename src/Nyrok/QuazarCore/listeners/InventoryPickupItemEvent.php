<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\Core;
use pocketmine\event\Listener;
use pocketmine\event\inventory\InventoryPickupItemEvent as ClassEvent;

final class InventoryPickupItemEvent implements Listener
{
    const NAME = "InventoryPickupItemEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        foreach ($event->getViewers() as $viewer){
            if ($viewer->getLevel()->getName() === Core::getInstance()->getConfig()->getNested("positions.spawn.world")){
                $event->setCancelled();
            }
        }
    }
}