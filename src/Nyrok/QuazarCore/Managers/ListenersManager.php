<?php

namespace Nyrok\QuazarCore\Managers;

use JetBrains\PhpStorm\Pure;
use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\Listeners\DataPacketReceiveEvent;
use Nyrok\QuazarCore\Listeners\EntityDamageByEntityEvent;
use Nyrok\QuazarCore\Listeners\EntityDamageEvent;
use Nyrok\QuazarCore\Listeners\EntityLevelChangeEvent;
use Nyrok\QuazarCore\Listeners\InventoryPickupItemEvent;
use Nyrok\QuazarCore\Listeners\InventoryTransactionEvent;
use Nyrok\QuazarCore\Listeners\PlayerDataSaveEvent;
use Nyrok\QuazarCore\Listeners\PlayerDeathEvent;
use Nyrok\QuazarCore\Listeners\PlayerDropItemEvent;
use Nyrok\QuazarCore\Listeners\PlayerExhaustEvent;
use Nyrok\QuazarCore\Listeners\PlayerInteractEvent;
use Nyrok\QuazarCore\Listeners\PlayerItemConsumeEvent;
use Nyrok\QuazarCore\Listeners\PlayerJoinEvent;
use Nyrok\QuazarCore\Listeners\PlayerQuitEvent;
use Nyrok\QuazarCore\Listeners\PlayerRespawnEvent;
use pocketmine\plugin\Plugin;

abstract class ListenersManager
{
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
            new EntityLevelChangeEvent()
        ];
    }

    public static function initListeners(Plugin $plugin): void {
        foreach (self::getListeners() as $event){
            $plugin->getServer()->getPluginManager()->registerEvents($event, $plugin);
            Core::getInstance()->getLogger()->alert("[LISTENERS] Listener: ".$event::NAME." Loaded");
        }
    }

}