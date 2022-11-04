<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\librairies\EasyUI\element\Button;
use Nyrok\QuazarCore\librairies\EasyUI\icon\ButtonIcon;
use Nyrok\QuazarCore\librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\objects\Mode;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\tasks\MatchmakingTask;
use pocketmine\Player;

abstract class MatchmakingManager
{
    private static array $matchmaking = [];

    public static function initMatchmaking(): void
    {
        Core::getInstance()->getScheduler()->scheduleRepeatingTask(new MatchmakingTask(), 20);
    }

    /**
     * @param Player $player
     * @param Mode $mode
     */
    public static function addPlayer(Player $player, Mode $mode): void
    {
        self::$matchmaking[$player->getName()] = [
            "elo" => PlayerProvider::toQuazarPlayer($player)->getElo(),
            "mode" => $mode
        ];
    }

    /**
     * @param Player $player
     */
    public static function removePlayer(string $name): void
    {
        unset(self::$matchmaking[$name]);
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function isPlayerInMatchmaking(string $name): bool
    {
        return isset(self::$matchmaking[$name]);
    }

    /**
     * @param Player $player
     * @return Mode
     */
    public static function getPlayerMode(Player $player): Mode
    {
        return end(self::$matchmaking[$player->getName()]);
    }

    /**
     * @param Player $player
     * @return int
     */
    public static function getPlayerElo(Player $player): int
    {
        return reset(self::$matchmaking[$player->getName()]);
    }

    /**
     * @return array
     */
    public static function getMatchmaking(): array
    {
        return self::$matchmaking;
    }

    /**
     * @param int $search
     * @param string $player
     * @return array|null
     */
    public static function getClosest(int $search, string $player): ?array
    {
        $closest = null;
        foreach (self::getMatchmaking() as $username => $data) {
            if (($closest === null || abs($search - $closest["data"]["elo"]) > abs($data["elo"] - $search)) && $username !== $player) {
                $closest = [
                    "name" => $username,
                    "data" => $data
                ];
            }
        }
        return $closest;
    }

    public function formMatchmaking(Player $player): void
    {
        $form = new SimpleForm("§m§a" . "Duels", "Choisir le Mode de Jeu");
        foreach (ArenasManager::getModes() as $mode) {
            $form->addButton(new Button($mode->getName(), new ButtonIcon("textures/ui/icon_recipe_equipment"), fn(Player $player) => self::addPlayer($player, $mode)));
        }
        $player->sendForm($form);
    }
}