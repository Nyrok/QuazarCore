<?php
namespace Nyrok\QuazarCore\Commands;

use Nyrok\QuazarCore\Managers\LobbyManager;
use Nyrok\QuazarCore\Provider\LanguageProvider;
use Nyrok\QuazarCore\Provider\PlayerProvider;
use Nyrok\QuazarCore\Utils\PlayerUtils;
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