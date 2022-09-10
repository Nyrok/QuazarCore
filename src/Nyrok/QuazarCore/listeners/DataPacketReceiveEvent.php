<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\CPSManager;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent as ClassEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\scheduler\ClosureTask;

final class DataPacketReceiveEvent implements Listener
{
    const NAME = "DataPacketReceiveEvent";

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
        }
    }
}