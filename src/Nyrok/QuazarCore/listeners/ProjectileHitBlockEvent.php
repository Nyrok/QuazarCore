<?php

namespace Nyrok\QuazarCore\listeners;

use pocketmine\event\entity\ProjectileHitBlockEvent as ClassEvent;
use pocketmine\event\Listener;

final class ProjectileHitBlockEvent implements Listener
{
    const NAME = "ProjectileHitBlockEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        // if($event->getEntity()->getLevel()->getName() !== $event->getEntity()->getOwningEntity()->getLevel()->getName()) $event->setCancelled(true);
    }
}