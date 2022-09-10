<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class TPSCommand extends QuazarCommands
{
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        $sender->sendMessage(str_replace(
            ["{ticks}", "{tps}", "{tps_average}", "{ticks_usage}", "{ticks_usage_average}"],
            [
                $this->getPlugin()->getServer()->getTick(),
                $this->getPlugin()->getServer()->getTicksPerSecond(),
                $this->getPlugin()->getServer()->getTicksPerSecondAverage(),
                $this->getPlugin()->getServer()->getTickUsage(),
                $this->getPlugin()->getServer()->getTickUsageAverage(),
            ],
        LanguageProvider::getLanguageMessage("messages.success.tps", $sender instanceof Player ? PlayerProvider::toQuazarPlayer($sender) : null, true)));
    }
}