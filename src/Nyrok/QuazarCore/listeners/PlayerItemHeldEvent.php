<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\utils\AntiSwitch;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent as ClassEvent;
use pocketmine\item\Durable;

final class PlayerItemHeldEvent implements Listener
{
    const NAME = "PlayerItemHeldEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){

       if($event->getItem() instanceof Durable and !$event->getItem()->isUnbreakable()){
           $item = clone $event->getItem();
           $item->setUnbreakable(true);
           $event->getPlayer()->getInventory()->setItem($event->getSlot(), $item);
       }
    }
}