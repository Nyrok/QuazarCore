<?php

namespace Nyrok\QuazarCore\tasks;

use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use Nyrok\QuazarCore\objects\Event;
use Nyrok\QuazarCore\managers\EventsManager;
use Nyrok\QuazarCore\Core;

final class EventsTask extends Task
{
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

            Core::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        } else {

            $startIn = $event->getStartIn() - time();

            // START EVENT
            if($startIn <= 0) {

                EventsManager::startEvent($event);
                return;
            }

            $configCache = Core::getInstance()->getConfig()->getAll();

            if(is_int($startIn / (int)$configCache["events"]["alert-time"]) && $startIn !== 120) {

                foreach (Server::getInstance()->getOnlinePlayers() as $player)
                {
                    if(!EventsManager::getIfPlayerIsInEvent($player)) {

                        $message = LanguageProvider::getLanguageMessage("messages.events.alert", PlayerProvider::toQuazarPlayer($player), true);
                        $message = str_replace("{type}", $event->getType(), $message);
                        $player->sendMessage($message);
                    }
                }
            }

            if(
                $startIn === 60 ||
                $startIn === 30 ||
                $startIn === 15 ||
                $startIn <= 5
            ) {

                $this->broadcastEventMessage($event);

            }

            if($startIn >= 1 && $startIn <= 3) {

                $this->broadcastEventSound($event);
            }
        }
    }
    
    private function broadcastEventMessage(Event $event): void
    {
        foreach($event->getPlayers() as $p)
        {
            $player = Server::getInstance()->getPlayerExact($p);
            $message = LanguageProvider::getLanguageMessage("messages.events.event-starts-in", PlayerProvider::toQuazarPlayer($player), true);
            $message = str_replace(["{time}"], [$event->getStartIn() - time()], $message);
            $player->sendMessage($message);
        }
    }
    
    private function broadcastEventSound(Event $event): void
    {
        foreach($event->getPlayers() as $p)
        {
            $player = Server::getInstance()->getPlayerExact($p);
            $player->getLevel()->broadcastLevelSoundEvent(new Vector3($player->x, $player->y, $player->z), LevelSoundEventPacket::SOUND_LEVELUP);
        }
    }
}