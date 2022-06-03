<?php

namespace Nyrok\QuazarCore\Managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\Provider\LanguageProvider;
use Nyrok\QuazarCore\Provider\PlayerProvider;
use Nyrok\QuazarCore\Utils\PlayerUtils;
use pocketmine\Player;

abstract class LogsManager
{
    public const TYPE_KILLS = "kills";
    public const TYPE_JOIN = "join";
    public const TYPE_LEAVE = "leave";

    /**
     * @param Player $victim
     * @param Player $killer
     * @param string $type
     */
    public static function sendKillMessage(Player $victim, Player $killer, string $type): void {
        $world = $victim->getLevel()->getName();
        foreach (Core::getInstance()->getServer()->getOnlinePlayers() as $player){
            $player->sendMessage(str_replace(
                ["{killer}", "{victim}", "{killerSoup}", "{victimSoup}", "{killerPopo}", "{victimPopo}"],
                [$killer->getName(), $victim->getName(), PlayerUtils::countSoup($killer), PlayerUtils::countSoup($victim), PlayerUtils::countPopo($killer), PlayerUtils::countPopo($victim)],
                LanguageProvider::getLanguageMessage("messages.worlds.$world.$type", PlayerProvider::toQuazarPlayer($player), true)));
        }
    }

    /**
     * @param string $type
     * @param Player|null $player
     * @return string
     */
    public static function getLogMessage(string $type, ?Player $player = null): string {
        return str_replace(["{player}"], [$player?->getName()], Core::getInstance()->getConfig()->getNested("messages.$type", ""));
    }
}