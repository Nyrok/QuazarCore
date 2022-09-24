<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\Player;

final class NickCommand extends QuazarCommands
{
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        if(isset($args[0], $args[1]) and $sender->hasPermission(Permission::DEFAULT_OP)){
            $target = $sender->getServer()->getPlayer($args[0]);
            if($target){
                if($args[1] === "off"){
                    $target->setDisplayName($target->getName());
                    PlayerProvider::toQuazarPlayer($target)->setData("nick", "off", false, PlayerProvider::TYPE_STRING);
                }
                else {
                    $target->setDisplayName($args[1]);
                    PlayerProvider::toQuazarPlayer($target)->setData("nick", $args[1], false, PlayerProvider::TYPE_STRING);
                }
            }
            else {
                $sender->sendMessage("messages.errors.player-not-connected"); // À faire
            }
        }
        else if(isset($args[0])){
            if($sender instanceof Player){
                $sender->setDisplayName($args[0]);
                PlayerProvider::toQuazarPlayer($sender)->setData("nick", $args[0], false, PlayerProvider::TYPE_STRING);
            }
            else {
                $sender->sendMessage("messages.errors.not-a-player"); // À faire
            }
        }
        else {
            if($sender instanceof Player){
                $sender->setDisplayName($sender->getName());
                PlayerProvider::toQuazarPlayer($sender)->setData("nick", $sender->getName(), false, PlayerProvider::TYPE_STRING);
            }
        }
        // TODO: Stocker le nick dans une db
    }

}