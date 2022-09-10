<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\Core;
use pocketmine\event\Listener;
use pocketmine\event\inventory\InventoryTransactionEvent as ClassEvent;

final class InventoryTransactionEvent implements Listener
{
    const NAME = "InventoryTransactionEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        if($event->getTransaction()->getSource()->getLevel()->getName() === Core::getInstance()->getConfig()->getNested("positions.spawn.world")){
            $event->setCancelled();
        }
    }
}