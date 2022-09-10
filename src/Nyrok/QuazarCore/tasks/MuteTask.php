<?php

namespace Nyrok\QuazarCore\tasks;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\MuteManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\scheduler\Task;

final class MuteTask extends Task
{
    public function onRun(int $currentTick)
    {
        foreach(Core::getInstance()->getMuteList()->getAll() as $player => $time){
            if($time <= time()){
                Core::getInstance()->getMuteList()->remove($player);
                Core::getInstance()->getMuteList()->save();
                if($player = Core::getInstance()->getServer()->getPlayer($player)){
                    $player->sendMessage(LanguageProvider::getLanguageMessage("messages.success.mute-expired", PlayerProvider::toQuazarPlayer($player), true));
                    MuteManager::setMuted($player, false);
                }
            }
        }
    }
}