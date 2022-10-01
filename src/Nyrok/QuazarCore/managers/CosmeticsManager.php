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
        self::checkRequirement();
        self::checkCosmetique();
    }

    public static function formCosmetics(Player $player): void {
        $form = new SimpleForm("§m§a"."§fCosmetics");
        $form->addButton(new Button("§lReset", new ButtonIcon("textures/ui/refresh", ButtonIcon::TYPE_PATH)));
        foreach (self::$cosmeticsDetails as $folder => $cosmetic) {
            $form->addButton(new Button("§l".ucfirst($cosmetic), new ButtonIcon("textures/ui/".strtolower($cosmetic), ButtonIcon::TYPE_PATH)));
        }
        AntiSpamForm::sendForm($player, $form);
    }
}