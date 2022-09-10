<?php

namespace Nyrok\QuazarCore\utils;

use JetBrains\PhpStorm\Pure;
use Nyrok\QuazarCore\Core;
use pocketmine\form\Form;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;

abstract class AntiSpamForm
{
    /**
     * @var bool[]
     */
    private static array $opened = [];

    /**
     * @param Player $player
     * @return bool
     */
    #[Pure] public static function getOpened(Player $player): bool
    {
        return self::$opened[$player->getName()] ?? false;
    }

    /**
     * @param Player $player
     */
    public static function setOpened(Player $player): void
    {
        self::$opened[$player->getName()] = true;
    }

    /**
     * @param Player $player
     */
    public static function unsetOpened(Player $player): void
    {
        self::$opened[$player->getName()] = false;
    }

    /**
     * @param Player $player
     * @param Form $form
     */
    public static function sendForm(Player $player, Form $form): void {
        if(!self::getOpened($player)){
            $player->sendForm($form);
            self::setOpened($player);
            Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($player): void {
                self::unsetOpened($player);
            }), 5);
        }
    }
}