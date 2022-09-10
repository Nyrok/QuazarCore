<?php

namespace Nyrok\QuazarCore\databases;

use pocketmine\utils\Config;

final class MuteListDatabase extends Config
{
    public function __construct(string $file, int $type = Config::DETECT, array $default = [], &$correct = null)
    {
        parent::__construct($file, $type, $default, $correct);
    }
}