<?php

namespace Nyrok\QuazarCore\Listeners;

use Nyrok\QuazarCore\Managers\FloatingTextManager;
use Nyrok\QuazarCore\Managers\LobbyManager;
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
        FloatingTextManager::update($event->getPlayer());
    }
}