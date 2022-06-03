<?php

namespace Nyrok\QuazarCore\Managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\Librairies\Voltage\Api\module\ScoreBoard;
use Nyrok\QuazarCore\Librairies\Voltage\Api\module\types\ScoreBoardLine;
use Nyrok\QuazarCore\Librairies\Voltage\Api\ScoreBoardApi;
use Nyrok\QuazarCore\Provider\PlayerProvider;
use Nyrok\QuazarCore\Tasks\ScoreBoardTask;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

abstract class ScoreBoardManager
{
    public static array $scoreboards = [];

    public static function initScoreBoard(): void {
        ScoreBoardApi::loadManager();

        $scoreboards = Core::getInstance()->getConfig()->get("scoreboards");
        foreach ($scoreboards as $world => $scoreboard){
            self::$scoreboards[$world] = ["id" => array_search($world, array_keys($scoreboards)) + 1, "scoreboard" => ($manager = ScoreBoardApi::getManager())?->getScoreBoard($manager?->createScoreBoard(array_search($world, array_keys($scoreboards)) + 1))];
            if(!isset(self::$scoreboards[$world])){
                Core::getInstance()->getLogger()->emergency("[SCOREBOARDS] Failed to load ScoreBoard: $world ... Retrying in 5 seconds");
                Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($world): void {
                    Core::getInstance()->getLogger()->alert("[SCOREBOARDS] Retrying to load ScoreBoard: $world");
                    self::initScoreBoard();
                }), 5*20);
            }
            else {
                self::$scoreboards[$world]["scoreboard"]->addPlayers(Server::getInstance()->getOnlinePlayers());
                if(Server::getInstance()->getLevelByName($world)){
                    self::$scoreboards[$world]["scoreboard"]->setDisplayName(self::getTitle(Server::getInstance()->getLevelByName($world)));
                    self::updateLines(self::$scoreboards[$world]["scoreboard"], Server::getInstance()->getLevelByName($world));
                }
                self::$scoreboards[$world]["scoreboard"]->sendToAll();
                Core::getInstance()->getLogger()->alert("[SCOREBOARDS] ScoreBoard For: $world Loaded");
            }
        }

        Core::getInstance()->getScheduler()->scheduleRepeatingTask(new ScoreBoardTask(), Core::getInstance()->getConfig()->get("scoreboard-refresh-time"));
    }

    /**
     * @return array
     */
    public static function getScoreboards(): array
    {
        return self::$scoreboards;
    }

    public static function getTitle(Level $level): string {
        return Core::getInstance()->getConfig()->getNested("scoreboards.{$level->getName()}.title", "QuazarMC");
    }

    public static function getLines(Level $level): array {
        return Core::getInstance()->getConfig()->getNested("scoreboards.{$level->getName()}.lines", []);
    }

    public static function updateScoreboard(ScoreBoard $scoreboard, Level $level): void {
        $scoreboard->removeAllPlayers();
        foreach (Server::getInstance()->getOnlinePlayers() as $player){
            if($player->getLevel()->getName() === $level->getName()) {
                $scoreboard->addPlayer($player);
                self::updateLines($scoreboard, $level, $player);
            }
        }
        self::updateTitle($scoreboard, $level);
        $scoreboard->sendToAll();
    }

    public static function updateLines(ScoreBoard $scoreboard, Level $level, ?Player $player = null): void {
        foreach (array_slice(self::getLines($level), 0, 15) as $i => $line){
            $opponent = $player ? OpponentManager::getOpponent($player) : null;
            $line = str_replace([
                "{ping}", "{opponentPing}", "{playerName}", "{opponentName}", "{world}", "{deaths}", "{kills}", "{killstreak}", "{kdr}", "{playersOnline}", "{maxPlayersOnline}", "{ip}", "{port}", "{combatTime}", "{elo}"
            ], [
                $player?->getPing() ?? 0, ($opponent?->getPing()) ?? 0, $player?->getName(), $opponent?->getName() ?? "Personne", $player?->getLevel()->getName() ?? "Inconnu", $player ? PlayerProvider::toQuazarPlayer($player)->getDeaths() : 0, $player ? PlayerProvider::toQuazarPlayer($player)->getKills() : 0, $player ? PlayerProvider::toQuazarPlayer($player)->getKillStreak() : 0, $player ? PlayerProvider::toQuazarPlayer($player)->getKDR() : 0, count(Server::getInstance()->getOnlinePlayers()), Server::getInstance()->getMaxPlayers(), Server::getInstance()->getIp(), Server::getInstance()->getPort(), $player ? PlayerProvider::toQuazarPlayer($player)->getCombatTime() : 0, $player ? PlayerProvider::toQuazarPlayer($player)->getElo() : EloManager::getDefaultElo()
            ], $line);
            $scoreboard->setLineToAll(new ScoreBoardLine($i + 1, $line));
        }
    }

    public static function updateTitle(ScoreBoard $scoreboard, Level $level): void {
        $scoreboard->setDisplayName(self::getTitle($level));
    }
}