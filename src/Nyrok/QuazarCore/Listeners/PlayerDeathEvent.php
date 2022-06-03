<?php
namespace Nyrok\QuazarCore\Listeners;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\Managers\LogsManager;
use Nyrok\QuazarCore\Managers\OpponentManager;
use Nyrok\QuazarCore\Provider\LanguageProvider;
use Nyrok\QuazarCore\Provider\PlayerProvider;
use Nyrok\QuazarCore\Utils\PlayerUtils;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent as ClassEvent;
use pocketmine\Player;

final class PlayerDeathEvent implements Listener
{
    const NAME = "PlayerDeathEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        // KILL
        if(($cause = $event->getPlayer()->getLastDamageCause()) instanceof EntityDamageByEntityEvent and ($killer = $cause->getDamager()) instanceof Player){
            PlayerProvider::toQuazarPlayer($killer)->setData('kills', 1, true)->updateKDR();
            PlayerProvider::toQuazarPlayer($killer)->setData('killstreak', 1, true);
            OpponentManager::setOpponent($killer, null);
            PlayerUtils::rekit($killer);
            LogsManager::sendKillMessage($event->getPlayer(), $killer, LogsManager::TYPE_KILLS);
            if(gettype(PlayerProvider::toQuazarPlayer($killer)->getKillStreak() / Core::getInstance()->getConfig()->getNested("killstreak.rate")) === "integer"){
                foreach (Core::getInstance()->getServer()->getOnlinePlayers() as $player){
                    $player->sendMessage(str_replace(["{player}", "{killstreak}"], [$killer->getName(), PlayerProvider::toQuazarPlayer($killer)->getKillStreak()], LanguageProvider::getLanguageMessage("messages.killstreak", PlayerProvider::toQuazarPlayer($player), true)));                }
            }
        }

        // DEATH
        PlayerProvider::toQuazarPlayer($event->getPlayer())->setData('deaths', 1, true)->updateKDR();
        PlayerProvider::toQuazarPlayer($event->getPlayer())->setData('killstreak', 0, false);
        OpponentManager::setOpponent($event->getPlayer(), null);
        $event->setDrops([]);
        $event->setDeathMessage("");
    }

}