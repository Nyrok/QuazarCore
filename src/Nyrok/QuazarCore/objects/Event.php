<?php

namespace Nyrok\QuazarCore\objects;

use pocketmine\Player;

final class Event
{
    /**
     * @var string[]
     */
    private array $players = [];

    /**
     * @var int
     */
    private int $startIn;

    /**
     * @var bool
     */
    private bool $start = false;

    /**
     * @var string[]
     */
    private array $fought = [];

    /**
     * @var string[]
     */
    private array $spectators = [];

    /**
     * @var string[]
     */
    private array $fighters = [];

    /**
     * @param string $name
     * @param string $type
     */
    public function __construct(private string $name, private string $type)
    {
        $this->addPlayer($name);
        $this->startIn = time() + 120;
    }
    
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
    
    /**
     * @return array
     */
    public function getPlayers(): array
    {
        return $this->players;
    }
    
    /**
     * @return int
     */
    public function getStartIn(): int
    {
        return $this->startIn;
    }
    
    /**
     * @return bool
     */
    public function getStart(): bool
    {
        return $this->start;
    }
    
    /**
     * @return void
     */
    public function setStart(): void
    {
        $this->start = true;
    }
    
    /**
     * @return array
     */
    public function getFought(): array
    {
        return $this->fought;
    }
    
    /**
     * @param string $pName
     * @return void
     */
    public function addFought(string $pName): void
    {
        $this->fought[] = $pName;
    }
    
    /**
     * @return void
     */
    public function resetFought(): void
    {
        $this->fought = [];
    }

    /**
     * @param string $player
     * @return void
     */
    public function addPlayer(string $player): void
    {
        $this->players[] = $player;
    }
    
    /**
     * @param string $player
     * @return void
     */
    public function removePlayer(string $player): void
    {
        unset($this->players[array_search($player, $this->players)]);
    }

    /**
     * @return array
     */
    public function getSpectators(): array
    {
        return $this->spectators;
    }

    /**
     * @param string $player
     * @return void
     */
    public function addSpectator(string $player): void
    {
        $this->spectators[] = $player;
    }

    /**
     * @param string $player
     * @return void
     */
    public function removeSpectator(string $player): void
    {
        unset($this->spectators[array_search($player, $this->spectators)]);
    }

    /**
     * @return string[]
     */
    public function getFighters(): array
    {
        return $this->fighters;
    }

    /**
     * @param string[] $fighters
     * @return void
     */
    public function setFighters(array $fighters): void
    {
        $this->fighters = $fighters;
    }
}