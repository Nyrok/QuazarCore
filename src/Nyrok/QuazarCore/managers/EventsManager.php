<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\utils\PlayerUtils;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\tasks\EventsTask;
use AndreasHGK\EasyKits\manager\KitManager;
use Nyrok\QuazarCore\objects\Event;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\utils\Utils;

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
        self::$events[$event->getName()] = $event;
        
        $type = str_replace("nodebuff", "ndb", $event->getType());
        
        foreach(Server::getInstance()->getOnlinePlayers() as $p)
        {
            $p->sendMessage(LanguageProvider::getLanguageMessage("messages.events.event-" . $type . "-created", PlayerProvider::toQuazarPlayer($p), true));
        }
        
        Core::getInstance()->getScheduler()->scheduleRepeatingTask(new EventsTask($event), 20);
    }
    
    /**
     * @param Event $event
     * @return void
     */
    public static function removeEvent(Event $event): void
    {
        unset(self::$events[$event->getName()]);
    }
    
    public static function addPlayerToEvent(Player $player, Event $event): void
    {
        $event->addPlayer($player->getName());
        self::teleportPlayerToEvent($player, $event);
        
        $player->removeAllEffects();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        
        $item = [4 => Item::get(152)->setCustomName("§4Leave")];
        $player->getInventory()->setContents($item);
        
        $message = LanguageProvider::getLanguageMessage("messages.events.event-join", PlayerProvider::toQuazarPlayer($player), true);
        $player->sendMessage($message);
    }
    
    /**
     * @param Event $event
     * @return void
     */
    public static function startEvent(Event $event): void
    {
        $configCache = Core::getInstance()->getConfig()->getAll();
        
        $event->setStart();
        
        if(count($event->getPlayers()) >= (int)$configCache["events"]["min-players"]) {

            foreach($event->getPlayers() as $p)
            {
                $player = Server::getInstance()->getPlayerExact($p);
                
                $eventStartMsg = LanguageProvider::getLanguageMessage("messages.events.event-start", PlayerProvider::toQuazarPlayer($player), true);
                $player->sendMessage($eventStartMsg);
            }
            
            self::startFights($event);
        }else{

            self::removeEvent($event);
            
            foreach($event->getPlayers() as $p)
            {
                $player = Server::getInstance()->getPlayerExact($p);
                
                $player->sendMessage(LanguageProvider::getLanguageMessage("messages.events.event-not-enough-player", PlayerProvider::toQuazarPlayer($player), true));
                
                if(PlayerUtils::teleportToSpawn($player)) LobbyManager::load($player);
            }
        }
    }
    
    /**
     * @param Event $event
     * @return void
     */
    public static function endEvent(Event $event): void
    {
        $players = $event->getPlayers();

        foreach ($players as $player)
        {
            $p = Server::getInstance()->getPlayerExact($player);
            $message = LanguageProvider::getLanguageMessage("messages.events.winner", PlayerProvider::toQuazarPlayer($p), true);
            $message = str_replace("{winner}", $event->getPlayers()[0], $message);
            $p->sendMessage($message);

            PlayerUtils::teleportToSpawn($p);
        }

        self::removeEvent($event);
    }
    
    /**
     * @param Event $event
     * @return void
     */
    public static function startFights(Event $event): void
    {
        $players = count($event->getPlayers());

        if($players === 1) {

            self::endEvent($event);
            return;
        }

        $fighters = self::getFighters($event);

        if(count($fighters) < 2) {

            $event->resetFought();
            $fighters = self::getFighters($event);
        }

        $fightersRand = array_rand($fighters, 2);

        $fighter1Name = $fightersRand[0];
        $event->addFought($fighter1Name);

        $fighter2Name = $fightersRand[1];
        $event->addFought($fighter2Name);
        
        $worldN = match($event->getType()) {
            'sumo' => 'sumo-event',
            'soup' => 'soup-event',
            default => 'ndb-event'
        };

        $fighter1 = Server::getInstance()->getPlayerExact($fighter1Name);
        $fighter2 = Server::getInstance()->getPlayerExact($fighter2Name);
        
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

    public static function getFighters(Event $event): array
    {
        $fighters = $event->getPlayers();

        foreach($fighters as $player)
        {
            if(isset($event->getFought()[$player])) {

                unset($fighters[$player]);
            }
        }

        return $fighters;
    }

    public static function removePlayer(Player $player, bool $teleport = false): void
    {
        self::getEventByPlayer($player)->removePlayer($player->getName());
        if($teleport) if(PlayerUtils::teleportToSpawn($player)) LobbyManager::load($player);
    }

    public static function removeSpectator(Player $player): void
    {
        self::getEventBySpectator($player)->removePlayer($player->getName());
        if(PlayerUtils::teleportToSpawn($player)) LobbyManager::load($player);
    }
    
    public static function getEventByPlayer(Player $player): ?Event
    {
        foreach(self::getEvents() as $event)
        {
            if (in_array($player->getName(), $event->getPlayers())) {

                return $event;
            }
        }
        return null;
    }

    public static function getEventBySpectator(Player $player): ?Event
    {
        foreach(self::getEvents() as $event)
        {
            if (in_array($player->getName(), $event->getSpectators())) {

                return $event;
            }
        }
        return null;
    }

    public static function getEventByType(string $type): ?Event
    {
        foreach(self::getEvents() as $event)
        {
            if ($event->getType() === $type) {

                return $event;
            }
        }
        return null;
    }
    
    public static function getIfPlayerIsInEvent(Player $player): bool
    {
        foreach(self::getEvents() as $event)
        {
            if (in_array($player->getName(), $event->getPlayers())) {

                return true;
            }
        }
        return false;
    }
    
    public static function getIfEventTypeUsed(string $type): bool
    {
        foreach(self::getEvents() as $event)
        {
            if($event->getType() == $type) return true;
        }
        return false;
    }

    public static function getIfPlayerIsSpectatorEvent(Player $player): bool
    {
        foreach(self::getEvents() as $event)
        {
            if (in_array($player->getName(), $event->getSpectators())) {

                return true;
            }
        }
        return false;
    }
    
    public static function teleportPlayerToEvent(Player $player, Event $event): void
    {
        $configCache = Core::getInstance()->getConfig()->getAll();
        
        $worldN = match($event->getType()) {
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