<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\objects\Event;
use pocketmine\Server;

abstract class EventsManager
{
    /**
     * @var Events[]
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
        $configCache = Core::getInstance()->getConfig()->getAll();
        
        if(count($event->getPlayers()) >= (int)$configCache["events"]["min-players"]) {
            $event->setStart();
            
            $worldN = match($event->getType()) {
                'nodebuff' => 'ndb-event',
                'sumo' => 'sumo-event',
                'soup' => 'soup-event',
            };
            
            $posData = $configCache["events"][$worldN]["spectators"]["spawn"];
            
            $world = Server::getInstance()->getLevelByName($worldN);
            $position = new Position($posData["x"], $posData["y"], $posData["z"], $world);
            
            foreach($event->getPlayers() as $player)
            {
                $player->teleport($position);
                
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
        
        $fighter1 = $fighters[mt_rand(0, (int)(count($fighters) - 1)];
        $event->setFought($fighter1->getName());
        unset($fighters[$fighter1]);
        
        $fighter2 = $fighters[mt_rand(0, (int)(count($fighters) - 1)];
        $event->setFought($fighter2->getName());
        
        $position = new Position();
        
        $fighter1->teleport($position);
    }
}