<?php

namespace Nyrok\QuazarCore\Provider;

use jacknoordhuis\combatlogger\CombatLogger;
use JetBrains\PhpStorm\Pure;
use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\Managers\EloManager;
use pocketmine\Player;

final class PlayerProvider
{
    public const TYPE_INT = "int";
    public const TYPE_STRING = "string";

    /**
     * @param Player $player
     * @return PlayerProvider|null
     */
    #[Pure] public static function toQuazarPlayer(Player $player): ?self
    {
        return clone new self($player);
    }

    /**
     * @param Player $player
     */
    public function __construct(private Player $player)
    {
    }

    /**
     * @return bool
     */
    public function isInitPlayer(): bool
    {
        return (bool)Core::getInstance()->getData()->get($this->player?->getUniqueId()?->toString(), false);
    }

    public function initPlayer(): void
    {
        Core::getInstance()->getData()->set($this->player?->getUniqueId()?->toString(), [
            "name" => strtolower($this->player->getName()),
            "language" => LanguageProvider::DEFAULT,
            "kills" => 0,
            "deaths" => 0,
            "k/d" => 0,
            "elo" => EloManager::getDefaultElo(),
            "killstreak" => 0,
        ]);
        Core::getInstance()->getData()->save();
    }

    /**
     * @return array|null
     */
    public function getData(): ?array {
        return Core::getInstance()->getData()->get($this->player?->getUniqueId()?->toString(), null);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param bool $add
     * @return PlayerProvider
     */
    public function setData(string $key, mixed $value, bool $add = false, mixed $type = self::TYPE_INT): self {
        match ($type){
            self::TYPE_INT => Core::getInstance()->getData()->setNested($this->player?->getUniqueId()?->toString().".$key", ($add ? ($this->getData()[$key] ?? 0) : 0) + $value),
            self::TYPE_STRING => Core::getInstance()->getData()->setNested($this->player?->getUniqueId()?->toString().".$key", ($add ? ($this->getData()[$key] ?? "") : "") . $value),
        };
        Core::getInstance()->getData()->save();
        return $this;
    }

    public function updateKDR(): self {
        $this->setData("k/d", round(($this->getData()['kills'] ?: 0) / ($this->getData()['deaths'] ?: 1), 2));
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage(): string {
        return $this->getData()['language'] ?? LanguageProvider::DEFAULT;
    }

    public function getKills(): int {
        return $this->getData()['kills'] ?? 0;
    }

    public function getDeaths(): int {
        return $this->getData()['deaths'] ?? 0;
    }

    public function getKDR(): float {
        return $this->getData()['k/d'] ?? 0;
    }

    public function getElo(): int {
        return $this->getData()['elo'] ?? EloManager::getDefaultElo();
    }

    public function getKillStreak(): int {
        return $this->getData()['killstreak'] ?? 0;
    }

    public function getCombatTime(): int {
        return CombatLogger::getInstance()->getTagDuration($this->player) ?? 0;
    }

}