<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\utils\AntiGlitchPearl;
use pocketmine\event\entity\EntityTeleportEvent as ClassEvent;
use pocketmine\event\Listener;
use pocketmine\Player;

final class EntityTeleportEvent implements Listener
{
    const NAME = "EntityTeleportEvent";

    /**
     * @param ClassEvent $event
     * @priority MONITOR
     */
    public function onEvent(ClassEvent $event): void
    {
        if($event->getEntity() instanceof Player and !AntiGlitchPearl::canTeleport($event->getEntity())){
            if($event->getEntity()->isAlive()){
                $event->setCancelled(true);
            }
        }
    }

}