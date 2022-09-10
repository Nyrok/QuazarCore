<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\managers\CPSManager;
use Nyrok\QuazarCore\managers\DuelsManager;
use Nyrok\QuazarCore\managers\LogsManager;
use Nyrok\QuazarCore\managers\StaffManager;
use Nyrok\QuazarCore\utils\AntiSwitch;
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
    }
}