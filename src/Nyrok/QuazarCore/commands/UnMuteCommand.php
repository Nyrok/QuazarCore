<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\managers\MuteManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class UnMuteCommand extends QuazarCommands
{
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        if(isset($args[0])) {
            $player = $this->getPlugin()->getServer()->getPlayer($args[0]);
            if($player) {
                MuteManager::setMuted($player, false);
                $player->sendMessage(str_replace("{staff}", $sender->getName(), LanguageProvider::getLanguageMessage("messages.success.unmuted", PlayerProvider::toQuazarPlayer($player), true)));
                $sender->sendMessage(str_replace("{player}", $player->getName(), LanguageProvider::getLanguageMessage("messages.success.unmute", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true)));
            } else {
                $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.player-not-found", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true));
            }
        }
        else {
            $sender->sendMessage(LanguageProvider::getPrefix().$this->getUsage());
        }
    }
}