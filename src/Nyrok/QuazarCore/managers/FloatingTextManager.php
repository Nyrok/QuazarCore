<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\objects\FloatingText;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\tasks\FloatingTextTask;
use Nyrok\QuazarCore\utils\PlayerUtils;
use pocketmine\level\Position;
use pocketmine\Server;

abstract class FloatingTextManager
{
    /**
     * @var FloatingText[]
     */
    public static array $floating_texts = [];

    public static function initFloatingTexts(): void {
        foreach (Core::getInstance()->getConfig()->get('floating-texts') as $title => $ft){
            $world = Server::getInstance()->getLevelByName($ft['position']['world']);
            if($world){
                $text = "";
                foreach($leaderboard = PlayerUtils::getLeaderboard($ft["type"]) as $name => $value){
                    $text .= str_replace(["{position}", "{name}", "{value}"], [array_search($name, array_keys($leaderboard)) + 1, $name, $value], LanguageProvider::getLanguageMessage('forms.top.format'));
                }
                $ft = new FloatingText($title, $text, new Position($ft['position']['x'], $ft['position']['y'], $ft['position']['z'], Core::getInstance()->getServer()->getLevelByName($ft['position']['world'])), $ft["type"]);
                self::$floating_texts[] = $ft;
                Core::getInstance()->getLogger()->notice("[FLOATING TEXTS] FT: {$ft->getTitle()} Loaded");
            }
        }
        Core::getInstance()->getScheduler()->scheduleRepeatingTask(new FloatingTextTask(), 7*20);
    }

    /**
     * @return FloatingText[]
     */
    public static function getFloatingTexts(): array
    {
        return self::$floating_texts;
    }

    /**
     * @return void
     */
    public static function update(): void {
        foreach (self::getFloatingTexts() as $floatingText){
            $floatingText->spawn();
        }
    }

    public static function delete(){
        foreach (self::getFloatingTexts() as $floatingText){
            $floatingText->delete();
        }
    }
}