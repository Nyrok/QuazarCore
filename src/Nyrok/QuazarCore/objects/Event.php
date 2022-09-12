<?php

namespace Nyrok\QuazarCore\objects;

use pocketmine\Player;
use pocketmine\item\Item;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\managers\LobbyManager;

final class Event
{
    /**
     * @var $players[]
     */
    public static array $players = [];
    
    /**
     * @var $startIn
     */
    public static $startIn;
    
    public static $start = false;
    
    /**
     * @param string $name
     * @param string $type
     * @param Player $host
     */
    public function __construct(string $name, string $type, Player $host)
    {
        self::addPlayer($host);
        self::$startIn = time() + 120;
    }
    
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
    
    /**
     * @return Player
     */
    public function getHost(): Player
    {
        return $this->host;
    }
    
    /**
     * @return array
     */
    public function getPlayers(): array
    {
        return self::$players;
    }
    
    /**
     * @return int
     */
    public function getStartIn(): int
    {
        return self::$startIn;
    }
    
    /**
     * @return bool
     */
    public function getStart(): bool
    {
        return self::$start;
    }
    
    /**
     * @return void
     */
    public function setStart(): void
    {
        self::$start = true;
    }
    
    /**
     * @param Player $player
     * @return void
     */
    public function addPlayer(Player $player, bool $sendMsg = false): void
    {
        array_push(self::$players, $player);
        
        
        $item = [4 => Item::get(152)->setCustomName("§4Leave")];
        
        $player->removeAllEffects();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->setContents($item);
        
        
        if($sendMsg) {
            $message = LanguageProvider::getLanguageMessage("messages.events.event-join", PlayerProvider::toQuazarPlayer($player), true));
            $player->sendMessage($message);
        }
    }
    
    /**
     * @param Player $player
     * @return void
     */
    public function removePlayer(Player $player): void
    {
        unset(self::$players[$player]);
        LobbyManager::load($player);
    }
}