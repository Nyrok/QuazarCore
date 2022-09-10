<?php

namespace Nyrok\QuazarCore\commands;

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
                }
                else {
                    $target->setDisplayName($args[1]);
                }
            }
            else {
                $sender->sendMessage("messages.errors.player-not-connected"); // À faire
            }
        }
        else if(isset($args[0])){
            if($sender instanceof Player){
                $sender->setDisplayName($args[0]);
            }
            else {
                $sender->sendMessage("messages.errors.not-a-player"); // À faire
            }
        }
        else {
            if($sender instanceof Player){
                $sender->setDisplayName($sender->getName());
            }
        }
        // TODO: Stocker le nick dans une db
    }

}