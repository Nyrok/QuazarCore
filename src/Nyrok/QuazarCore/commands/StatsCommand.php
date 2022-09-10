<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\utils\PlayerUtils;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class StatsCommand extends QuazarCommands
{
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        if($sender instanceof Player) {
            $sender->sendMessage(PlayerUtils::getStats(PlayerProvider::toQuazarPlayer($sender)));
        }
        else {
            $sender->sendMessage(LanguageProvider::getLanguageMessage('messages.errors.not-a-player', null, true));
        }
    }
}