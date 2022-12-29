<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\managers\EventsManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\utils\PlayerUtils;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class RekitCommand extends QuazarCommands
{
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        if($sender instanceof Player){
            if(EventsManager::getIfPlayerIsInEvent($sender)) {
                $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.events.unauthorized-command", PlayerProvider::toQuazarPlayer($sender), true));
                return;
            }
            PlayerUtils::rekit($sender);
        }
    }
}