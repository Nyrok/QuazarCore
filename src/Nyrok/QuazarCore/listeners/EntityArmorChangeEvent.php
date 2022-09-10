<?php

namespace Nyrok\QuazarCore\listeners;

use pocketmine\event\entity\EntityArmorChangeEvent as ClassEvent;
use pocketmine\event\Listener;
use pocketmine\item\Durable;

final class EntityArmorChangeEvent implements Listener
{
    const NAME = "EntityArmorChangeEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        if($event->getNewItem() instanceof Durable and !$event->getNewItem()->isUnbreakable()){
            $item = clone $event->getNewItem();
            $item->setUnbreakable(true);
            $event->setNewItem($item);
        }
    }
}