<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\managers\CPSManager;
use Nyrok\QuazarCore\managers\DuelsManager;
use Nyrok\QuazarCore\managers\LogsManager;
use Nyrok\QuazarCore\managers\StaffManager;
use Nyrok\QuazarCore\managers\EventsManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\utils\AntiSwitch;
use pocketmine\event\player\PlayerQuitEvent as ClassEvent;
use pocketmine\event\Listener;
use pocketmine\Server;

final class PlayerQuitEvent implements Listener
{
    const NAME = "PlayerQuitEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        $event->setQuitMessage(LogsManager::getLogMessage(LogsManager::TYPE_LEAVE, $event->getPlayer()));
        CPSManager::unload($event->getPlayer());
        if(StaffManager::isStaff($event->getPlayer())) StaffManager::turnOff($event->getPlayer());
        AntiSwitch::unblacklist($event->getPlayer());
        if(DuelsManager::getDuel($event->getPlayer()->getName())){
            DuelsManager::getDuel($event->getPlayer()->getName())->stop();
            return;
        }

        foreach (DuelsManager::getDuels() as $duel){
            if($duel->getOpponent()->getName() === $event->getPlayer()->getName()){
                $duel->stop();
            }
        }
        
        if(EventsManager::getIfPlayerIsInEvent($event->getPlayer())) {

            $tournament = EventsManager::getEventByPlayer($event->getPlayer());

            EventsManager::removePlayer($event->getPlayer(), false, true);

            if(in_array($event->getPlayer()->getName(), $tournament->getFighters())) {

                $players = $tournament->getPlayers();
                $fighters = $tournament->getFighters();
                unset($fighters[array_search($event->getPlayer()->getName(), $fighters)]);
                $fighters = array_values($fighters);
                $killer = Server::getInstance()->getPlayerExact($fighters[0]);

                foreach ($players as $pName)
                {
                    $p = Server::getInstance()->getPlayerExact($pName);
                    $message = LanguageProvider::getLanguageMessage("messages.events.event-kill", PlayerProvider::toQuazarPlayer($p), true);
                    $message = str_replace(["{killer}", "{death}"], [$killer->getName(), $event->getPlayer()->getName()], $message);
                    $p->sendMessage($message);
                }

                if(!$tournament->getFightStart()) {

                    EventsManager::$task[$tournament->getName()]->cancel();
                    $killer->setImmobile(false);
                    $killer->sendTitle(" ");
                }

                EventsManager::teleportPlayerToEvent($killer, $tournament);
                $killer->removeAllEffects();
                $killer->getInventory()->clearAll();
                $killer->getArmorInventory()->clearAll();
                $killer->setHealth(20);

                EventsManager::startFights($tournament);
            }
        }

        if(EventsManager::getIfPlayerIsSpectatorEvent($event->getPlayer())) {
            EventsManager::removeSpectator($event->getPlayer());
        }
    }
}