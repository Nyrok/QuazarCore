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
    public function addPlayer(Player $player): void
    {
        self::$players[] = $player;
    }
    
    /**
     * @param Player $player
     * @return void
     */
    public function addPlayer(Player $player): void
    {
        self::$players[] = $player;
    }
}