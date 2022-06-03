<?php

namespace Nyrok\QuazarCore\Listeners;

use Nyrok\QuazarCore\Managers\LogsManager;
use Nyrok\QuazarCore\Provider\LanguageProvider;
use pocketmine\event\player\PlayerQuitEvent as ClassEvent;
use pocketmine\event\Listener;

final class PlayerQuitEvent implements Listener
{
    const NAME = "PlayerQuitEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        $event->setQuitMessage(LogsManager::getLogMessage(LogsManager::TYPE_LEAVE, $event->getPlayer()));
    }
}