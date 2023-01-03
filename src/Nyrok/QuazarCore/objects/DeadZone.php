<?php

namespace Nyrok\QuazarCore\objects;

use pocketmine\level\Level;
use pocketmine\math\Vector3;

class DeadZone
{
    public function __construct(private Level $world, private Vector3 $first, private Vector3 $second)
    {
    }

    public function getWorld(): Level
    {
        return $this->world;
    }

    public function getFirst(): Vector3
    {
        return $this->first;
    }

    public function getSecond(): Vector3
    {
        return $this->second;
    }
}