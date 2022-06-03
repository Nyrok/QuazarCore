<?php

namespace Nyrok\QuazarCore\Managers;

use JetBrains\PhpStorm\Pure;
use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\Librairies\EasyUI\element\Button;
use Nyrok\QuazarCore\Librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\Objects\FFA;
use Nyrok\QuazarCore\Utils\AntiSpamForm;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

abstract class FFAManager
{
    /**
     * @var FFA[]
     */
    public static array $ffas = [];

    public static function initFFAs(){
            foreach (Core::getInstance()->getConfig()->get('ffas', []) as $world => $data){
                $name = $data["name"];
                $kit = $data["kit"];
                $x = [$data["min-x"], $data["max-x"]];
                $y = $data["y"];
                $z = [$data["min-z"], $data["max-z"]];
                Core::getInstance()->getScheduler()->scheduleTask(new ClosureTask(function (int $currentTick) use ($name, $world, $kit, $x, $y, $z): void {
                    $world = Server::getInstance()->getLevelByName($world);
                    self::$ffas[$name] = new FFA($name, $world, $kit, $x, $y, $z);
                }));
                Core::getInstance()->getLogger()->alert("[FFAS] FFA: $name Loaded");
            }
    }

    /**
     * @param int $data
     * @return FFA|null
     */
    #[Pure] public static function dataToFFA(int $data): ?FFA {
        return array_values(self::getAllFFAS())[$data];
    }

    /**
     * @param string $world
     * @return FFA|null
     */
    #[Pure] public static function worldToFFA(string $world): ?FFA {
        foreach(self::getAllFFAS() as $ffa){
            if($ffa->getLevel()?->getName() === $world) return $ffa;
        }
        return null;
    }

    /**
     * @return FFA[]
     */
    public static function getAllFFAS(): array {
        return self::$ffas;
    }

    public static function formFFAS(Player $player): void {
        $form = new SimpleForm("§m§a"."§fFFA");
        foreach (self::getAllFFAS() as $ffa){
            $form->addButton(new Button($ffa->getName(), null, function (Player $player) use ($ffa): void {
                $ffa?->start($player);
            }));
        }
        AntiSpamForm::sendForm($player, $form);
    }
}