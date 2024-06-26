<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\librairies\Voltage\Api\module\ScoreBoard;
use Nyrok\QuazarCore\librairies\Voltage\Api\module\types\ScoreBoardLine;
use Nyrok\QuazarCore\librairies\Voltage\Api\ScoreBoardApi;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\tasks\ScoreBoardTask;
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
                    Core::getInstance()->getLogger()->notice("[SCOREBOARDS] Retrying to load ScoreBoard: $world");
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
                Core::getInstance()->getLogger()->notice("[SCOREBOARDS] ScoreBoard For: $world Loaded");
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
        if(strpos($level->getName(), "-event")) {

            $type = str_replace(["-event", "ndb"], ["", "nodebuff"], $level->getName());
            if(EventsManager::getIfEventTypeUsed($type)) {

                $event = EventsManager::getEventByType($type);
                if($event->getStart()) {

                    return Core::getInstance()->getConfig()->getNested("scoreboards.{$level->getName()}.started.lines", []);
                }else{

                    return Core::getInstance()->getConfig()->getNested("scoreboards.{$level->getName()}.not-started.lines", []);
                }
            }else return [];
        }

        return Core::getInstance()->getConfig()->getNested("scoreboards.{$level->getName()}.lines", []);
    }

    public static function updateScoreboard(ScoreBoard $scoreboard, Level $level): void {
        $scoreboard->removeAllPlayers();
        foreach (Server::getInstance()->getOnlinePlayers() as $player){
            if($player->getLevel()->getName() === $level->getName() and PlayerProvider::toQuazarPlayer($player)->getData()['scoreboard'] ?? true) {
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
                "{ping}", "{opponentPing}", "{playerName}", "{opponentName}", "{world}", "{deaths}", "{kills}", "{killstreak}", "{kdr}", "{playersOnline}", "{maxPlayersOnline}", "{ip}", "{port}", "{combatTime}", "{elo}", "{time}", "{players}", "{fighter1}", "{fighter2}"
            ], [
                $player?->getPing() ?? 0,
                ($opponent?->getPing()) ?? 0,
                $player?->getName(),
                $opponent?->getName() ?? "Personne",
                $player?->getLevel()->getName() ?? "Inconnu",
                $player ? PlayerProvider::toQuazarPlayer($player)->getDeaths() : 0,
                $player ? PlayerProvider::toQuazarPlayer($player)->getKills() : 0,
                $player ? PlayerProvider::toQuazarPlayer($player)->getKillStreak() : 0,
                $player ? PlayerProvider::toQuazarPlayer($player)->getKDR() : 0,
                count(Server::getInstance()->getOnlinePlayers()),
                Server::getInstance()->getMaxPlayers(),
                Server::getInstance()->getIp(),
                Server::getInstance()->getPort(),
                $player ? PlayerProvider::toQuazarPlayer($player)->getCombatTime() : 0,
                $player ? PlayerProvider::toQuazarPlayer($player)->getElo() : EloManager::getDefaultElo(),
                $player ? (EventsManager::getIfPlayerIsInEvent($player) ? EventsManager::getEventByPlayer($player)->getStartIn() - time() : 0) : 0,
                $player ? (EventsManager::getIfPlayerIsInEvent($player) ? count(EventsManager::getEventByPlayer($player)->getPlayers()) : 0) : 0,
                $player ? (EventsManager::getIfPlayerIsInEvent($player) ? (!empty(EventsManager::getEventByPlayer($player)->getFighters()) ? (EventsManager::getEventByPlayer($player)->getFighters()[0]) : "Personne") : "Personne") : "Personne",
                $player ? (EventsManager::getIfPlayerIsInEvent($player) ? (!empty(EventsManager::getEventByPlayer($player)->getFighters()) ? (EventsManager::getEventByPlayer($player)->getFighters()[1]) : "Personne") : "Personne") : "Personne",
            ], $line);
            $scoreboard->setLineToAll(new ScoreBoardLine($i + 1, $line));
        }
    }

    public static function updateTitle(ScoreBoard $scoreboard, Level $level): void {
        $scoreboard->setDisplayName(self::getTitle($level));
    }
}