<?php

namespace Nyrok\QuazarCore\tasks;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\DeadZoneManager;
use Nyrok\QuazarCore\objects\DeadZone;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class DeadZoneTask extends Task
{
    /**
     * @param string $playerName
     */
    public function __construct(private string $playerName)
    {
    }

    public function onRun(int $currentTick): void
    {
        $player = Server::getInstance()->getPlayerExact($this->playerName);

        if($player instanceof Player) {

            foreach(DeadZoneManager::getDeadZones() as $deadZone)
            {
                if($deadZone instanceof DeadZone) {

                    if ($player->getLevel()->getName() == $deadZone->getWorld()) {

                        $first = $deadZone->getFirst();
                        $second = $deadZone->getSecond();
                        $toCheck = $player->getPosition();
                        if((min($first->getX(), $second->getX()) <= $toCheck->getX()) && (max($first->getX(), $second->getX()) >= $toCheck->getX()) && (min($first->getZ(), $second->getZ()) <= $toCheck->getZ()) && (max($first->getZ(), $second->getZ()) >= $toCheck->getZ())){

                            $player->kill();
                        }
                    }
                }
            }
        }else {

            Core::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}