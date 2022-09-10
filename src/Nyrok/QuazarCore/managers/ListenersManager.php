<?php

namespace Nyrok\QuazarCore\managers;

use JetBrains\PhpStorm\Pure;
use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\listeners\DataPacketReceiveEvent;
use Nyrok\QuazarCore\listeners\EntityArmorChangeEvent;
use Nyrok\QuazarCore\listeners\EntityDamageByEntityEvent;
use Nyrok\QuazarCore\listeners\EntityDamageEvent;
use Nyrok\QuazarCore\listeners\EntityLevelChangeEvent;
use Nyrok\QuazarCore\listeners\EntityTeleportEvent;
use Nyrok\QuazarCore\listeners\InventoryPickupItemEvent;
use Nyrok\QuazarCore\listeners\InventoryTransactionEvent;
use Nyrok\QuazarCore\listeners\LevelLoadEvent;
use Nyrok\QuazarCore\listeners\PlayerChatEvent;
use Nyrok\QuazarCore\listeners\PlayerCommandPreprocessEvent;
use Nyrok\QuazarCore\listeners\PlayerDataSaveEvent;
use Nyrok\QuazarCore\listeners\PlayerDeathEvent;
use Nyrok\QuazarCore\listeners\PlayerDropItemEvent;
use Nyrok\QuazarCore\listeners\PlayerExhaustEvent;
use Nyrok\QuazarCore\listeners\PlayerInteractEvent;
use Nyrok\QuazarCore\listeners\PlayerItemConsumeEvent;
use Nyrok\QuazarCore\listeners\PlayerItemHeldEvent;
use Nyrok\QuazarCore\listeners\PlayerJoinEvent;
use Nyrok\QuazarCore\listeners\PlayerLoginEvent;
use Nyrok\QuazarCore\listeners\PlayerMoveEvent;
use Nyrok\QuazarCore\listeners\PlayerQuitEvent;
use Nyrok\QuazarCore\listeners\PlayerRespawnEvent;
use Nyrok\QuazarCore\listeners\ProjectileHitBlockEvent;
use Nyrok\QuazarCore\listeners\ProjectileHitEntityEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\Plugin;

abstract class ListenersManager
{
    /**
     * @return Listener[]
     */
    #[Pure] public static function getListeners(): array {
        return [
            new PlayerJoinEvent(),
            new PlayerDeathEvent(),
            new PlayerExhaustEvent(),
            new PlayerItemConsumeEvent(),
            new PlayerDropItemEvent(),
            new PlayerQuitEvent(),
            new PlayerInteractEvent(),
            new InventoryTransactionEvent(),
            new InventoryPickupItemEvent(),
            new DataPacketReceiveEvent(),
            new PlayerDataSaveEvent(),
            new EntityDamageEvent(),
            new PlayerRespawnEvent(),
            new EntityDamageByEntityEvent(),
            new EntityLevelChangeEvent(),
            new EntityArmorChangeEvent(),
            new PlayerItemHeldEvent(),
            new ProjectileHitEntityEvent(),
            new PlayerMoveEvent(),
            new PlayerLoginEvent(),
            new PlayerCommandPreprocessEvent(),
            new PlayerChatEvent(),
            new LevelLoadEvent(),
            new ProjectileHitBlockEvent(),
            new EntityTeleportEvent()
        ];
    }

    /**
     * @param Plugin $plugin
     */
    public static function initListeners(Plugin $plugin): void {
        foreach (self::getListeners() as $event){
            $plugin->getServer()->getPluginManager()->registerEvents($event, $plugin);
            Core::getInstance()->getLogger()->notice("[LISTENERS] Listener: ".$event::NAME." Loaded");
        }
    }

}