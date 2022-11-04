<?php

namespace Nyrok\QuazarCore\tasks;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\DuelsManager;
use Nyrok\QuazarCore\managers\MatchmakingManager;
use Nyrok\QuazarCore\objects\Duel;
use pocketmine\scheduler\Task;

final class MatchmakingTask extends Task
{
    public function onRun(int $currentTick): void
    {
        foreach (MatchmakingManager::getMatchmaking() as $name => $data) {
            $player = Core::getInstance()->getServer()->getPlayer($name);
            if (!$player) {
                MatchmakingManager::removePlayer($name);
                return;
            }
            if ($found = MatchmakingManager::getClosest($data["elo"], $name)) {
                if (!$opponent = Core::getInstance()->getServer()->getPlayer($found["name"])) {
                    MatchmakingManager::removePlayer($found["name"]);
                    return;
                }
                $player->sendMessage("§aVous avez été match avec " . $found["name"]); // À faire
                MatchmakingManager::removePlayer($found["name"]);
                MatchmakingManager::removePlayer($name);
                DuelsManager::addDuel(new Duel($player, $opponent, $data["mode"]));
            } else {
                $player->sendActionBarMessage("§cMatchmaking en cours pour le mode: " . $data["mode"]->getName()); // À faire
            }
        }
    }
}