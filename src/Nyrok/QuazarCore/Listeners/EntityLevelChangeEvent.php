<?php

namespace Nyrok\QuazarCore\Listeners;

use Nyrok\QuazarCore\Managers\FloatingTextManager;
use pocketmine\event\entity\EntityLevelChangeEvent as ClassEvent;
use pocketmine\event\Listener;
use pocketmine\Player;

class EntityLevelChangeEvent implements Listener
{
    const NAME = "EntityLevelChangeEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        if($event->getEntity() instanceof Player) FloatingTextManager::update($event->getEntity());
    }
}