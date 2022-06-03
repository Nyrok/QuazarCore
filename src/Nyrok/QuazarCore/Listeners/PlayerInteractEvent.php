<?php

namespace Nyrok\QuazarCore\Listeners;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\Managers\FFAManager;
use Nyrok\QuazarCore\Managers\LobbyManager;
use Nyrok\QuazarCore\Managers\SoupManager;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent as ClassEvent;

final class PlayerInteractEvent implements Listener
{
    const NAME = "PlayerInteractEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        $id = $event->getItem()?->getId() ?? 0;
        if($event->getPlayer()->getLevel()->getName() === Core::getInstance()->getConfig()->getNested('positions.spawn.world', "")){
            match($id){
                267 => FFAManager::formFFAS($event->getPlayer()),
                276 => 1,
                283 => 1,
                264 => 1,
                340 => LobbyManager::formStats($event->getPlayer()),
                347 => LobbyManager::formSettings($event->getPlayer()),
                default => null
            };
        }
        else if($id === SoupManager::getSoupId() and $event->getPlayer()->getHealth() != $event->getPlayer()->getMaxHealth()){
            $event->getPlayer()->heal(new EntityRegainHealthEvent($event->getPlayer(), SoupManager::getSoupHeal(), EntityRegainHealthEvent::CAUSE_CUSTOM));
            $event->getPlayer()->getInventory()->setItemInHand($event->getItem()->setCount($event->getItem()->getCount() - 1));
        }
    }
}