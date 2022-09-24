<?php

namespace Nyrok\QuazarCore\tasks;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\objects\Duel;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\scheduler\Task;
use pocketmine\Server;

final class DuelAcceptTask extends Task
{
    private int $count = 3;
    public function __construct(private string $player1, private string $player2, private Duel $duel)
    {
    }

    public function onRun(int $currentTick): void
    {
        $player1 = Server::getInstance()->getPlayerExact($this->player1);
        $player2 = Server::getInstance()->getPlayerExact($this->player2);
        if($this->count === 0){
            $player1->setImmobile(false);
            $player2->setImmobile(false);
            Core::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        if(!$player1 or !$player2){
            $this->duel->stop();
            return;
        }

        $message = LanguageProvider::getLanguageMessage("messages.duels.countdown", PlayerProvider::toQuazarPlayer($player1), true);
        $player1->sendMessage(str_replace("{countdown}", $this->count, $message));
        $message = LanguageProvider::getLanguageMessage("messages.duels.countdown", PlayerProvider::toQuazarPlayer($player2), true);
        $player2->sendMessage(str_replace("{countdown}", $this->count, $message));
        $player1->setImmobile(true);
        $player2->setImmobile(true);
    }
}