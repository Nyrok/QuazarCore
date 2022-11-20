<?php

namespace Nyrok\QuazarCore\tasks;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use Nyrok\QuazarCore\librairies\Voltage\Api\module\ScoreBoard;
use Nyrok\QuazarCore\librairies\Voltage\Api\module\types\ScoreBoardLine;
use Nyrok\QuazarCore\objects\Event;
use Nyrok\QuazarCore\managers\EventsManager;
use Nyrok\QuazarCore\managers\ScoreBoardManager;
use Nyrok\QuazarCore\Core;

final class EventsTask extends Task
{
    private float $progress = 0.0;
    
    /**
     * @param Event $event
     */
    public function __construct(private Event $event)
    {
    }
    
    public function onRun(int $currentTick): void
    {
        $event = $this->event;
        if($event->getStart()) {
            
        } else {
            $startIn = $event->getStartIn() - time();
            
            if($startIn <= 0) {
                EventsManager::startEvent($event);
                return;
            }
            
            switch($startIn)
            {
                case $startIn === 60:
                    $this->broadcastEventMessage($event, "L'évent commence dans une minute");
                    break;
                
                case $startIn >= 1 && $startIn <= 3:
                    $this->broadcastEventSoud($event, LevelSoundEventPacket::SOUND_LEVELUP);
                    break;
            }
            
            $worldN = match($event->getType()) {
                'nodebuff' =>'ndb-event',
                'sumo' => 'sumo-event',
                'soup' => 'soup-event',
                default => 'ndb-event'
            };
            $scoreboard = ScoreBoardManager::getScoreboards()[$worldN]['scoreboard'];
            $scoreboard->setLineToAll(new ScoreBoardLine(3, "Start : $startIn"));
            ScoreBoardManager::updateScoreboard($scoreboard, Server::getInstance()->getLevelByName($worldN));
        }
    }
    
    private function broadcastEventMessage(Event $event, string $message): void
    {
        foreach($event->getPlayers() as $key => $p)
        {
            $player = Server::getInstance()->getPlayerExact($p);
            $player->sendMessage($message);
        }
    }
    
    private function broadcastEventSoud(Event $event, int $sound): void
    {
        foreach($event->getPlayers() as $key => $p)
        {
            $player = Server::getInstance()->getPlayerExact($p);
            $player->getLevel()->broadcastLevelSoundEvent(new Vector3($player->x, $player->y, $player->z), $sound);
        }
    }
}