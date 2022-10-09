<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\Core;
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
        if ($event->getTransaction()->getSource()->getLevel()->getName() === Core::getInstance()->getConfig()->getNested("positions.spawn.world") and
            !$event->getTransaction()->getSource()->hasPermission(Permission::DEFAULT_OP)) {
            $event->setCancelled();
        }
    }
}