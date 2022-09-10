<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\managers\SanctionsManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class WarnCommand extends QuazarCommands
{
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        if(isset($args[0])) {
            $player = $this->getPlugin()->getServer()->getPlayer($args[0]) ?? $this->getPlugin()->getServer()->getOfflinePlayer($args[0]);
            $reason = isset($args[1]) ? implode(" ", array_slice($args, 2)) : "Aucune raison donnée.";
            if($player instanceof Player){
                $player->sendTitle("§c[§4WARNING§c]", "Vous avez reçu un avertissement.", 10, 70, 20);
                $player->sendMessage(str_replace(["{staff}", "{reason}"], [$sender->getName(), $reason], LanguageProvider::getLanguageMessage("messages.success.warned", PlayerProvider::toQuazarPlayer($player), true)));
                $sender->sendMessage(str_replace(["{player}", "{reason}"], [$player->getName(), $reason], LanguageProvider::getLanguageMessage("messages.success.warn", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true)));
                SanctionsManager::addSanction($player->getName(), $reason, $sender->getName(), SanctionsManager::TYPE_WARN);
            } else{
                $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.player-not-found", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true));
            }
        }
        else {
            $sender->sendMessage(LanguageProvider::getPrefix().$this->getUsage());
        }
    }

}