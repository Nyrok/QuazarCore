<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\EventsManager;
use Nyrok\QuazarCore\managers\FloatingTextManager;
use Nyrok\QuazarCore\managers\LobbyManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent as ClassEvent;
use pocketmine\item\ItemFactory;
use pocketmine\level\Position;
use pocketmine\Server;

final class PlayerRespawnEvent implements Listener
{
    const NAME = "PlayerRespawnEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){

        $player = $event->getPlayer();
        if(EventsManager::getIfPlayerIsSpectatorEvent($player)) {

            $tournament = EventsManager::getEventBySpectator($player);

            $worldN = match($tournament->getType()) {
                'sumo' => 'sumo-event',
                'soup' => 'soup-event',
                default => 'ndb-event'
            };

            $configCache = Core::getInstance()->getConfig()->getAll();

            $posData = $configCache["events"][$worldN]["spectators"]["spawn"];

            $world = Server::getInstance()->getLevelByName($worldN);
            $position = new Position($posData["x"], $posData["y"], $posData["z"], $world);
            $event->setRespawnPosition($position);

            $player->removeAllEffects();
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $item = [4 => ItemFactory::get(-161)->setCustomName("§4Leave")];
            $player->getInventory()->setContents($item);
        }else{

            $event->setRespawnPosition(LobbyManager::getSpawnPosition());
            LobbyManager::load($event->getPlayer());
            FloatingTextManager::update();
        }
    }
}