<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\librairies\EasyUI\element\Dropdown;
use Nyrok\QuazarCore\librairies\EasyUI\element\Option;
use Nyrok\QuazarCore\librairies\EasyUI\element\Toggle;
use Nyrok\QuazarCore\librairies\EasyUI\utils\FormResponse;
use Nyrok\QuazarCore\librairies\EasyUI\variant\CustomForm;
use Nyrok\QuazarCore\librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\utils\AntiSpamForm;
use Nyrok\QuazarCore\utils\PlayerUtils;
use pocketmine\block\BlockIds;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
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
        Core::getInstance()->getLogger()->notice("[LOBBY] Item: FFA Loaded");
        self::$items[1] = Item::get(276)->setCustomName("Duel Unranked");
        Core::getInstance()->getLogger()->notice("[LOBBY] Item: Duel Unranked Loaded");
        self::$items[4] = Item::get(264)->setCustomName("Cosmetic");
        Core::getInstance()->getLogger()->notice("[LOBBY] Item: Cosmetic Loaded");
        self::$items[6] = Item::get(340)->setCustomName("Statistic");
        Core::getInstance()->getLogger()->notice("[LOBBY] Item: Statistic Loaded");
        self::$items[7] = Item::get(54)->setCustomName("Kit Editor");
        Core::getInstance()->getLogger()->notice("[LOBBY] Item: Kit Editor Loaded");
        self::$items[8] = Item::get(347)->setCustomName("Settings");
        Core::getInstance()->getLogger()->notice("[LOBBY] Item: Settings Loaded");

        foreach (self::getItems() as $key => $item){
            if($item instanceof Durable){
                $new = clone $item;
                $new->setUnbreakable(true);
                self::$items[$key] = $new;
            }
        }
    }

    /**
     * @return Item[]
     */
    public static function getItems(): array
    {
        return self::$items;
    }

    /**
     * @param Player $player
     */
    public static function load(Player $player): void {
        $player->removeAllEffects();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->setContents(self::getItems());
        $player->setGamemode(Player::ADVENTURE);
    }

    /**
     * @return Position|null
     */
    public static function getSpawnPosition(): ?Position {
        $spawn = Core::getInstance()->getConfig()->getNested('positions.spawn') ?? [];
        if(!empty($spawn)) {
            return new Position((int)$spawn['x'], (int)$spawn['y'], (int)$spawn['z'], Server::getInstance()->getLevelByName($spawn['world'] ?? "world"));
        }
        return null;
    }

    /**
     * @param Player $player
     */
    public static function formStats(Player $player): void {
        $form = new SimpleForm("§m§c"."§fStats");
        $form->setHeaderText(PlayerUtils::getStats(PlayerProvider::toQuazarPlayer($player), false));
        AntiSpamForm::sendForm($player, $form);
    }

    /**
     * @param Player $player
     */
    public static function formSettings(Player $player): void {
        $form = new CustomForm("§m§c"."§fSettings");
        $scoreboard = new Toggle("ScoreBoard");
        $scoreboard->setDefaultChoice(PlayerProvider::toQuazarPlayer($player)->getData()['scoreboard'] ?? false);
        $form->addElement("scoreboard",$scoreboard);
        $cps = new Toggle("CPS");
        $cps->setDefaultChoice(PlayerProvider::toQuazarPlayer($player)->getData()['cps'] ?? false);
        $form->addElement("cps",$cps);
        $dropdown = new Dropdown("");
        foreach (LanguageProvider::LANGUAGES as $LANGUAGE){
            $dropdown->addOption(new Option($LANGUAGE, $LANGUAGE));
        }
        $dropdown->setDefaultIndex(LanguageProvider::langToIndex(PlayerProvider::toQuazarPlayer($player)->getLanguage()));
        $form->addElement("language", $dropdown);
        $form->setSubmitListener(function (Player $player, FormResponse $formResponse): void {
            PlayerProvider::toQuazarPlayer($player)->setData("language", $formResponse->getDropdownSubmittedOptionId('language'), false, PlayerProvider::TYPE_STRING);
            $player->sendMessage(LanguageProvider::getLanguageMessage("messages.success.change", PlayerProvider::toQuazarPlayer($player), true));

            if($formResponse->getToggleSubmittedChoice('cps')){
                CPSManager::activate($player);
                PlayerProvider::toQuazarPlayer($player)->setData('cps', true, false, PlayerProvider::TYPE_BOOL);
            }
            else {
                CPSManager::desactivate($player);
                PlayerProvider::toQuazarPlayer($player)->setData('cps', false, false, PlayerProvider::TYPE_BOOL);
            }

            if($formResponse->getToggleSubmittedChoice('scoreboard')){
                PlayerProvider::toQuazarPlayer($player)->setData('scoreboard', true, false, PlayerProvider::TYPE_BOOL);
            }
            else {
                PlayerProvider::toQuazarPlayer($player)->setData('scoreboard', false, false, PlayerProvider::TYPE_BOOL);
            }
        });
        AntiSpamForm::sendForm($player, $form);
    }
}