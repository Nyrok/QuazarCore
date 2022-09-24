<?php

namespace Nyrok\QuazarCore\objects;

use Nyrok\QuazarCore\managers\DuelsManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\tasks\DuelAcceptTask;
use pocketmine\Player;

final class Duel
{
    public bool $started = false;
    public bool $accepted = false;
    public ?Arena $arena = null;

    public function __construct(private Player $host, private Player $opponent, private Mode $mode)
    {
        $this->host->sendMessage(LanguageProvider::getLanguageMessage("messages.success.duel-created", PlayerProvider::toQuazarPlayer($this->host), true));
        $this->opponent->sendMessage(LanguageProvider::getLanguageMessage("messages.success.duel-request", PlayerProvider::toQuazarPlayer($this->opponent), true));
    }

    /**
     * @return Player
     */
    public function getHost(): Player
    {
        return $this->host;
    }

    /**
     * @return Mode
     */
    public function getMode(): Mode
    {
        return $this->mode;
    }

    public function getArena(): ?Arena
    {
        return $this->arena;
    }

    /**
     * @param Player|null $opponent
     */
    public function setOpponent(?Player $opponent): void
    {
        $this->opponent = $opponent;
    }

    /**
     * @return Player|null
     */
    public function getOpponent(): ?Player
    {
        return $this->opponent;
    }

    /**
     * @param bool $accepted
     */
    public function setAccepted(bool $accepted): void
    {
        if(!$accepted){
            $this->stop();
            return;
        }
        if($this->opponent->isOnline() and $this->host->isOnline()){
            $message = LanguageProvider::getLanguageMessage("messages.success.duel-accepted-opponent", PlayerProvider::toQuazarPlayer($this->opponent), true);
            $this->opponent->sendMessage(str_replace("{host}", $this->host->getName(), $message));
            $message = LanguageProvider::getLanguageMessage("messages.success.duel-accepted-host", PlayerProvider::toQuazarPlayer($this->host), true);
            $this->host->sendMessage(str_replace("{opponent}", $this->opponent->getName(), $message));
        }
        else {
            $this->stop();
            return;
        }
        $this->accepted = $accepted;
        $this->start();
    }

    public function start(): void {
        $arena = $this->mode->getRandomArena();
        $this->arena ??= $arena;
        if(!$this->host->isOnline() or !$this->opponent->isOnline()){
            $this->stop();
            return;
        }
        if(($level = $arena->generate($this->host))){
            $this->host->removeAllEffects();
            $this->host->getInventory()->clearAll(true);
            $this->host->getArmorInventory()->clearAll(true);
            $this->mode->getKit()->claimFor($this->host);
            $pos = $arena->getPlayer1();
            $pos->level = $level;
            $this->host->setGamemode($this->mode->getGameMode());
            $this->host->teleport($pos);

            $this->opponent->removeAllEffects();
            $this->opponent->getInventory()->clearAll(true);
            $this->opponent->getArmorInventory()->clearAll(true);
            $this->mode->getKit()->claimFor($this->opponent);
            $pos = $arena->getPlayer2();
            $pos->level = $level;
            $this->opponent->setGamemode($this->mode->getGameMode());
            $this->opponent->teleport($pos);
            /*
            $i = 3;
            $task = new ClosureTask(function (int $currentTick) use ($i): void {
                $msg = LanguageProvider::getLanguageMessage("messages.success.duel-countdown", PlayerProvider::toQuazarPlayer($this->host), true);
                $this->host->sendMessage(str_replace("{countdown}", $i, $msg));
                $msg = LanguageProvider::getLanguageMessage("messages.success.duel-countdown", PlayerProvider::toQuazarPlayer($this->opponent), true);
                $this->opponent->sendMessage(str_replace("{countdown}", $i, $msg));
                $i--;
                if($i === 0){

                }
                else {

                }
            }); */
            Core::getInstance()->getScheduler()->scheduleRepeatingTask(new DuelAcceptTask($this->host, $this->opponent, $this), 20);
        }
        else {
            $this->host->sendMessage("Erreur lors du chargement de la map");
        }
    }

    public function stop(): void {
        DuelsManager::removeDuel($this);
    }
}