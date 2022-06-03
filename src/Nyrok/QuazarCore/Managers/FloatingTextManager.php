<?php

namespace Nyrok\QuazarCore\Managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\Objects\FloatingText;
use Nyrok\QuazarCore\Provider\LanguageProvider;
use Nyrok\QuazarCore\Utils\PlayerUtils;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

abstract class FloatingTextManager
{
    public static array $floating_texts = [];

    public static function initFloatingTexts(): void {
        foreach (Core::getInstance()->getConfig()->get('floating-texts') as $title => $ft){
            $world = Server::getInstance()->getLevelByName($ft['position']['world']);
            if($world){
                $text = "";
                foreach($leaderboard = PlayerUtils::getLeaderboard($ft["type"]) as $name => $value){
                    $text .= str_replace(["{position}", "{name}", "{value}"], [array_search($name, array_keys($leaderboard)) + 1, $name, $value], LanguageProvider::getLanguageMessage('forms.top.format'));
                }
                $ft = new FloatingText($title, $text, new Position($ft['position']['x'], $ft['position']['y'], $ft['position']['z'], Core::getInstance()->getServer()->getLevelByName($ft['position']['world'])));
                self::$floating_texts[] = $ft;
                Core::getInstance()->getLogger()->alert("[FLOATING TEXTS] FT: {$ft->getTitle()} Loaded");
            }
        }
    }

    /**
     * @return FloatingText[]
     */
    public static function getFloatingTexts(): array
    {
        return self::$floating_texts;
    }

    public static function update(Player $player){
        foreach (self::getFloatingTexts() as $floatingText){
            $floatingText->spawn($player);
        }
    }
}