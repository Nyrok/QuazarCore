<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\objects\Duel;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

abstract class DuelsManager
{
    /**
     * @var Duel[]
     */
    private static array $duels = [];

    /**
     * @return array
     */
    public static function getDuels(): array
    {
        return self::$duels;
    }

    public static function getDuel(string $name): ?Duel
    {
        return self::$duels[$name] ?? null;
    }

    public static function addDuel(Duel $duel): void
    {
        self::$duels[$duel->getHost()->getName()] = $duel;
        Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($duel): void {
            if(isset(self::$duels[$duel->getHost()->getName()])){
                $duel = self::$duels[$duel->getHost()->getName()];
                if(!$duel->getOpponent()){
                    if ($player = Server::getInstance()->getPlayerExact($duel->getHost()->getName())){
                        $player->sendMessage("messages.duels.request-timeout");
                    }
                    self::removeDuel($duel);
                }
            }
        }), (20*60*10));
    }

    public static function removeDuel(Duel $duel): void
    {
        unset(self::$duels[$duel->getHost()->getName()]);
    }

}