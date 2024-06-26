<?php

namespace Nyrok\QuazarCore\objects;

use AndreasHGK\EasyKits\Kit;
use pocketmine\network\mcpe\protocol\types\GameMode;

final class Mode
{
    private array $arenas = [];

    public function __construct(private string $name, private Kit $kit, private int $gameMode)
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function addArena(Arena $arena): void
    {
        $this->arenas[] = $arena;
    }

    public function getArenas(): array
    {
        return $this->arenas;
    }

    public function getRandomArena(): Arena
    {
        return $this->arenas[array_rand($this->arenas)];
    }

    /**
     * @return Kit
     */
    public function getKit(): Kit
    {
        return $this->kit;
    }

    /**
     * @return int
     */
    public function getGameMode(): int
    {
        return $this->gameMode;
    }

}