<?php

namespace Nyrok\QuazarCore\tasks;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\objects\Duel;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class DuelAcceptTask extends Task
{
    private int $count = 3;
    public function __construct(private string $player1, private string $player2, private Duel $duel)
    {
    }

    public function onRun(int $currentTick): void
    {
        if($this->count === 0){
            Core::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        $player1 = Server::getInstance()->getPlayerExact($this->player1);
        $player2 = Server::getInstance()->getPlayerExact($this->player2);
        if(!$player1 or !$player2){
            $this->duel->stop();
            return;
        }

        $message = LanguageProvider::getLanguageMessage("messages.duels.decount", PlayerProvider::toQuazarPlayer($player1), true);
        $player1->sendMessage(str_replace("{count}", $this->count, $message));
        $message = LanguageProvider::getLanguageMessage("messages.duels.decount", PlayerProvider::toQuazarPlayer($player2), true);
        $player2->sendMessage(str_replace("{count}", $this->count, $message));
    }
}