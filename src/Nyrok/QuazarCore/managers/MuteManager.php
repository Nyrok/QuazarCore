<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use pocketmine\Player;

abstract class MuteManager
{
    public static function setMuted(Player $player, bool $value = false, int $time = 0, string $reason = "Aucune raison donnée"): void
    {
        if($value) Core::getInstance()->getMuteList()->set($player->getName(), ["time" => $time, "reason" => $reason]);
        else Core::getInstance()->getMuteList()->remove($player->getName());
        Core::getInstance()->getMuteList()->save();
    }

    public static function isMuted(Player $player): bool
    {
        Core::getInstance()->getMuteList()->reload();
        return Core::getInstance()->getMuteList()->getNested($player->getName().".time", 0) > time();
    }

    public static function getMuteDate(Player $player): int
    {
        return Core::getInstance()->getMuteList()->getNested($player->getName().".time", 0);
    }
    public static function getMuteReason(Player $player): string
    {
        return Core::getInstance()->getMuteList()->getNested($player->getName().".reason", "Aucune raison donnée");
    }
}