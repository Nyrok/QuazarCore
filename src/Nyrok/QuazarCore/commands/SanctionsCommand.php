<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\managers\SanctionsManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\IPlayer;
use pocketmine\Player;

final class SanctionsCommand extends QuazarCommands
{
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        if($sender instanceof Player){
            if(!isset($args[0])){
                goto usage;
            }
            else if(isset($args[0], $args[1], $args[2])) {
                $method = $args[0];
                if($method === "remove"){
                    $player = $this->getPlugin()->getServer()->getPlayer(strtolower($args[1])) ?? $this->getPlugin()->getServer()->getOfflinePlayer(strtolower($args[1]));
                    $id = (int)$args[2] ?? null;
                    if(!$player){
                        $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.player-not-found", PlayerProvider::toQuazarPlayer($sender), true));
                    }
                    else
                        if(!$id){
                            $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.invalid-argument", PlayerProvider::toQuazarPlayer($sender), true));
                        }
                        else {
                            $this->getPlugin()->getSanctions()->removeNested(strtolower($player->getName()).".$id");
                            $this->getPlugin()->getSanctions()->save();
                            $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.success.sanction-removed", PlayerProvider::toQuazarPlayer($sender), true));
                        }
                }
            }
            else if(isset($args[0], $args[1])) {
                $method = $args[0];
                if ($method === "staff") {
                    $staff = $this->getPlugin()->getServer()->getPlayer(strtolower($args[1])) ?? $this->getPlugin()->getServer()->getOfflinePlayer(strtolower($args[1]));
                    if (!$staff) {
                        $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.player-not-found", PlayerProvider::toQuazarPlayer($sender), true));
                    }
                    SanctionsManager::StaffSanctions($sender, strtolower($staff->getName()));

                } else {
                    usage:
                    $sender->sendMessage(LanguageProvider::getPrefix().$this->getUsage());
                }
            }
            else {
                $player = $this->getPlugin()->getServer()->getPlayer($args[0]) ?? $this->getPlugin()->getServer()->getOfflinePlayer($args[0]);
                if(!$player instanceof IPlayer){
                    $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.player-not-found", PlayerProvider::toQuazarPlayer($sender), true));
                }
                SanctionsManager::Sanctions($sender, $player);
            }
        }
    }
}