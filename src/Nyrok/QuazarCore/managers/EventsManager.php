<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\KitManager;
use Nyrok\QuazarCore\objects\Event;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

abstract class EventsManager
{
    /**
     * @var Event[]
     */
    public static array $events = [];
    
    /**
     * @return array
     */
    public static function getEvents(): array
    {
        return self::$events;
    }
    
    /**
     * @param string $name
     * @return Event
     */
    public static function getEvent(string $name): Event
    {
        return self::$events[$name];
    }
    
    /**
     * @param Event $event
     * @return void
     */
    public static function addEvent(Event $event): void
    {
        self::$events[$event->getHost()->getName()] = $event;
        
        foreach(Server::getInstance()->getOnlinePlayers() as $p)
        {
            $p->sendMessage(LanguageProvider::getLanguageMessage("messages.events.event-ndb-created", PlayerProvider::toQuazarPlayer($p), true));
        }
    }
    
    /**
     * @param Event $event
     * @return void
     */
    public static function removeEvent(Event $event): void
    {
        unset(self::$events[$event->getHost()->getName()]);
    }
    
    /**
     * @param Event $event
     * @return void
     */
    public static function startEvent(Event $event): void
    {
        if(count($event->getPlayers()) >= (int)$configCache["events"]["min-players"]) {
            $event->setStart();
            
            foreach($event->getPlayers() as $player)
            {
                
                $eventStartMsg = LanguageProvider::getLanguageMessage("messages.events.event-start", PlayerProvider::toQuazarPlayer($player), true);
                $player->sendMessage($eventStartMsg);
                
                self::startFights($event);
            }
        }else{
            unset(self::$events[$event->getName()]);
            
            foreach($event->getPlayers() as $player)
            {
                $player->sendMessage(LanguageProvider::getLanguageMessage("messages.events.event-not-enough-player", PlayerProvider::toQuazarPlayer($player), true));
            }
            
            $event->cancel();
        }
    }
    
    /**
     * @param Event $event
     * @return void
     */
    public static function endEvent(Event $event): void
    {
        
    }
    
    /**
     * @param Event $event
     * @return void
     */
    public static function startFights(Event $event): void
    {
        $players = count($event->getPlayers());
        $fighters = $event->getPlayers();
        
        foreach($event->getPlayers() as $player)
        {
            if(isset($event->getFought()[$player->getName()])) {
                unset($fighters[$player->getName()]);
            }
        }
        
        $fighter1 = $fighters[mt_rand(0, count($fighters) - 1)];
        $event->setFought($fighter1->getName());
        unset($fighters[array_search($fighter1, $fighters)]);
        
        $fighter2 = $fighters[mt_rand(0, count($fighters) - 1)];
        $event->setFought($fighter2->getName());
        
        $worldN = match($event->getType()) {
            'nodebuff' =>'ndb-event',
            'sumo' => 'sumo-event',
            'soup' => 'soup-event',
            default => 'ndb-event'
        };
        
        $configCache = Core::getInstance()->getConfig()->getAll();
        
        $posData = $configCache["events"][$worldN]["duel"]["spawn"];
        $world = Server::getInstance()->getLevelByName($worldN);
        
        $posDataF1 = $posData["player1"];
        $position1 = new Position($posDataF1["x"], $posDataF1["y"], $posDataF1["z"], $world);
        
        $posDataF2 = $posData["player2"];
        $position2 = new Position($posDataF2["x"], $posDataF2["y"], $posDataF2["z"], $world);
        
        $fighter1->teleport($position1);
        $fighter2->teleport($position2);
        
        $kit = KitManager::get($configCache["events"][$worldN]["duel"]["kit"]);
        
        $kit->claimFor($fighter1);
        $kit->claimFor($fighter2);
    }

    public static function removePlayer(Player $player): void
    {
        foreach (self::getEvents() as $event){
            $event->removePlayer($player);
        }
        LobbyManager::load($player);
    }
    
    public static function getIfPlayerIsInEvent(Player $player): bool
    {
        foreach(self::getEvents() as $host => $event)
        {
            foreach($event->getPlayers() as $key => $p)
            {
                if($p->getName() == $player->getName()) return true;
            }
        }
        return false;
    }
    
    public static function teleportPlayerToEvent(Player $player, Event $event): void
    {
        $configCache = Core::getInstance()->getConfig()->getAll();
        
        $worldN = match($event->getType()) {
            'nodebuff' => 'ndb-event',
            'sumo' => 'sumo-event',
            'soup' => 'soup-event',
            default => 'ndb-event'
        };
            
        $posData = $configCache["events"][$worldN]["spectators"]["spawn"];
        
        $world = Server::getInstance()->getLevelByName($worldN);
        $position = new Position($posData["x"], $posData["y"], $posData["z"], $world);
        
        $player->teleport($position);
    }
}