<?php

namespace Nyrok\QuazarCore\Listeners;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent as ClassEvent;

final class DataPacketReceiveEvent implements Listener
{
    const NAME = "DataPacketReceiveEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){

    }
}