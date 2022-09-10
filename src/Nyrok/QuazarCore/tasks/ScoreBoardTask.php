<?php

namespace Nyrok\QuazarCore\tasks;

use Nyrok\QuazarCore\managers\ScoreBoardManager;
use pocketmine\scheduler\Task;
use pocketmine\Server;

final class ScoreBoardTask extends Task
{
    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick): void
    {
        foreach (ScoreBoardManager::getScoreboards() as $world => $scoreboard){
            if(Server::getInstance()->loadLevel($world)) ScoreBoardManager::updateScoreboard($scoreboard['scoreboard'], Server::getInstance()->getLevelByName($world));
        }
    }
}