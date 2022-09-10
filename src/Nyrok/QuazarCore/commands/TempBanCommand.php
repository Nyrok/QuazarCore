<?php

namespace Nyrok\QuazarCore\commands;

use DateTime;
use DateTimeZone;
use Nyrok\QuazarCore\managers\SanctionsManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class TempBanCommand extends QuazarCommands
{
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        if(isset($args[0])) {
            $player = $this->getPlugin()->getServer()->getPlayer($args[0]) ?? $this->getPlugin()->getServer()->getOfflinePlayer($args[0]);
            $time = isset($args[1]) ? strtotime(str_replace(["S", "M", "H", "D", "W", "Y"], ["seconds", "minutes", "hours","days","weeks","years"], strtoupper($args[1]))) : "forever";
            $reason = isset($args[2]) ? implode(" ", array_slice($args, 2)) : "Aucune raison donnée.";
            if($player){
                $this->getPlugin()->getServer()->getNameBans()->addBan($player->getName(), $reason, ($time === "forever" ? null : new DateTime())?->setTimestamp($time), $sender->getName());
                $sender->sendMessage(str_replace(["{player}", "{reason}", "{type}", "{time}"], [$player->getName(), $reason, ($time === "forever" ? "définitivement" : "temporairement"),
                    (new DateTime())->setTimestamp($time === "forever" ? time() * 2 : $time)->setTimezone(new DateTimeZone("GMT+2"))->format("d/m/Y à H:i")], LanguageProvider::getLanguageMessage("messages.success.ban", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true)));
                SanctionsManager::addSanction($player->getName(), $reason, $sender->getName(), SanctionsManager::TYPE_BAN, $time === "forever" ? time() * 2 : $time);
                if(($target = $this->getPlugin()->getServer()->getPlayer($player->getName()))->isConnected()){
                    $target->kick(str_replace(["{staff}", "{reason}", "{time}"], [$sender->getName(), $reason, (new DateTime())->setTimestamp($time === "forever" ? time() * 2 : $time)->setTimezone(new DateTimeZone("GMT+2"))->format("d/m/Y à H:i")], LanguageProvider::getLanguageMessage("messages.success.banned", PlayerProvider::toQuazarPlayer($target), true)), false);
                }
            } else{
                $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.player-not-found", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true));
            }
        }
        else {
            $sender->sendMessage(LanguageProvider::getPrefix().$this->getUsage());
        }
    }
}