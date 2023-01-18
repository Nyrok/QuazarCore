<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\librairies\EasyUI\element\Button;
use Nyrok\QuazarCore\librairies\EasyUI\icon\ButtonIcon;
use Nyrok\QuazarCore\librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
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
        if(!$player->hasPermission("core.utils.cosmetics")) {
            $message = LanguageProvider::getLanguageMessage("messages.errors.cosmetics-not-permission", PlayerProvider::toQuazarPlayer($player), true);
            $player->sendMessage($message);
            return;
        }
        $form = new SimpleForm("§m§a"."§fCosmetics");
        $form->addButton(new Button("§lReset", new ButtonIcon("textures/ui/refresh", ButtonIcon::TYPE_PATH), function (Player $player) {
            self::resetSkin($player);
        }));
        foreach (self::$cosmeticsDetails as $cosmetic) {
            foreach ($cosmetic as $c){
                $form->addButton(new Button("§l".ucfirst($c), new ButtonIcon("textures/ui/mashup_hangar", ButtonIcon::TYPE_PATH), function (Player $player) use ($c){
                    self::setSkin($player, $c, "cosmetics");
                }));
            }
        }
        AntiSpamForm::sendForm($player, $form);
    }
}