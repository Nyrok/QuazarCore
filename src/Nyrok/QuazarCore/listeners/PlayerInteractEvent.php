<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\CooldownManager;
use Nyrok\QuazarCore\managers\CosmeticsManager;
use Nyrok\QuazarCore\managers\CPSManager;
use Nyrok\QuazarCore\managers\DuelsManager;
use Nyrok\QuazarCore\managers\EventsManager;
use Nyrok\QuazarCore\managers\FFAManager;
use Nyrok\QuazarCore\managers\LobbyManager;
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
        if ($event->getAction() === $event::LEFT_CLICK_AIR or $event->getAction() === $event::LEFT_CLICK_BLOCK) CPSManager::addClick($event->getPlayer());
        $id = $event->getItem()?->getId() ?? 0;
        if (StaffManager::isStaff($event->getPlayer()) and $event->getItem()->getNamedTagEntry("staff")) {
            match ($id) {
                ItemIds::COMPASS => StaffManager::randomTP($event->getPlayer()),
                BlockIds::REDSTONE_BLOCK => StaffManager::turnOff($event->getPlayer()),
                default => null
            };
        } else {
            if ($event->getPlayer()->getLevel()->getName() === Core::getInstance()->getConfig()->getNested('positions.spawn.world', "")) {
                match ($id) {
                    267 => FFAManager::formFFAS($event->getPlayer()),
                    276, 283 => 1,
                    264 => CosmeticsManager::formCosmetics($event->getPlayer()),
                    340 => LobbyManager::formStats($event->getPlayer()),
                    347 => LobbyManager::formSettings($event->getPlayer()),
                    152 => EventsManager::removePlayer($event->getPlayer()),
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
                            /*$event->getPlayer()->sendActionBarMessage(str_replace(
                                "{cooldown}",
                                (string)($cooldown->get($event->getPlayer()) - time()),
                                LanguageProvider::getLanguageMessage("messages.cooldowns.{$cooldown->getName()}", PlayerProvider::toQuazarPlayer($event->getPlayer()), false)
                            ));*/
                            $event->setCancelled(true);
                        } else {
                            $cooldown->set($event->getPlayer());
                            Core::getInstance()->getScheduler()->scheduleRepeatingTask(new EnderPearlCooldownTask($cooldown, $event->getPlayer()), 20);
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