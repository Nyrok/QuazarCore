<?php

namespace Nyrok\QuazarCore\objects;

use Nyrok\QuazarCore\managers\DuelsManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\Player;

final class Duel
{
    public bool $started = false;
    public bool $accepted = false;

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
            $this->opponent->sendMessage(LanguageProvider::getLanguageMessage("messages.success.duel-accepted", PlayerProvider::toQuazarPlayer($this->opponent), true));
            $this->host->sendMessage(LanguageProvider::getLanguageMessage("messages.success.duel-accepted", PlayerProvider::toQuazarPlayer($this->host), true));
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
        if(($level = $arena->generate($this->host))){
            $this->host->sendMessage("Map chargée");

            $this->host->removeAllEffects();
            $this->host->getInventory()->clearAll(true);
            $this->host->getArmorInventory()->clearAll(true);
            $this->mode->getKit()->claimFor($this->host);
            $pos = $arena->getPlayer1();
            $pos->level = $level;
            $this->host->teleport($pos);

            $this->opponent->removeAllEffects();
            $this->opponent->getInventory()->clearAll(true);
            $this->opponent->getArmorInventory()->clearAll(true);
            $this->mode->getKit()->claimFor($this->opponent);
            $pos = $arena->getPlayer2();
            $pos->level = $level;
            $this->opponent->teleport($pos);
        }
        else {
            $this->host->sendMessage("Erreur lors du chargement de la map");
        }
    }

    public function stop(): void {
        DuelsManager::removeDuel($this);
    }
}