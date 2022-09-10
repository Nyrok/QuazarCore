<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\FloatingTextManager;
use Nyrok\QuazarCore\managers\LobbyManager;
use Nyrok\QuazarCore\managers\LogsManager;
use Nyrok\QuazarCore\managers\OpponentManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\utils\AntiGlitchPerl;
use Nyrok\QuazarCore\utils\PlayerUtils;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent as ClassEvent;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;

final class PlayerDeathEvent implements Listener
{
    const NAME = "PlayerDeathEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event)
    {
        // KILL
        if (($cause = $event->getPlayer()->getLastDamageCause()) instanceof EntityDamageByEntityEvent and ($killer = $cause->getDamager()) instanceof Player) {
            $player = $event->getPlayer();
            if (str_starts_with($player->getLevel()->getName(), "duel.")) {
                LobbyManager::load($killer);
                PlayerUtils::teleportToSpawn($killer);
                $player->getServer()->removeLevel($player->getLevel());
            }
            PlayerProvider::toQuazarPlayer($killer)->setData('kills', 1, true)->updateKDR();
            PlayerProvider::toQuazarPlayer($killer)->setData('killstreak', 1, true);
            OpponentManager::setOpponent($killer, null);
            LogsManager::sendKillMessage($player, $killer, LogsManager::TYPE_KILLS);
            PlayerUtils::rekit($killer);
            if (gettype(PlayerProvider::toQuazarPlayer($killer)->getKillStreak() / Core::getInstance()->getConfig()->getNested("killstreak.rate")) === "integer") {
                foreach (Core::getInstance()->getServer()->getOnlinePlayers() as $player) {
                    $player->sendMessage(str_replace(["{player}", "{killstreak}"], [$killer->getName(), PlayerProvider::toQuazarPlayer($killer)->getKillStreak()], LanguageProvider::getLanguageMessage("messages.killstreak", PlayerProvider::toQuazarPlayer($player), true)));
                }
            }
        }

        // DEATH
        PlayerProvider::toQuazarPlayer($player)->setData('deaths', 1, true)->updateKDR();
        PlayerProvider::toQuazarPlayer($player)->setData('killstreak', 0, false);
        OpponentManager::setOpponent($player, null);
        $event->setDrops([]);
        $event->setDeathMessage("");
        FloatingTextManager::update();
        AntiGlitchPerl::blacklist($player);
        Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($event): void {
            AntiGlitchPerl::unblacklist($event->getPlayer());
        }), 80);
    }

}