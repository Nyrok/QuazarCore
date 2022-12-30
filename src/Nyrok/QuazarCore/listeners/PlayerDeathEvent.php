<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\EventsManager;
use Nyrok\QuazarCore\managers\FloatingTextManager;
use Nyrok\QuazarCore\managers\LobbyManager;
use Nyrok\QuazarCore\managers\LogsManager;
use Nyrok\QuazarCore\managers\OpponentManager;
use Nyrok\QuazarCore\managers\CooldownManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\utils\AntiGlitchPearl;
use Nyrok\QuazarCore\utils\PlayerUtils;
use Nyrok\QuazarCore\managers\EloManager;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent as ClassEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

final class PlayerDeathEvent implements Listener
{
    const NAME = "PlayerDeathEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event)
    {
        // KILL
        $player = $event->getPlayer();
        if (($cause = $event->getPlayer()->getLastDamageCause()) instanceof EntityDamageByEntityEvent and ($killer = $cause->getDamager()) instanceof Player) {
            if (str_starts_with($player->getLevel()->getName(), "duel.")) {
                LobbyManager::load($killer);
                PlayerUtils::teleportToSpawn($killer);
                $player->getServer()->removeLevel($player->getLevel());
            }
            if (EventsManager::getIfPlayerIsInEvent($player) && EventsManager::getIfPlayerIsInEvent($killer)) {

                $tournament = EventsManager::getEventByPlayer($player);
                $players = $tournament->getPlayers();
                foreach ($players as $pName)
                {
                    $p = Server::getInstance()->getPlayerExact($pName);
                    $message = LanguageProvider::getLanguageMessage("messages.events.event-kill", PlayerProvider::toQuazarPlayer($p), true);
                    $message = str_replace(["{killer}", "{death}"], [$killer->getName(), $player->getName()], $message);
                    $p->sendMessage($message);
                }
                EventsManager::teleportPlayerToEvent($killer, $tournament);
                $killer->removeAllEffects();
                $killer->getInventory()->clearAll();
                $killer->getArmorInventory()->clearAll();
                $killer->setHealth(20);
            }else {

                $elo = EloManager::calculateElo(PlayerProvider::toQuazarPlayer($event->getPlayer())->getElo(), PlayerProvider::toQuazarPlayer($killer)->getElo());
                PlayerProvider::toQuazarPlayer($event->getPlayer())->setData("elo", -$elo, true)->updateRank();
                PlayerProvider::toQuazarPlayer($killer)->setData("elo", $elo, true)->updateRank();
                $event->getPlayer()->sendMessage(str_replace("{elo}", $elo, LanguageProvider::getLanguageMessage("messages.elo.lost", PlayerProvider::toQuazarPlayer($event->getPlayer()), true)));
                $killer->sendMessage(str_replace("{elo}", $elo, LanguageProvider::getLanguageMessage("messages.elo.gain", PlayerProvider::toQuazarPlayer($killer), true)));
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
        }

        // DEATH
        if(EventsManager::getIfPlayerIsInEvent($player)) {

            $tournament = EventsManager::getEventByPlayer($player);
            EventsManager::removePlayer($player);
            $tournament->addSpectator($player->getName());
            EventsManager::startFights($tournament);
        }else {

            PlayerProvider::toQuazarPlayer($player)->setData('deaths', 1, true)->updateKDR();
            PlayerProvider::toQuazarPlayer($player)->setData('killstreak', 0, false);
            OpponentManager::setOpponent($player, null);
            FloatingTextManager::update();
        }

        $event->setDrops([]);
        $event->setXpDropAmount(0);
        $event->setDeathMessage("");
        AntiGlitchPearl::blacklist($player);
        CooldownManager::resetPlayerCooldown($player);
        Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($event): void {
            AntiGlitchPearl::unblacklist($event->getPlayer());
        }), 80);
    }

}