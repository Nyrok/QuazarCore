<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\managers\FloatingTextManager;
use Nyrok\QuazarCore\utils\AntiGlitchPearl;
use pocketmine\event\entity\EntityLevelChangeEvent as ClassEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use Nyrok\QuazarCore\managers\CooldownManager;

final class EntityLevelChangeEvent implements Listener
{
    const NAME = "EntityLevelChangeEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        if($event->getEntity() instanceof Player){
            FloatingTextManager::update();
            CooldownManager::resetPlayerCooldown($event->getEntity());
            if(!AntiGlitchPearl::canTeleport($event->getEntity())){
                if($event->getEntity()->isAlive()) $event->setCancelled(true);
            }
        }
    }
}