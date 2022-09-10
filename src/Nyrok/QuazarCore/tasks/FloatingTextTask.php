<?php

namespace Nyrok\QuazarCore\tasks;

use Nyrok\QuazarCore\managers\FloatingTextManager;
use pocketmine\scheduler\Task;

final class FloatingTextTask extends Task
{
    public function onRun(int $currentTick)
    {
        FloatingTextManager::update();
    }
}