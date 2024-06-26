<?php

namespace Nyrok\QuazarCore\managers;

use JetBrains\PhpStorm\Pure;
use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\librairies\EasyUI\element\Button;
use Nyrok\QuazarCore\librairies\EasyUI\icon\ButtonIcon;
use Nyrok\QuazarCore\librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\objects\FFA;
use Nyrok\QuazarCore\utils\AntiSpamForm;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

abstract class FFAManager
{
    /**
     * @var FFA[]
     */
    public static array $ffas = [];

    public static function initFFAs()
    {
        foreach (Core::getInstance()->getConfig()->get('ffas', []) as $world => $data) {
            $name = $data["name"];
            $kit = $data["kit"];
            $texture = $data["texture"];
            $x = [$data["min-x"], $data["max-x"]];
            $y = $data["y"];
            $z = [$data["min-z"], $data["max-z"]];
            Core::getInstance()->getScheduler()->scheduleTask(new ClosureTask(function (int $currentTick) use ($name, $world, $kit, $texture, $x, $y, $z): void {
                $world = Server::getInstance()->getLevelByName($world);
                if ($world) {
                    self::$ffas[$name] = new FFA($name, $world, $kit, $texture, $x, $y, $z);
                }
            }));
            Core::getInstance()->getLogger()->notice("[FFAS] FFA: $name Loaded");
        }
    }

    /**
     * @param int $data
     * @return FFA|null
     */
    #[Pure] public static function dataToFFA(int $data): ?FFA
    {
        return array_values(self::getAllFFAS())[$data];
    }

    /**
     * @param string $world
     * @return FFA|null
     */
    #[Pure] public static function worldToFFA(string $world): ?FFA
    {
        foreach (self::getAllFFAS() as $ffa) {
            if ($ffa->getLevel()?->getName() === $world) return $ffa;
        }
        return null;
    }

    /**
     * @return FFA[]
     */
    public static function getAllFFAS(): array
    {
        return self::$ffas;
    }

    /**
     * @param Player $player
     */
    public static function formFFAS(Player $player): void
    {
        $form = new SimpleForm("§m§a" . "§fFFA");
        foreach (self::getAllFFAS() as $ffa) {
            $form->addButton(new Button($ffa->getName(), new ButtonIcon($ffa->getTexture(), ButtonIcon::TYPE_PATH), function (Player $player) use ($ffa): void {
                $ffa->start($player);
            }));
        }
        AntiSpamForm::sendForm($player, $form);
    }
}