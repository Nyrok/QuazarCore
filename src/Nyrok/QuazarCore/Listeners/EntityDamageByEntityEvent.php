<?php

namespace Nyrok\QuazarCore\Listeners;

use Nyrok\QuazarCore\Managers\LobbyManager;
use Nyrok\QuazarCore\Managers\OpponentManager;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent as ClassEvent;
use pocketmine\Player;

final class EntityDamageByEntityEvent implements Listener
{
    const NAME = "EntityDamageByEntityEvent";

    /**
     * @param ClassEvent $event
     * @noinspection PhpParamsInspection
     */
    public function onEvent(ClassEvent $event){
        if($event->getEntity() instanceof Player and $event->getDamager() instanceof Player){
            if($event->getEntity()->getLevel() === LobbyManager::getSpawnPosition()->getLevel()) $event->cancel();
            else if((OpponentManager::getOpponent($event->getDamager())?->getName() ?? $event->getEntity()->getName()) !== $event->getEntity()->getName()){
                $event->cancel();
            }
            else if((OpponentManager::getOpponent($event->getEntity())?->getName() ?? $event->getDamager()->getName()) !== $event->getDamager()->getName()){
                $event->cancel();
            }
            else {
                OpponentManager::setOpponent($event->getDamager(), $event->getEntity());
                OpponentManager::setOpponent($event->getEntity(), $event->getDamager());
            }
        }
    }
}