<?php

namespace Nyrok\QuazarCore\providers;

use jacknoordhuis\combatlogger\CombatLogger;
use JetBrains\PhpStorm\Pure;
use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\managers\EloManager;
use pocketmine\Player;
use Nyrok\QuazarCore\managers\RanksManager;
use Nyrok\QuazarCore\objects\Rank;

final class PlayerProvider
{
    public const TYPE_INT = "int";
    public const TYPE_STRING = "string";
    public const TYPE_BOOL = "bool";
    public const TYPE_ARRAY = "array";

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
    public function __construct(public Player $player)
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
            "cps" => true,
            "scoreboard" => true
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
     * @param mixed $type
     * @return PlayerProvider
     */
    public function setData(string $key, mixed $value, bool $add = false, mixed $type = self::TYPE_INT): self {
        match ($type){
            self::TYPE_INT => Core::getInstance()->getData()->setNested($this->player?->getUniqueId()?->toString().".$key", ($add ? ($this->getData()[$key] ?? 0) : 0) + $value),
            self::TYPE_STRING => Core::getInstance()->getData()->setNested($this->player?->getUniqueId()?->toString().".$key", ($add ? ($this->getData()[$key] ?? "") : "") . $value),
            self::TYPE_BOOL => Core::getInstance()->getData()->setNested($this->player?->getUniqueId()?->toString().".$key", $value),
            self::TYPE_ARRAY => Core::getInstance()->getData()->setNested($this->player?->getUniqueId()?->toString().".$key", ($add ? ($this->getData()[$key] ?? []) : []) + $value),
        };
        Core::getInstance()->getData()->save();
        return $this;
    }

    /**
     * @return $this
     */
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

    /**
     * @return int
     */
    public function getKills(): int {
        return $this->getData()['kills'] ?? 0;
    }

    /**
     * @return int
     */
    public function getDeaths(): int {
        return $this->getData()['deaths'] ?? 0;
    }

    /**
     * @return float
     */
    public function getKDR(): float {
        return $this->getData()['k/d'] ?? 0;
    }

    /**
     * @return int
     */
    public function getElo(): int {
        return $this->getData()['elo'] ?? EloManager::getDefaultElo();
    }

    /**
     * @return int
     */
    public function getKillStreak(): int {
        return $this->getData()['killstreak'] ?? 0;
    }

    /**
     * @return int
     */
    public function getCombatTime(): int {
        return CombatLogger::getInstance()->getTagDuration($this->player) ?? 0;
    }

    public function updateRank(): self {
        if($this->getRank() !== RanksManager::getRank($this->getData()['elo'])?->getName() ?? $this->getRank()){
            $this->setData("rank", RanksManager::getRank($this->getData()['elo'])?->getName() ?? $this->getRank(), false, self::TYPE_STRING);
            $this->onRankChange(RanksManager::getRank($this->getData()['elo']));
        }
        return $this;
    }

    public function getRank(): string {
        return $this->getData()['rank'] ?? RanksManager::getDefaultRank()?->getName() ?? "*";
    }

    public function onRankChange(Rank $rank): void {
        $this->player->sendMessage(str_replace(["{rank}", "{elo}"], [$rank->getName(), $rank->getElo()], LanguageProvider::getLanguageMessage("messages.rank-change", $this, true)));
    }

}