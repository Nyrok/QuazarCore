<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\managers\FloatingTextManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
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
        if(!$this->testPermissionSilent($sender)) return;
        $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.success.discord", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true));
    }
}