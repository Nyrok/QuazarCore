<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\CPSManager;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent as ClassEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\scheduler\ClosureTask;

final class DataPacketReceiveEvent implements Listener
{
    const NAME = "DataPacketReceiveEvent";

    private array $lastTime = [];
    private array $balance = [];

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        switch ($pk = $event->getPacket()){
            case $pk instanceof LevelSoundEventPacket:
                switch ($pk->sound){
                    case $pk::SOUND_ATTACK:
                    case $pk::SOUND_ATTACK_STRONG:
                    case $pk::SOUND_ATTACK_NODAMAGE:
                        CPSManager::addClick($event->getPlayer());
                        $event->setCancelled();
                        break;
                }
                break;
            case $pk instanceof LoginPacket:
                Core::getInstance()->getScheduler()->scheduleTask(new ClosureTask(function (int $currentTick) use ($event, $pk): void {
                    if(!PlayerProvider::toQuazarPlayer($event->getPlayer())->isInitPlayer()) PlayerProvider::toQuazarPlayer($event->getPlayer())->initPlayer();
                    PlayerProvider::toQuazarPlayer($event->getPlayer())->setData("platform", match($pk->clientData['DeviceOS']){
                        1 => "Android",
                        2 => "iOS",
                        3 => "OSX",
                        4 => "FireOS",
                        5 => "GearVR",
                        6 => "HoloLens",
                        7 => "Win10",
                        8 => "Win32",
                        9 => "Dedicated",
                        10 => "Orbis",
                        11 => "NX",
                        default => "Unknown",
                    }, false, PlayerProvider::TYPE_STRING);
                }));
                break;
            case $pk instanceof PlayerAuthInputPacket:
                if (!$event->getPlayer()->isAlive()) {
                    $this->lastTime[$event->getPlayer()->getName()] = null;
                    $this->balance[$event->getPlayer()->getName()] = 0;
                    return;
                }
                $currentTime = microtime(true) * 1000;
                if (isset($this->lastTime[$event->getPlayer()->getName()]) and $this->lastTime[$event->getPlayer()->getName()] !== null) {
                    $this->lastTime[$event->getPlayer()->getName()] = $currentTime;
                    return;
                }
                // convert the time difference into ticks (round this value to detect lower timer values).
                $timeDiff = round(($currentTime - $this->lastTime[$event->getPlayer()->getName()]) / 50, 2);
                // there should be a one tick difference between the two packets
                $this->balance[$event->getPlayer()->getName()] -= 1;
                // add the time difference between the two packet (this should be near one tick - which evens out the subtraction of one)
                $this->balance[$event->getPlayer()->getName()] += $timeDiff;
                // if the balance is too low (the time difference is usually less than one tick)
                if ($this->balance[$event->getPlayer()->getName()] <= -5) {
                    $event->getPlayer()->kick("Anti Timer mother fucker", false);
                    $this->balance[$event->getPlayer()->getName()] = 0;
                }
                $this->lastTime[$event->getPlayer()->getName()] = $currentTime;
        }
    }
}