<?php

namespace Nyrok\QuazarCore\Managers;

use Nyrok\QuazarCore\Core;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

abstract class SoupManager
{
    public static function initSoups(): void {
        $soup = self::getSoup();
        Core::getInstance()->getLogger()->alert("[SOUPS] Soup: {$soup->getName()} ({$soup->getId()}:{$soup->getDamage()}) with ".self::getSoupHeal()." Heal Loaded");
    }

    public static function getSoup(): ?Item {
        return ItemFactory::get(self::getSoupId(), self::getSoupMeta());
    }

    public static function getSoupId(): int {
        return (int)Core::getInstance()->getConfig()->getNested('utils.soup.id', 0);
    }

    public static function getSoupMeta(): int {
        return (int)Core::getInstance()->getConfig()->getNested('utils.soup.meta', 0);
    }

    public static function getSoupHeal(): float {
        return (float)Core::getInstance()->getConfig()->getNested('utils.soup.heal', 2);
    }

}