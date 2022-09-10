<?php
namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\managers\LobbyManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\utils\PlayerUtils;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class SpawnCommand extends QuazarCommands
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
        if($sender instanceof Player and PlayerProvider::toQuazarPlayer($sender) instanceof PlayerProvider){
            if(!PlayerUtils::teleportToSpawn($sender)) $sender->sendMessage(LanguageProvider::getLanguageMessage('messages.errors.spawn-not-found', PlayerProvider::toQuazarPlayer($sender), true));
            else {
                LobbyManager::load($sender);
                $sender->sendMessage(LanguageProvider::getLanguageMessage('messages.success.teleport-to-spawn', PlayerProvider::toQuazarPlayer($sender), true));
            }
        }
        else $sender->sendMessage(LanguageProvider::getLanguageMessage('messages.errors.not-a-player', null, true));
    }
}