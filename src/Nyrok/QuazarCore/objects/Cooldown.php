<?php

namespace Nyrok\QuazarCore\objects;

use JetBrains\PhpStorm\Pure;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use pocketmine\level\Level;

final class Cooldown
{
    /**
     * @var array
     */
    public array $cooldowns = [];

    /**
     * @param string $name
     * @param int $id
     * @param array $levels
     */
    public function __construct(private string $name, private int $id, private array $levels)
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getLevels(): array
    {
        return $this->levels;
    }

    /**
     * @param Level $level
     * @return int
     */
    #[Pure] public function getCooldown(Level $level): int
    {
        return $this->levels[$level->getName()] ?? 0;
    }

    /**
     * @return Item|null
     */
    public function getItem(): ?Item
    {
        return new Item($this->getId(), 0, ItemFactory::get($this->getId())->getVanillaName()) ?? null;
    }

    /**
     * @param Player $player
     * @return void
     */
    public function resetCooldown(Player $player): void
    {
        $this->cooldowns[$player->getName()] = 0;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function has(Player $player): bool
    {
        return ($this->cooldowns[$player->getName()] ?? 0) > time();
    }

    /**
     * @param Player $player
     */
    public function set(Player $player): void
    {
        $this->cooldowns[$player->getName()] = time() + $this->getCooldown($player->getLevel());
    }

    /**
     * @param Player $player
     * @return int
     */
    #[Pure] public function get(Player $player): int
    {
        return $this->cooldowns[$player->getName()] ?? 0;
    }
}