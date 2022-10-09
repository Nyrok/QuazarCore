<?php

namespace Nyrok\QuazarCore\managers;

use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\KitManager;
use Nyrok\QuazarCore\librairies\EasyUI\element\Button;
use Nyrok\QuazarCore\librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\librairies\muqsit\invmenu\InvMenu;
use Nyrok\QuazarCore\librairies\muqsit\invmenu\MenuIds;
use Nyrok\QuazarCore\librairies\muqsit\invmenu\transaction\InvMenuTransaction;
use Nyrok\QuazarCore\librairies\muqsit\invmenu\transaction\InvMenuTransactionResult;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\block\BlockIds;
use Nyrok\QuazarCore\librairies\muqsit\invmenu\inventory\InvMenuInventory;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\permission\Permission;
use pocketmine\Player;

abstract class KitsManager
{

    public static function getKit(Player $player, $name): ?Kit
    {
        $provider = PlayerProvider::toQuazarPlayer($player);
        if (!isset($provider->getData()["kits"][$name])) return null;
        $kit = $provider->getData()["kits"][$name];
        $items = json_decode($kit["items"], true);
        $armor = json_decode($kit["armor"], true);
        foreach ($items as $slot => $item) {
            $items[$slot] = Item::jsonDeserialize($item);
        }
        foreach ($armor as $slot => $item) {
            $armor[$slot] = Item::jsonDeserialize($item);
        }
        $return = new Kit("$name ({$provider->player->getName()})", Permission::DEFAULT_TRUE, 0.0, 0, $items, $armor);
        $return->setDoOverride(true);
        $return->setDoOverrideArmor(true);
        $return->setAlwaysClaim(true);
        return $return;
    }

    public static function formKits(Player $player): void
    {
        $form = new SimpleForm("§m§a" . "§fKits", "§fModifier l'ordre des items de vos kits.");
        foreach (FFAManager::getAllFFAS() as $FFA) {
            $form->addButton(new Button(
                "§fKit:  " . $FFA->getName(),
                null, // new ButtonIcon("", ButtonIcon::TYPE_PATH),
                function (Player $player) use ($FFA) {
                    self::editKit($player, $FFA->getKit()->getName());
                }
            ));
        }
        $player->sendForm($form);
    }

    public static function editKit(Player $player, string $name): void
    {
        $menu = InvMenu::create(MenuIds::TYPE_DOUBLE_CHEST);
        $menu->setName("§fKit: §c$name");
        $reset = function () use ($name, $menu) {
            $menu->getInventory()->setContents([]);
            foreach (KitManager::get($name)->getItems() as $slot => $item) {
                $menu->getInventory()->setItem($slot, $item);
            }
            foreach (KitManager::get($name)->getArmor() as $slot => $armor) {
                $menu->getInventory()->setItem(match ($slot) {
                    0 => 47,
                    1 => 48,
                    2 => 50,
                    3 => 51,
                }, $armor);
            }
            for ($i = 36; $i < 54; $i++) {
                switch ($i) {
                    case 42:
                        $item = ItemFactory::get(BlockIds::STAINED_GLASS_PANE, 14);
                        $item->setCustomName("Bloqué");
                        $item->setNamedTagEntry(new ByteTag("immobile", 1));
                        $item->setLore(["Casque"]);
                        $menu->getInventory()->setItem($i, $item);
                        break;
                    case 41:
                        $item = ItemFactory::get(BlockIds::STAINED_GLASS_PANE, 14);
                        $item->setCustomName("Bloqué");
                        $item->setNamedTagEntry(new ByteTag("immobile", 1));
                        $item->setLore(["Plastron"]);
                        $menu->getInventory()->setItem($i, $item);
                        break;
                    case 39:
                        $item = ItemFactory::get(BlockIds::STAINED_GLASS_PANE, 14);
                        $item->setCustomName("Bloqué");
                        $item->setNamedTagEntry(new ByteTag("immobile", 1));
                        $item->setLore(["Jambières"]);
                        $menu->getInventory()->setItem($i, $item);
                        break;
                    case 38:
                        $item = ItemFactory::get(BlockIds::STAINED_GLASS_PANE, 14);
                        $item->setCustomName("Bloqué");
                        $item->setNamedTagEntry(new ByteTag("immobile", 1));
                        $item->setLore(["Bottes"]);
                        $menu->getInventory()->setItem($i, $item);
                        break;
                    case 47:
                    case 48:
                    case 50:
                    case 51:
                    case 53:
                        break;
                    default:
                        $item = ItemFactory::get(BlockIds::STAINED_GLASS_PANE, 15);
                        $item->setCustomName("Bloqué");
                        $item->setNamedTagEntry(new ByteTag("immobile", 1));
                        $menu->getInventory()->setItem($i, $item);
                        break;
                }
            }
            $item = ItemFactory::get(BlockIds::WOOL, 14)->setCustomName("§cRéinitialiser");
            $nbt = $item->getNamedTag();
            $nbt->setByte("reset", 1);
            $item->setNamedTag($nbt);
            $menu->getInventory()->setItem(53, $item);
        };
        $reset();
        $menu->setInventoryCloseListener(function (Player $player, InvMenuInventory $inventory) use ($name) {
            $kits = PlayerProvider::toQuazarPlayer($player)->getData()["kits"] ?? [];
            $kits[$name]["items"] = json_encode(array_filter($inventory->getContents(), function ($item, $slot) {
                return $item->getNamedTag()->getByte("immobile", 0) !== 1 and
                    $item->getNamedTag()->getByte("reset", 0) !== 1 and
                    !in_array($slot, [47, 48, 50, 51]);
            }, ARRAY_FILTER_USE_BOTH));
            $kits[$name]["armor"] = json_encode(array_values(
                array_filter($inventory->getContents(), function ($item, $slot) {
                    return $item->getNamedTag()->getByte("immobile", 0) !== 1 and
                        $item->getNamedTag()->getByte("reset", 0) !== 1 and
                        in_array($slot, [47, 48, 50, 51]);
                }, ARRAY_FILTER_USE_BOTH)
            ));
            PlayerProvider::toQuazarPlayer($player)->setData("kits", $kits, false, PlayerProvider::TYPE_ARRAY);
            LobbyManager::load($player);
        });
        $menu->setListener(function (InvMenuTransaction $invMenuTransaction) use ($reset) {
            if ($invMenuTransaction->getItemClicked()->getNamedTag()->hasTag("immobile", ByteTag::class)) {
                return new InvMenuTransactionResult(true);
            } else if ($invMenuTransaction->getItemClicked()->getNamedTag()->hasTag("reset", ByteTag::class)) {
                $reset();
                return new InvMenuTransactionResult(true);
            } else if ($invMenuTransaction->getItemClicked() instanceof Armor) {
                return new InvMenuTransactionResult(true);
            } else {
                foreach ($invMenuTransaction->getTransaction()->getInventories() as $inventory) {
                    if ($inventory->getName() !== MenuIds::TYPE_DOUBLE_CHEST and $inventory->getName() !== "UI") {
                        $reset();
                        return new InvMenuTransactionResult(true);
                    }
                }
            }
            return new InvMenuTransactionResult(false);
        });
        $menu->send($player);
    }
}