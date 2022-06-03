<?php

namespace Nyrok\QuazarCore\Managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\Librairies\EasyUI\element\Dropdown;
use Nyrok\QuazarCore\Librairies\EasyUI\element\Option;
use Nyrok\QuazarCore\Librairies\EasyUI\element\Toggle;
use Nyrok\QuazarCore\Librairies\EasyUI\utils\FormResponse;
use Nyrok\QuazarCore\Librairies\EasyUI\variant\CustomForm;
use Nyrok\QuazarCore\Librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\Provider\LanguageProvider;
use Nyrok\QuazarCore\Provider\PlayerProvider;
use Nyrok\QuazarCore\Utils\AntiSpamForm;
use Nyrok\QuazarCore\Utils\PlayerUtils;
use pocketmine\item\Item;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

abstract class LobbyManager
{
    /**
     * @var Item[]
     */
    public static array $items = [];

    public static function initLobby(): void {
        self::$items[0] = Item::get(267)->setCustomName("FFA");
        Core::getInstance()->getLogger()->alert("[LOBBY] Item: FFA Loaded");
        self::$items[1] = Item::get(276)->setCustomName("Duel Unranked");
        Core::getInstance()->getLogger()->alert("[LOBBY] Item: Duel Unranked Loaded");
        self::$items[2] = Item::get(283)->setCustomName("Duel Ranked");
        Core::getInstance()->getLogger()->alert("[LOBBY] Item: Duel Ranked Loaded");
        self::$items[4] = Item::get(264)->setCustomName("Cosmetic");
        Core::getInstance()->getLogger()->alert("[LOBBY] Item: Cosmetic Loaded");
        self::$items[6] = Item::get(340)->setCustomName("Statistic");
        Core::getInstance()->getLogger()->alert("[LOBBY] Item: Statistic Loaded");
        self::$items[8] = Item::get(347)->setCustomName("Settings");
        Core::getInstance()->getLogger()->alert("[LOBBY] Item: Settings Loaded");
        $top = "";
        foreach($leaderboard = PlayerUtils::getLeaderboard(strtolower("kills")) as $name => $value){
            $top .= str_replace(["{position}", "{name}", "{value}"], [array_search($name, array_keys($leaderboard)) + 1, $name, $value], LanguageProvider::getLanguageMessage('forms.top.format'));
        }
        $floating = new FloatingTextParticle(self::getSpawnPosition(), $top, "§4Top §cKills");
        self::getSpawnPosition()->getLevel()->addParticle($floating, Server::getInstance()->getOnlinePlayers());
    }

    /**
     * @return Item[]
     */
    public static function getItems(): array
    {
        return self::$items;
    }

    public static function load(Player $player): void {
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->setContents(self::getItems());
        $player->setGamemode(Player::ADVENTURE);
    }

    public static function getSpawnPosition(): ?Position {
        $spawn = Core::getInstance()->getConfig()->getNested('positions.spawn') ?? [];
        if(!empty($spawn)) {
            return new Position((int)$spawn['x'], (int)$spawn['y'], (int)$spawn['z'], Server::getInstance()->getLevelByName($spawn['world'] ?? "world"));
        }
        return null;
    }

    public static function formStats(Player $player): void {
        $form = new SimpleForm("§m§a"."§fStats");
        $form->setHeaderText(PlayerUtils::getStats(PlayerProvider::toQuazarPlayer($player), false));
        AntiSpamForm::sendForm($player, $form);
    }

    public static function formSettings(Player $player): void {
        $form = new CustomForm("§m§a"."§fSettings");
        $toggle1 = new Toggle("Toggle 1");
        $toggle1->setDefaultChoice(true);
        $form->addElement("toggle1",$toggle1);
        $toggle = new Toggle("Toggle 2");
        $toggle->setDefaultChoice(true);

        $form->addElement("toggle",$toggle);
        $dropdown = new Dropdown("Language:");
        foreach (LanguageProvider::LANGUAGES as $LANGUAGE){
            $dropdown->addOption(new Option($LANGUAGE, $LANGUAGE));
        }
        $dropdown->setDefaultIndex(LanguageProvider::langToIndex(PlayerProvider::toQuazarPlayer($player)->getLanguage()));
        $form->addElement("language", $dropdown);
        $form->setSubmitListener(function (Player $player, FormResponse $formResponse): void {
            PlayerProvider::toQuazarPlayer($player)->setData("language", $formResponse->getDropdownSubmittedOptionId('language'), false, PlayerProvider::TYPE_STRING);
            $player->sendMessage(LanguageProvider::getLanguageMessage("messages.success.change", PlayerProvider::toQuazarPlayer($player), true));
        });
        AntiSpamForm::sendForm($player, $form);
    }
}