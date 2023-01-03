<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\objects\DeadZone;
use pocketmine\math\Vector3;
use pocketmine\Server;

abstract class DeadZoneManager
{
    /**
     * @var array $deadZones
     */
    private static array $deadZones = [];

    /**
     * @return void
     */
    public static function initDeadZone(): void
    {
        $plugin = Core::getInstance();
        $config = $plugin->getConfig()->getAll()["deadzones"];

        foreach ($config as $worldName => $zone)
        {
            if($world = Server::getInstance()->getLevelByName($worldName)) {

                Server::getInstance()->loadLevel($worldName);

                $x = $zone["first"]["x"];
                $y = $zone["first"]["y"];
                $z = $zone["first"]["z"];
                $first = new Vector3($x, $y, $z);

                $x = $zone["second"]["x"];
                $y = $zone["second"]["y"];
                $z = $zone["second"]["z"];
                $second = new Vector3($x, $y, $z);

                self::$deadZones[$worldName] = new DeadZone($world, $first, $second);
            }else {

                $plugin->getLogger()->warning("The world $worldName does not exist !");
            }
        }
    }

    public static function getDeadZones(): array
    {
        return self::$deadZones;
    }
}