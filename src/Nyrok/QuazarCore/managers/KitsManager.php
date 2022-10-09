<?php

namespace Nyrok\QuazarCore\managers;

use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\KitManager;
use Nyrok\QuazarCore\librairies\EasyUI\element\Button;
use Nyrok\QuazarCore\librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\librairies\muqsit\invmenu\InvMenu;
use Nyrok\QuazarCore\librairies\muqsit\invmenu\MenuIds;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\Player;

abstract class KitsManager
{
    private static array $kits = [];
    public static function getKit(PlayerProvider $player, $type): Kit {
        return new Kit("", "", 0.0, 0, [], []);
    }

    public static function getKits(): array {
        return self::$kits;
    }

    public static function formKits(Player $player): void
    {
        $form = new SimpleForm("§m§a"."§fKits", "§fModifier l'ordre des items de vos kits.");
        foreach(FFAManager::getAllFFAS() as $FFA){
            $form->addButton(new Button(
                "§cKit: ".$FFA->getName(),
                null, // new ButtonIcon("", ButtonIcon::TYPE_PATH),
                function (Player $player) use ($FFA) {
                    $this->editKit($player, $FFA->getKit()->getName());
                }
            ));
        }
        $player->sendForm($form);
    }

    public static function editKit(Player $player, string $name): void {
        $menu = InvMenu::create(MenuIds::TYPE_DOUBLE_CHEST);
        $menu->setName("§fKit: §c$name");
        foreach (KitManager::get($name)->getItems() as $slot => $item){
            $menu->getInventory()->setItem($slot, $item);
        }
        foreach(KitManager::get($name)->getArmor() as $slot => $armor){
            $menu->getInventory()->setItem(match($slot){
                0 => 47,
                1 => 48,
                2 => 50,
                3 => 51,
            }, $armor);
        }
        for($i = 36; $i < 54; $i++) {
            switch($i) {
                case 42:
                    $item = ItemFactory::get(Item::STAINED_GLASS_PANE, 14);
                    $item->setCustomName("Bloqué");
                    $item->setNamedTagEntry(new ByteTag("immovable", 1));
                    $item->setLore(["Casque"]);
                    $menu->getInventory()->setItem($i, $item);
                    break;
                case 41:
                    $item = ItemFactory::get(Item::STAINED_GLASS_PANE, 14);
                    $item->setCustomName("Bloqué");
                    $item->setNamedTagEntry(new ByteTag("immovable", 1));
                    $item->setLore(["Plastron"]);
                    $menu->getInventory()->setItem($i, $item);
                    break;
                case 39:
                    $item = ItemFactory::get(Item::STAINED_GLASS_PANE, 14);
                    $item->setCustomName("Bloqué");
                    $item->setNamedTagEntry(new ByteTag("immovable", 1));
                    $item->setLore(["Jambières"]);
                    $menu->getInventory()->setItem($i, $item);
                    break;
                case 38:
                    $item = ItemFactory::get(Item::STAINED_GLASS_PANE, 14);
                    $item->setCustomName("Bloqué");
                    $item->setNamedTagEntry(new ByteTag("immovable", 1));
                    $item->setLore(["Bottes"]);
                    $menu->getInventory()->setItem($i, $item);
                    break;
                case 47:
                case 48:
                case 50:
                case 51:
                    break;
                default:
                    $item = ItemFactory::get(Item::STAINED_GLASS_PANE, 15);
                    $item->setCustomName("Bloqué");
                    $item->setNamedTagEntry(new ByteTag("immovable", 1));
                    $menu->getInventory()->setItem($i, $item);
                    break;
            }
        }
        $menu->setListener(function (){

        });
        $menu->send($player);
    }
}