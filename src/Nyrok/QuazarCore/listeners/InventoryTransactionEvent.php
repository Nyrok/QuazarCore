<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\EventsManager;
use pocketmine\event\Listener;
use pocketmine\event\inventory\InventoryTransactionEvent as ClassEvent;
use pocketmine\permission\Permission;

final class InventoryTransactionEvent implements Listener
{
    const NAME = "InventoryTransactionEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event): void
    {
        foreach ($event->getTransaction()->getInventories() as $inventory) {
            if ($inventory->getName() === "invmenu:double_chest") return;
        }
        $player = $event->getTransaction()->getSource();
        if(EventsManager::getIfPlayerIsInEvent($player)) {

            $tournament = EventsManager::getEventByPlayer($player);

            if (!in_array($player->getName(), $tournament->getFighters())) {
                $event->setCancelled();
            }
        }
        else if(EventsManager::getIfPlayerIsSpectatorEvent($player)) $event->setCancelled();
        if ($event->getTransaction()->getSource()->getLevel()->getName() === Core::getInstance()->getConfig()->getNested("positions.spawn.world") and
            !$event->getTransaction()->getSource()->hasPermission(Permission::DEFAULT_OP)) {
            $event->setCancelled();
        }
    }
}