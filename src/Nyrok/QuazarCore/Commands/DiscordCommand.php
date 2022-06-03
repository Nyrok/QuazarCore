<?php

namespace Nyrok\QuazarCore\Commands;

use Nyrok\QuazarCore\Provider\LanguageProvider;
use Nyrok\QuazarCore\Provider\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class DiscordCommand extends QuazarCommands
{
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.success.discord", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true));
    }
}