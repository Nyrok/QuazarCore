<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\managers\FloatingTextManager;
use Nyrok\QuazarCore\managers\LobbyManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent as ClassEvent;

final class PlayerRespawnEvent implements Listener
{
    const NAME = "PlayerRespawnEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        $event->setRespawnPosition(LobbyManager::getSpawnPosition());
        LobbyManager::load($event->getPlayer());
        FloatingTextManager::update();
    }
}