<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\CooldownManager;
use Nyrok\QuazarCore\managers\CosmeticsManager;
use Nyrok\QuazarCore\managers\DuelsManager;
use Nyrok\QuazarCore\managers\EventsManager;
use Nyrok\QuazarCore\managers\FFAManager;
use Nyrok\QuazarCore\managers\KitsManager;
use Nyrok\QuazarCore\managers\LobbyManager;
use Nyrok\QuazarCore\managers\MatchmakingManager;
use Nyrok\QuazarCore\managers\SoupManager;
use Nyrok\QuazarCore\managers\StaffManager;
use Nyrok\QuazarCore\tasks\EnderPearlCooldownTask;
use pocketmine\block\BlockIds;
use pocketmine\block\Door;
use pocketmine\block\FenceGate;
use pocketmine\block\Trapdoor;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent as ClassEvent;
use pocketmine\item\ItemIds;

final class PlayerInteractEvent implements Listener
{
    const NAME = "PlayerInteractEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event)
    {
        $id = $event->getItem()?->getId() ?? 0;
        if (StaffManager::isStaff($event->getPlayer()) and $event->getItem()->getNamedTagEntry("staff")) {
            match ($id) {
                ItemIds::COMPASS => StaffManager::randomTP($event->getPlayer()),
                BlockIds::REDSTONE_BLOCK => StaffManager::turnOff($event->getPlayer()),
                default => null
            };
        } else if (EventsManager::getIfPlayerIsInEvent($event->getPlayer())) {
            match($id) {
                152 => EventsManager::removePlayer($event->getPlayer(), true),
                default => null
            };
        } else if (EventsManager::getIfPlayerIsSpectatorEvent($event->getPlayer())) {
            match ($id) {
                152 => EventsManager::removeSpectator($event->getPlayer()),
                default => null
            };
        }else {
            if ($event->getPlayer()->getLevel()->getName() === Core::getInstance()->getConfig()->getNested('positions.spawn.world', "")) {
                match ($id) {
                    267 => FFAManager::formFFAS($event->getPlayer()),
                    276 => MatchmakingManager::formMatchmaking($event->getPlayer()),
                    54 => KitsManager::formKits($event->getPlayer()),
                    264 => CosmeticsManager::formCosmetics($event->getPlayer()),
                    340 => LobbyManager::formStats($event->getPlayer()),
                    347 => LobbyManager::formSettings($event->getPlayer()),
                    -161 => MatchmakingManager::removePlayer($event->getPlayer()->getName()),
                    default => null
                };
                
            } else if ($id === SoupManager::getSoupId() and $event->getPlayer()->getHealth() != $event->getPlayer()->getMaxHealth()) {
                $event->getPlayer()->heal(new EntityRegainHealthEvent($event->getPlayer(), SoupManager::getSoupHeal(), EntityRegainHealthEvent::CAUSE_CUSTOM));
                $event->getPlayer()->getInventory()->setItemInHand($event->getItem()->setCount($event->getItem()->getCount() - 1));
            }

            if ($event->getAction() === $event::RIGHT_CLICK_AIR) {
                if ($event->getBlock() instanceof Door or $event->getBlock() instanceof Trapdoor or $event->getBlock() instanceof FenceGate) $event->setCancelled();
                foreach (CooldownManager::getCooldowns() as $cooldown) {
                    if ($cooldown->getItem()->equals($event->getPlayer()->getInventory()->getItemInHand())) {
                        if ($cooldown->has($event->getPlayer())) {
                            $event->setCancelled(true);
                        } else {
                            $cooldown->set($event->getPlayer());
                            Core::getInstance()->getScheduler()->scheduleRepeatingTask(new EnderPearlCooldownTask($cooldown, $event->getPlayer()), 1);
                        }
                    }
                }
            }

            $duel = DuelsManager::getDuel($event->getPlayer());

            foreach (DuelsManager::getDuels() as $d) {
                if ($d->started and $d->getOpponent()->getName() === $event->getPlayer()->getName()) {
                    $duel ??= $d;
                    break;
                }
            }

            if (!is_null($duel?->started)) {
                if (!in_array($event->getBlock()->getId(), $duel?->getArena()?->getBlocks() ?? [])) {
                    $event->setCancelled(true);
                }
            }
        }
    }
}