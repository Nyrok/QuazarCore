<?php
namespace Nyrok\QuazarCore\Databases;

use pocketmine\utils\Config;

final class ConfigDatabase extends Config {

    /**
     * @param string $file
     * @param int $type
     * @param array $default
     */
    public function __construct(string $file, int $type = parent::DETECT, array $default = []){
        parent::__construct($file, $type, $default);
    }
}
