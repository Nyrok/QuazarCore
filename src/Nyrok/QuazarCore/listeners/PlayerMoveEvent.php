<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\managers\FloatingTextManager;
use Nyrok\QuazarCore\managers\LobbyManager;
use Nyrok\QuazarCore\managers\OpponentManager;
use Nyrok\QuazarCore\utils\PlayerUtils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent as ClassEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;

final class PlayerMoveEvent implements Listener
{
    const NAME = "PlayerMoveEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        if($event->getPlayer()->getPosition()->y <= 0){
            if(OpponentManager::getOpponent($event->getPlayer())){
                $event->getPlayer()->setLastDamageCause(new EntityDamageByEntityEvent(OpponentManager::getOpponent($event->getPlayer()), $event->getPlayer(), EntityDamageEvent::CAUSE_CUSTOM, $event->getPlayer()->getHealth()));
                $event->getPlayer()->kill();
            }
            PlayerUtils::teleportToSpawn($event->getPlayer());
            LobbyManager::load($event->getPlayer());
        }
    }
}