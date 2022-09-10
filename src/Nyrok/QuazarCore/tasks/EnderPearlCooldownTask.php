<?php

namespace Nyrok\QuazarCore\tasks;

use pocketmine\scheduler\Task;
use pocketmine\Player;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\objects\Cooldown;

final class EnderPearlCooldownTask extends Task
{
    /**
     * @param Cooldown $cooldown
     * @param Player $player
     */
    public function __construct(private Cooldown $cooldown, private Player $player){}
    
    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        if($this->cooldown->has($this->player))
        {
            $player->getXpManager()->setXpLevel($this->cooldown->get($player - time());
        }else{
            $player->getXpManager()->setXpLevel(0);
            Core::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}