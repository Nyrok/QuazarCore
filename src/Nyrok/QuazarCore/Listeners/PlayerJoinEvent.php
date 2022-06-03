<?php
namespace Nyrok\QuazarCore\Listeners;

use Nyrok\QuazarCore\Managers\FloatingTextManager;
use Nyrok\QuazarCore\Managers\LobbyManager;
use Nyrok\QuazarCore\Managers\LogsManager;
use Nyrok\QuazarCore\Provider\PlayerProvider;
use Nyrok\QuazarCore\Utils\PlayerUtils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent as ClassEvent;

final class PlayerJoinEvent implements Listener
{
    const NAME = "PlayerJoinEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        if(!PlayerProvider::toQuazarPlayer($event->getPlayer())->isInitPlayer()) PlayerProvider::toQuazarPlayer($event->getPlayer())->initPlayer();
        PlayerUtils::teleportToSpawn($event->getPlayer());
        LobbyManager::load($event->getPlayer());
        $event->setJoinMessage(LogsManager::getLogMessage(LogsManager::TYPE_JOIN, $event->getPlayer()));
        FloatingTextManager::update($event->getPlayer());
    }
}