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
        self::$players[$host];
    }
}