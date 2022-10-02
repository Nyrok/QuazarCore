<?php

namespace Nyrok\QuazarCore\tasks;

use pocketmine\scheduler\Task;
use pocketmine\Player;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\objects\Cooldown;

final class EnderPearlCooldownTask extends Task
{
    private float $progress = 0.0;
    
    /**
     * @param Cooldown $cooldown
     * @param Player $player
     */
    public function __construct(private Cooldown $cooldown, private Player $player)
    {
    }
    
    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        if ($this->cooldown->has($this->player)) {
            if($this->progress === 0.0) {
                $this->player->setXpLevel($this->cooldown->get($this->player) - time());
                $this->progress = 1.0;
            }else{
                $this->player->setXpProgress($this->progress);
                $this->progress -= 0.1;
            }
        } else {
            $this->player->setXpLevel(0);
                $this->player->setXpProgress(0.0);
            Core::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}