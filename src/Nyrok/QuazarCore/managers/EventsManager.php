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
        if(count($event->getPlayers()) >= 6) {
            $event->setStart();
            
            $worldN = match($event->getType()) {
                'nodebuff' => 'ndb-event',
                'sumo' => 'sumo-event',
                'soup' => 'soup-event',
            };
            
            $configCache = Core::getInstance()->getConfig()->getAll();
            
            $posData = $configCache["events"][$worldN]["spectators"]["spawn"];
            
            $world = Server::getInstance()->getLevelByName($worldN);
            $position = new Position($posData["x"], $posData["y"], $posData["z"], $world);
            
            foreach($event->getPlayers() as $player)
            {
                $player->teleport($position);
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
}