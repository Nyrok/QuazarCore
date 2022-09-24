<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\managers\DuelsManager;
use pocketmine\event\block\BlockBreakEvent as ClassEvent;

final class BlockBreakEvent implements \pocketmine\event\Listener
{
    public function onEvent(ClassEvent $event): void
    {
        $duel = DuelsManager::getDuel($event->getPlayer());

        foreach (DuelsManager::getDuels() as $d) {
            if ($d->started and $d->getOpponent()->getName() === $event->getPlayer()->getName()) {
                $duel ??= $d;
                break;
            }
        }

        if (!$duel?->started) {
            if (!in_array($event->getBlock()->getId(), $duel->getArena()?->getBlocks())) {
                $event->setCancelled(true);
            }
        }
    }

}