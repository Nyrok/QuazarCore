<?php

namespace Nyrok\QuazarCore\listeners;

use jkorn\pvpcore\PvPCore;
use Nyrok\QuazarCore\managers\EventsManager;
use Nyrok\QuazarCore\managers\LobbyManager;
use Nyrok\QuazarCore\managers\OpponentManager;
use Nyrok\QuazarCore\managers\StaffManager;
use Nyrok\QuazarCore\utils\AntiSwitch;
use Nyrok\QuazarCore\Core;
use pocketmine\block\BlockIds;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent as ClassEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\ItemIds;
use pocketmine\entity\projectile\FishingHook;
use pocketmine\Player;

final class EntityDamageByEntityEvent implements Listener
{
    const NAME = "EntityDamageByEntityEvent";

    /**
     * @param ClassEvent $event
     * @noinspection PhpParamsInspection
     * @noinspection PhpVoidFunctionResultUsedInspection
     * @priority HIGHEST
     */
    public function onEvent(ClassEvent $event){
        if($event->getEntity() instanceof Player and $event->getDamager() instanceof FishingHook){
            $event->setCancelled();
            $kb = Core::getInstance()->getConfig()['utils']['rod']['kb'];
            $event->getEntity()->attack(new ClassEvent($event->getDamager(), $event->getEntity(), EntityDamageEvent::CAUSE_ENTITY_ATTACK, 0, [], $kb));
            if(Core::getInstance()->getConfig()['utils']['rod']['dispawn']) $event->getDamager()->flagForDespawn();
        }
        
        if($event->getEntity() instanceof Player and $event->getDamager() instanceof Player){
            if($event->getCause() === $event::CAUSE_PROJECTILE) return;
            if(AntiSwitch::isBlacklist($event->getDamager())) $event->setCancelled();
            if(StaffManager::isStaff($event->getDamager()) and $event->getDamager()->getInventory()->getItemInHand()->getNamedTagEntry('staff')){
                match ($event->getDamager()->getInventory()->getItemInHand()->getId()){
                    BlockIds::ICE => ($event->getEntity()->isImmobile() ? StaffManager::unfreeze($event->getDamager(), $event->getEntity()) : StaffManager::freeze($event->getDamager(), $event->getEntity())),
                    ItemIds::BOOK => StaffManager::playerInfo($event->getDamager(), $event->getEntity()),
                    default => null
                };
            }

            if(EventsManager::getIfPlayerIsInEvent($event->getEntity())) {

                $tournament = EventsManager::getEventByPlayer($event->getEntity());

                if (!(in_array($event->getEntity()->getName(), $tournament->getFighters()) && in_array($event->getDamager()->getName(), $tournament->getFighters()))) {
                    $event->setCancelled();
                }
            }
            else if(EventsManager::getIfPlayerIsSpectatorEvent($event->getEntity())) $event->setCancelled();
            else if($event->getEntity()->getLevel() === LobbyManager::getSpawnPosition()->getLevel()) $event->setCancelled();
            else if((OpponentManager::getOpponent($event->getDamager())?->getName() ?? $event->getEntity()->getName()) !== $event->getEntity()->getName()){
                $event->setCancelled();
            }
            else if((OpponentManager::getOpponent($event->getEntity())?->getName() ?? $event->getDamager()->getName()) !== $event->getDamager()->getName()){
                $event->setCancelled();
            }
            else {
                OpponentManager::setOpponent($event->getDamager(), $event->getEntity());
                OpponentManager::setOpponent($event->getEntity(), $event->getDamager());
            }
            if(!$event->isCancelled()) AntiSwitch::blacklist($event->getDamager(), 0.05 * PvPCore::getWorldHandler()?->getWorld($event->getEntity()->getLevel())?->getKnockback()?->getSpeed() ?? $event->getAttackCooldown());
        }
    }
}