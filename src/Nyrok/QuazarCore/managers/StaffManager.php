<?php

namespace Nyrok\QuazarCore\managers;

use DateTime;
use DateTimeZone;
use JetBrains\PhpStorm\Pure;
use Nyrok\QuazarCore\librairies\EasyUI\element\Label;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\tasks\VanishTask;
use Nyrok\QuazarCore\utils\PlayerUtils;
use pocketmine\block\BlockIds;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\Player;
use Nyrok\QuazarCore\librairies\EasyUI\variant\CustomForm;
use Nyrok\QuazarCore\Core;
use pocketmine\Server;

abstract class StaffManager
{
    /**
     * @var Item[]
     */
    private static array $items = [];

    /**
     * @var Player[]
     */
    private static array $staffs = [];

    public static function initItems(): void {
        self::$items[0] = Item::get(BlockIds::ICE)->setCustomName("Freeze");
        self::$items[0]->setNamedTagEntry(new ByteTag("staff", 1));
        Core::getInstance()->getLogger()->notice("[STAFF] Item: Freeze Loaded");
        self::$items[2] = Item::get(ItemIds::COMPASS)->setCustomName("Random TP");
        self::$items[2]->setNamedTagEntry(new ByteTag("staff", 1));
        Core::getInstance()->getLogger()->notice("[STAFF] Item: Random TP Loaded");
        self::$items[4] = Item::get(ItemIds::BOOK)->setCustomName("Infos");
        self::$items[4]->setNamedTagEntry(new ByteTag("staff", 1));
        Core::getInstance()->getLogger()->notice("[STAFF] Item: Player Info Loaded");
        self::$items[8] = Item::get(BlockIds::REDSTONE_BLOCK)->setCustomName("Exit");
        self::$items[8]->setNamedTagEntry(new ByteTag("staff", 1));
        Core::getInstance()->getLogger()->notice("[STAFF] Item: Exit Loaded");

        foreach (self::getItems() as $key => $item){
            if($item instanceof Durable){
                $new = clone $item;
                $new->setUnbreakable(true);
                self::$items[$key] = $new;
            }
        }
    }

    public static function getItems(): array
    {
        return self::$items;
    }


    public static function Sanctions(Player $player, $target){
        $date = new DateTime();
        $message = "";
        $sanctions = Core::getInstance()->getSanctions()->get(strtolower($target->getName())) ?? array();
        if(!empty($sanctions)){
            foreach ($sanctions as $index => $array) {
                $message .= "§cSanction §4[§e$index"."§4] §f:\n";
                foreach ($array as $key => $value) {
                    if($key !== "Date :"){
                        $message .= "§f- §f$key §c$value\n";
                    }
                    else {
                        $message .= "§f- §f$key §c{$date->setTimezone(new DateTimeZone("Europe/Paris"))->setTimestamp($value)->format("d/m H:i")}\n";
                    }
                }
            }
        }
        if(empty($message)) $message .= "§e".$target->getName()." §fn'a aucune sanction !\n";
        $form = new CustomForm("§4[ §c{$target->getName()} §4] §f- §cSANCTIONS", null);
        $form->addElement("Sanctions", new Label($message));
        $player->sendForm($form);
    }

    public static function StaffSanctions(Player $player, $staff){
        $date = new DateTime();
        $i = 0;
        $message = "§lVoici la liste des sanctions accomplies par §c$staff §f:§r\n";
        foreach (Core::getInstance()->getSanctions()->getAll() as $name => $sanctions) {
            foreach ($sanctions as $index => $array) {
                if ($array["Staff :"] === $staff) {
                    $message .= "§cSanction §4[§e$index" . "§4] §fJoueur : §c$name §f:\n";
                    foreach ($array as $key => $value) {
                        if ($key !== "Date :") {
                            $message .= "§f- §f$key §c$value\n";
                        } else {
                            $message .= "§f- §f$key §c{$date->setTimezone(new DateTimeZone("Europe/Paris"))->setTimestamp($value)->format("d/m H:i")}\n";
                        }
                    }
                    $i++;
                }
            }
        }
        if ($i === 0) $message .= "> §e$staff §fn'a accomplie §faucune §cSanction.";
        $form = new CustomForm("§4[ §c$staff §4] §f- §cSANCTIONS FAITES", null);
        $form->addElement("Sanctions", new Label($message));
        $player->sendForm($form);
    }

    #[Pure] public static function isStaff(Player $player): bool
    {
        return isset(self::$staffs[$player->getName()]);
    }

    public static function turnOn(Player $player): void
    {
        $player->removeAllEffects();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->setContents(self::getItems());
        $player->setInvisible(true);
        $player->setAllowFlight(true);
        $player->despawnFromAll();
        Core::getInstance()->getScheduler()->scheduleRepeatingTask(new VanishTask($player), 1);
        self::$staffs[$player->getName()] = $player;
    }

    public static function turnOff(Player $player): void
    {
        unset(self::$staffs[$player->getName()]);
        LobbyManager::load($player);
        $player->setInvisible(false);
        $player->setAllowFlight(false);
        $player->setFlying(false);
        $player->respawnToAll();
        PlayerUtils::teleportToSpawn($player);
        LobbyManager::load($player);
    }

    public static function randomTP(Player $player): void
    {
        $target = Server::getInstance()->getOnlinePlayers()[array_rand(Server::getInstance()->getOnlinePlayers())];
        if($target?->getName() !== $player->getName()){
            $player->teleport($target->getLocation());
            $player->sendMessage(str_replace("{player}", $target->getName(), LanguageProvider::getLanguageMessage("messages.staff.randomtp", PlayerProvider::toQuazarPlayer($player), true)));
        }
        else if(count(Server::getInstance()->getOnlinePlayers()) > 1){
            self::randomTP($player);
        }
    }

    public static function freeze(Player $staff, Player $target): void
    {
        $staff->sendMessage(str_replace("{player}", $target->getName(), LanguageProvider::getLanguageMessage("messages.staff.freeze", PlayerProvider::toQuazarPlayer($staff), true)));
        $target->sendMessage(str_replace("{player}", $staff->getName(), LanguageProvider::getLanguageMessage("messages.staff.freezed", PlayerProvider::toQuazarPlayer($target), true)));
        $target->setImmobile(true);
    }

    public static function unfreeze(Player $staff, Player $target): void
    {
        $staff->sendMessage(str_replace("{player}", $target->getName(), LanguageProvider::getLanguageMessage("messages.staff.unfreeze", PlayerProvider::toQuazarPlayer($staff), true)));
        $target->sendMessage(str_replace("{player}", $staff->getName(), LanguageProvider::getLanguageMessage("messages.staff.unfreezed", PlayerProvider::toQuazarPlayer($target), true)));
        $target->setImmobile(false);
    }

    public static function playerInfo(Player $staff, Player $player)
    {
        $form = new CustomForm("§4[ §c{$player->getName()} §4] §f- §cINFOS", null);
        $form->addElement("infos", new Label(
            str_replace(
                ["{name}", "{ping}", "{platform}", "{cps}"],
                [$player->getName(), $player->getPing(), PlayerProvider::toQuazarPlayer($player)->getData()['platform'] ?? "Inconnue", CPSManager::getCps($player)],
                LanguageProvider::getLanguageMessage("messages.staff.playerinfo", PlayerProvider::toQuazarPlayer($staff), true)
            )
        ));
        $staff->sendForm($form);
    }

}