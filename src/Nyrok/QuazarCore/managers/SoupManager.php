<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

abstract class SoupManager
{
    public static function initSoups(): void {
        $soup = self::getSoup();
        Core::getInstance()->getLogger()->notice("[SOUPS] Soup: {$soup->getName()} ({$soup->getId()}:{$soup->getDamage()}) with ".self::getSoupHeal()." Heal Loaded");
    }

    /**
     * @return Item|null
     */
    public static function getSoup(): ?Item {
        return ItemFactory::get(self::getSoupId(), self::getSoupMeta());
    }

    /**
     * @return int
     */
    public static function getSoupId(): int {
        return (int)Core::getInstance()->getConfig()->getNested('utils.soup.id', 0);
    }

    /**
     * @return int
     */
    public static function getSoupMeta(): int {
        return (int)Core::getInstance()->getConfig()->getNested('utils.soup.meta', 0);
    }

    /**
     * @return float
     */
    public static function getSoupHeal(): float {
        return (float)Core::getInstance()->getConfig()->getNested('utils.soup.heal', 2);
    }

}