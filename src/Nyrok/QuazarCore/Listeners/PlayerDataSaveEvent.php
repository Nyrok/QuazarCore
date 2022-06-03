<?php

namespace Nyrok\QuazarCore\Listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDataSaveEvent as ClassEvent;

final class PlayerDataSaveEvent implements Listener
{
    const NAME = "PlayerDataSaveEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        $event->cancel();
    }
}