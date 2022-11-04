<?php
namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\commands\NickCommand;
use Nyrok\QuazarCore\managers\CosmeticsManager;
use Nyrok\QuazarCore\managers\CPSManager;
use Nyrok\QuazarCore\managers\FloatingTextManager;
use Nyrok\QuazarCore\managers\LobbyManager;
use Nyrok\QuazarCore\managers\LogsManager;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\utils\AntiSwitch;
use Nyrok\QuazarCore\utils\PlayerUtils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent as ClassEvent;

final class PlayerJoinEvent implements Listener
{
    const NAME = "PlayerJoinEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        PlayerUtils::teleportToSpawn($event->getPlayer());
        LobbyManager::load($event->getPlayer());
        $event->setJoinMessage(LogsManager::getLogMessage(LogsManager::TYPE_JOIN, $event->getPlayer()));
        FloatingTextManager::update();
        CPSManager::load($event->getPlayer());
        AntiSwitch::unblacklist($event->getPlayer());
        $nick = PlayerProvider::toQuazarPlayer($event->getPlayer())->getData()["nick"] ?? null;
        NickCommand::setNick($event->getPlayer(), $nick !== "off" and !is_null($nick) ? $nick : null);
        CosmeticsManager::saveSkin($event->getPlayer()->getSkin(), $event->getPlayer()->getName());
    }
}