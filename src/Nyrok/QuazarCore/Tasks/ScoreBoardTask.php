<?php

namespace Nyrok\QuazarCore\Tasks;

use Nyrok\QuazarCore\Managers\ScoreBoardManager;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ScoreBoardTask extends Task
{
    public function onRun(int $currentTick): void
    {
        foreach (ScoreBoardManager::getScoreboards() as $world => $scoreboard){
            if(Server::getInstance()->loadLevel($world)) ScoreBoardManager::updateScoreboard($scoreboard['scoreboard'], Server::getInstance()->getLevelByName($world));
        }
    }
}