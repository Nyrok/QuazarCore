<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\librairies\EasyUI\element\Button;
use Nyrok\QuazarCore\librairies\EasyUI\icon\ButtonIcon;
use Nyrok\QuazarCore\librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\traits\CosmeticsTrait;
use Nyrok\QuazarCore\utils\AntiSpamForm;
use pocketmine\Player;

abstract class CosmeticsManager
{
    use CosmeticsTrait;

    public static array $cosmeticsTypes = [];
    public static array $cosmeticsDetails = [];

    public static function initCosmetics(): void
    {
        Core::getInstance()->saveResource("steve.json", false);
        Core::getInstance()->saveResource("cosmetics/Ailes d'Ange.json", false);
        Core::getInstance()->saveResource("cosmetics/Ailes d'Ange.png", false);
        self::checkRequirement();
        self::checkCosmetique();
    }

    public static function formCosmetics(Player $player): void {
        $form = new SimpleForm("§m§a"."§fCosmetics");
        $form->addButton(new Button("§lReset", new ButtonIcon("textures/ui/refresh", ButtonIcon::TYPE_PATH), function (Player $player) {
            self::resetSkin($player);
        }));
        foreach (self::$cosmeticsDetails as $cosmetic) {
            $form->addButton(new Button("§l".ucfirst(reset($cosmetic)), new ButtonIcon("textures/ui/mashup_hangar", ButtonIcon::TYPE_PATH), function (Player $player) use ($cosmetic){
                self::setSkin($player, reset($cosmetic), "cosmetics");
            }));
        }
        AntiSpamForm::sendForm($player, $form);
    }
}