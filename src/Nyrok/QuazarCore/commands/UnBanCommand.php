<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class UnBanCommand extends QuazarCommands
{
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        if(isset($args[0])) {
            $player = $this->getPlugin()->getServer()->getOfflinePlayer($args[0]);
            if($player){
                $this->getPlugin()->getServer()->getNameBans()->remove($player->getName());
                $sender->sendMessage(str_replace(["{player}"], [$player->getName()], LanguageProvider::getLanguageMessage("messages.success.unban", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true)));
            } else{
                $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.player-not-found", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true));
            }
        }
        else {
            $sender->sendMessage(LanguageProvider::getPrefix().$this->getUsage());
        }
    }
}