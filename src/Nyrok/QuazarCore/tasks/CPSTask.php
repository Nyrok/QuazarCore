<?php

namespace Nyrok\QuazarCore\tasks;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\CPSManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\scheduler\Task;

final class CPSTask extends Task
{
    /**
     * @inheritDoc
     */
    public function onRun(int $currentTick)
    {
        foreach (Core::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $cps = CPSManager::getCps($player);

            if (in_array($player->getName(), CPSManager::$users) and PlayerProvider::toQuazarPlayer($player)->getData()['cps']) {
                $player->sendTip(str_replace("{cps}", $cps, CPSManager::getCPSMessage()));
            }

            if($cps >= CPSManager::getAlertCPS("ig") and $player->getPing() <= CPSManager::getPingMinimum()){
                if($cps >= CPSManager::getAlertCPS("webhook")){
                    CPSManager::addAlert($player, $cps, true);
                    $player->kick(LanguageProvider::getLanguageMessage("messages.cps.kick", PlayerProvider::toQuazarPlayer($player), true), false);
                    foreach (Core::getInstance()->getServer()->getOnlinePlayers() as $p) {
                        $p->sendMessage(str_replace("{player}", $player->getName(), LanguageProvider::getLanguageMessage("messages.cps.broadcast", PlayerProvider::toQuazarPlayer($p), true)));
                    }
                }
                else {
                    CPSManager::addAlert($player, $cps, false);
                }
            }
        }
    }
}