<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\Core;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\event\entity\ProjectileHitEntityEvent as ClassEvent;
use pocketmine\event\Listener;

final class ProjectileHitEntityEvent implements Listener
{
    const NAME = "ProjectileHitEntityEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        // if($event->getEntity()->getLevel()->getName() !== $event->getEntityHit()->getLevel()->getName()) $event->setCancelled(true);;
        if($event->getEntity() instanceof EnderPearl){
            $motion = $event->getEntityHit()->getMotion();
            $motion->x += Core::getInstance()->getConfig()->getNested("utils.enderpearl.kb.x", 0);
            $motion->y += Core::getInstance()->getConfig()->getNested("utils.enderpearl.kb.y", 0);
            $motion->z += Core::getInstance()->getConfig()->getNested("utils.enderpearl.kb.z", 0);
            $event->getEntityHit()->setMotion($motion);
        }
    }
}