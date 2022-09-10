<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\managers\StaffManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class StaffCommand extends QuazarCommands
{
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        if($sender instanceof Player){
            if(isset($args[0])){
                switch ($args[0]){
                    case "on":
                        StaffManager::turnOn($sender);
                        $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.success.staff-on", PlayerProvider::toQuazarPlayer($sender), true));
                        break;
                    case "off":
                        StaffManager::turnOff($sender);
                        $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.success.staff-off", PlayerProvider::toQuazarPlayer($sender), true));
                        break;
                    default:
                        $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.success.staff-help", PlayerProvider::toQuazarPlayer($sender), true));
                        break;
                }
            }
            else{
                $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.success.staff-help", PlayerProvider::toQuazarPlayer($sender), true));
            }
        }
        else {
            $sender->sendMessage(LanguageProvider::getLanguageMessage('messages.errors.not-a-player', null, true));
        }

    }
}