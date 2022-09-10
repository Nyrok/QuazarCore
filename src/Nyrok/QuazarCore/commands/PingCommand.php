<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class PingCommand extends QuazarCommands
{
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        $target = $sender;
        if(isset($args[0])){
            $target = $sender->getServer()->getPlayer($args[0]);
            if(!$target){
                $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.player-not-connected", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true));
            }
        } else if(!$sender instanceof Player){
            $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.not-a-player", null, true));
            return;
        }
        if($target instanceof Player){
            $sender->sendMessage(str_replace(["{player}", "{ping}"], [$target->getName(), $target->getPing()], LanguageProvider::getLanguageMessage("messages.success.ping", PlayerProvider::toQuazarPlayer($sender), true)));
        }
    }
}