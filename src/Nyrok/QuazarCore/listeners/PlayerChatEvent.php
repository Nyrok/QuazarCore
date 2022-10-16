<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\managers\MuteManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent as ClassEvent;

final class PlayerChatEvent implements Listener
{
    const NAME = "PlayerChatEvent";

    /**
     * @param ClassEvent $event
     * @priority MONITOR
     * @ignoreCancelled true
     */
    public function onEvent(ClassEvent $event): void
    {
        $player = $event->getPlayer();
        if(MuteManager::isMuted($player)){
            $event->setCancelled(true);
            $player->sendMessage(str_replace(["{player}", "{reason}", "{time}"], [$player->getName(), MuteManager::getMuteReason($player), date("d/m/Y H:i", MuteManager::getMuteDate($player))], LanguageProvider::getLanguageMessage("messages.errors.muted", PlayerProvider::toQuazarPlayer($player), true)));
        }
        $event->setFormat(str_replace("{rank}", PlayerProvider::toQuazarPlayer($event->getPlayer())->getRank(), $event->getFormat()));
        $event->setMessage(str_replace("{rank}", PlayerProvider::toQuazarPlayer($event->getPlayer())->getRank(), $event->getMessage()));
    }
}