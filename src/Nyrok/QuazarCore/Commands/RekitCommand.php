<?php

namespace Nyrok\QuazarCore\Commands;

use Nyrok\QuazarCore\Utils\PlayerUtils;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class RekitCommand extends QuazarCommands
{
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if($sender instanceof Player){
            PlayerUtils::rekit($sender);
        }
    }
}