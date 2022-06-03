<?php

namespace Nyrok\QuazarCore\Commands;

use Nyrok\QuazarCore\Provider\LanguageProvider;
use Nyrok\QuazarCore\Provider\PlayerProvider;
use Nyrok\QuazarCore\Utils\PlayerUtils;
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
        if($sender instanceof Player) $sender->sendMessage(PlayerUtils::getStats(PlayerProvider::toQuazarPlayer($sender)));
        else $sender->sendMessage(LanguageProvider::getLanguageMessage('messages.errors.not-a-player', null, true));
    }
}