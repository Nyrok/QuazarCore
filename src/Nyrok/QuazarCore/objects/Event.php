<?php

namespace Nyrok\QuazarCore\objects;

use pocketmine\Player;

final class Event
{
    /**
     * @var $players[]
     */
    public static array $players = [];
    
    /**
     * @param string $name
     * @param string $type
     * @param Player $host
     */
    public function __construct(string $name, string $type, Player $host)
    {
        self::addPlayer($host);
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
    }
}