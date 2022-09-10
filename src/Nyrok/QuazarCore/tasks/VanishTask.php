<?php

namespace Nyrok\QuazarCore\tasks;

use Nyrok\QuazarCore\managers\StaffManager;
use pocketmine\Player;
use pocketmine\scheduler\Task;

final class VanishTask extends Task
{
    public function __construct(private Player $player)
    {
    }

    public function onRun(int $currentTick)
    {
        if(StaffManager::isStaff($this->player)){
            $this->player->setInvisible(true);
            $this->player->despawnFromAll();
        }
        else {
            $this->getHandler()->cancel();
        }
    }
}