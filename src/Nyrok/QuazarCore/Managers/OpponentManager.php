<?php

namespace Nyrok\QuazarCore\Managers;

use Nyrok\QuazarCore\Core;
use pocketmine\Player;

abstract class OpponentManager
{
    public static array $opponents = [];

    /**
     * @return array
     */
    public static function getOpponents(): array
    {
        return self::$opponents;
    }

    /**
     * @param Player $player
     * @return Player|null
     */
    public static function getOpponent(Player $player): ?Player {
        return Core::getInstance()->getServer()->getPlayerExact(self::getOpponents()[$player->getName()] ?? "");
    }

    /**
     * @param Player $player
     * @param Player|null $target
     */
    public static function setOpponent(Player $player, ?Player $target): void {
        self::$opponents[$player->getName()] = $target?->getName();
    }



}