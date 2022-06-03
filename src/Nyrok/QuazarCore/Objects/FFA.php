<?php
namespace Nyrok\QuazarCore\Objects;

use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\KitManager;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;

final class FFA
{
    public function __construct(private string $name, private Level $level, private string $kit, private array $x, private int $y, private array $z)
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
     * @return Level
     */
    public function getLevel(): Level
    {
        return $this->level;
    }

    /**
     * @return Kit|null
     */
    public function getKit(): ?Kit
    {
        return KitManager::get($this->kit);
    }

    /**
     * @return array
     */
    public function getX(): array
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY(): int
    {
        return $this->y;
    }

    /**
     * @return array
     */
    public function getZ(): array
    {
        return $this->z;
    }

    /**
     * @return int
     */
    public function getRandomX(): int {
        return mt_rand($this->getX()[0], $this->getX()[1]);
    }

    /**
     * @return int
     */
    public function getRandomZ(): int {
        return mt_rand($this->getZ()[0], $this->getZ()[1]);
    }

    public function start(Player $player): void {
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->teleport(new Position($this->getRandomX(), $this->getY(), $this->getRandomZ(), $this->getLevel()));
        $player->setGamemode(Player::ADVENTURE);
        $this->getKit()?->claimFor($player);
    }
}