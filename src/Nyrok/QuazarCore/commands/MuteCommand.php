<?php

namespace Nyrok\QuazarCore\commands;

use DateTime;
use DateTimeZone;
use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\MuteManager;
use Nyrok\QuazarCore\managers\SanctionsManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class MuteCommand extends QuazarCommands
{
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        if(isset($args[0], $args[1])) {
            $player = $this->getPlugin()->getServer()->getPlayer($args[0]);
            $time = strtotime(str_replace(["S", "M", "H", "D", "W", "Y"], ["seconds", "minutes", "hours","days","weeks","years"], strtoupper($args[1])));
            $reason = isset($args[2]) ? implode(" ", array_slice($args, 2)) : "Aucune raison donnée.";
            if($player instanceof Player) {
                MuteManager::setMuted($player, true, $time, $reason);
                Core::getInstance()->getMuteList()->set($player->getName(), $time ?: time());
                $player->sendMessage(str_replace(["{staff}", "{reason}", "{time}"], [$sender->getName(), $reason,
                    (new DateTime())->setTimestamp(($time == "forever") ? time() * 2 : $time)->setTimezone(new DateTimeZone("GMT+2"))->format("d/m/Y à H:i")], LanguageProvider::getLanguageMessage("messages.success.muted", PlayerProvider::toQuazarPlayer($player), true)));
                $sender->sendMessage(str_replace(["{player}", "{reason}", "{time}"], [$player->getName(), $reason,
                    (new DateTime())->setTimestamp(($time == "forever") ? time() * 2 : $time)->setTimezone(new DateTimeZone("GMT+2"))->format("d/m/Y à H:i")], LanguageProvider::getLanguageMessage("messages.success.mute", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true)));
                SanctionsManager::addSanction($player->getName(), $reason, $sender->getName(), SanctionsManager::TYPE_MUTE, $time);
            } else {
                $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.player-not-found", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true));
            }
        }
        else {
            $sender->sendMessage(LanguageProvider::getPrefix().$this->getUsage());
        }
    }
}