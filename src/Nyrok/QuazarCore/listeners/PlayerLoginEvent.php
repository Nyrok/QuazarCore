<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent as ClassEvent;

final class PlayerLoginEvent implements Listener
{
    const NAME = "PlayerLoginEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        if(!PlayerProvider::toQuazarPlayer($event->getPlayer())->isInitPlayer()) PlayerProvider::toQuazarPlayer($event->getPlayer())->initPlayer();
    }
}