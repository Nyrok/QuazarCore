<?php

namespace Nyrok\QuazarCore\librairies\Voltage\Api\module;

use JetBrains\PhpStorm\Pure;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;
use Nyrok\QuazarCore\librairies\Voltage\Api\module\types\ScoreBoardLine;

final class ScoreBoard
{
    public const SORT_ASCENDING = 0;
    public const SLOT_SIDEBAR = "sidebar";
    private const CRITERIA = "dummy";
    public static int $count = 0;

    /** @var Player[] */
    private array $players = [];

    private array $objectives = [];
    private array $removes = [];

    private array $entries = [];

    private string $objectiveName;
    private string $displayName;
    private string $displaySlot;
    private int $sortOrder;

    private SetDisplayObjectivePacket $display;

    public function __construct(string $displayName = "",?string $objectiveName = null, ?string $displaySlot = null, ?int $sortOrder = null, ?int $slotOrder = null, ?array $players = null, bool $send = false)
    {
        $this->displayName = $displayName;
        if (is_null($objectiveName)) {
            $objectiveName = (string)self::$count++;
        }
        $this->objectiveName = $objectiveName;
        if (is_null($displaySlot)) {
            $displaySlot = self::SLOT_SIDEBAR;
        }
        $this->displaySlot = $displaySlot;
        if (is_null($sortOrder)) {
            $sortOrder = self::SORT_ASCENDING;
        }
        $this->sortOrder = $sortOrder;
        if (is_null($slotOrder)) {
            $slotOrder = SetDisplayObjectivePacket::SORT_ORDER_ASCENDING;
        }

        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = $displaySlot;
        $pk->objectiveName = $objectiveName;
        $pk->displayName = $displayName;
        $pk->criteriaName = self::CRITERIA;
        $pk->sortOrder = $slotOrder;
        $this->display = $pk;
        if (!is_null($players)) {
            $this->addPlayers($players);
        }
        if ($send) {
            $this->sendToAll();
        }
    }

    /**
     * @return Player[]
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    #[Pure] public function hasPlayer(Player $player) : bool {
        return isset($this->players[$player->getId()]);
    }

    /**
     * @param Player[] $players
     * @return self
     */
    public function addPlayers(array $players) : self
    {
        foreach ($players as $player) {
            $this->addPlayer($player);
        }
        return $this;
    }

    public function addPlayer(Player $player): self
    {
        if ($this->hasPlayer($player)) {
            return $this;
        }
        $this->showTo([$player]);
        $this->players[$player->getId()] = $player;
        $this->sendToPlayers([$player]);
        return $this;
    }

    public function removePlayer(Player $player): self
    {
        if (!$this->hasPlayer($player)) {
            return $this;
        }
        $this->hideFrom([$player]);
        unset($this->players[$player->getId()]);
        $this->sendToPlayers([$player]);
        return $this;
    }

    public function removePlayers(array $players): self
    {
        foreach ($players as $player) {
            $this->removePlayer($player);
        }
        return $this;
    }

    public function removeAllPlayers(): self
    {
        $this->removePlayers($this->getPlayers());
        return $this;
    }

    public function setObjectiveName(string $name) : self
    {
        $this->objectiveName = $name;
        $this->display->objectiveName = $name;
        return $this;
    }

    public function getObjectiveName() : string
    {
        return $this->objectiveName;
    }

    public function setDisplayName(string $name) : self
    {
        $this->displayName = $name;
        $this->display->displayName = $name;
        return $this;
    }

    public function getDisplayName() : string
    {
        return $this->displayName;
    }

    public function setDisplaySlot(string $name) : self
    {
        $this->displaySlot = $name;
        $this->display->displaySlot = $name;
        return $this;
    }

    public function getDisplaySlot() : string
    {
        return $this->displaySlot;
    }

    public function setSortOrder(int $number) : self
    {
        $this->sortOrder = $number;
        $this->display->sortOrder = $number;
        return $this;
    }

    public function getSortOrder() : string
    {
        return $this->sortOrder;
    }

    public function showTo(array $players) : self
    {
        foreach ($players as $player) {
            $this->objectives[] = $player->getId();
        }
        return $this;
    }

    public function showToAll(): self
    {
        $this->showTo($this->getPlayers());
        return $this;
    }

    public function hideFrom(array $players): self
    {
        foreach ($players as $player) {
            $this->removes[] = $player->getId();
        }
        return $this;
    }

    public function hideFromAll(): self
    {
        $this->hideFrom($this->getPlayers());
        return $this;
    }

    public function updateTo(array $players) : self
    {
        $this->hideFrom($players);
        $this->showTo($players);
        return $this;
    }

    public function updateToAll(): self
    {
        $this->updateTo($this->getPlayers());
        return $this;
    }

    public function setLineToPlayers(array $players, ScoreBoardLine $line) : self {
        $line->setObjectiveName($this->getObjectiveName());
        $this->addEntryPacket($players,SetScorePacket::TYPE_CHANGE,$line->getPacketEntry());
        return $this;
    }

    public function setLineToAll(ScoreBoardLine $line): self {
        $this->setLineToPlayers($this->getPlayers(), $line);
        return $this;
    }

    public function removeLineToPlayers(array $players, ScoreBoardLine $line) : self {
        //after with juste score
        $line->setObjectiveName($this->getObjectiveName());
        $this->addEntryPacket($players,SetScorePacket::TYPE_REMOVE,$line->getPacketEntry());
        return $this;
    }

    public function removeLineToAll(ScoreBoardLine $line): self {
        $this->removeLineToPlayers($this->getPlayers(), $line);
        return $this;
    }

    /**
     * @param Player[] $players
     */
    public function sendToPlayers(array $players) : void {
        foreach ($players as $player) {
            if (in_array($player->getId(),$this->removes)) {
                $pk = new RemoveObjectivePacket();
                $pk->objectiveName = $this->getObjectiveName();
                $player->sendDataPacket($pk);
                unset($this->removes[array_search($player->getId(),$this->removes)]);
                unset($this->entries[$player->getId()]);
            } else {
                if (in_array($player->getId(),$this->objectives)) {
                    $player->sendDataPacket($this->display);
                    unset($this->objectives[array_search($player->getId(),$this->objectives)]);
                }

                $entries = $this->getEntriesPacket($player, SetScorePacket::TYPE_CHANGE);
                if (!is_null($entries)) {
                    $pk = new SetScorePacket();
                    $pk->type = SetScorePacket::TYPE_CHANGE;
                    $pk->entries = $entries;
                    $player->sendDataPacket($pk);
                }

                $entries = $this->getEntriesPacket($player, SetScorePacket::TYPE_REMOVE);
                if (!is_null($entries)) {
                    $pk = new SetScorePacket();
                    $pk->type = SetScorePacket::TYPE_REMOVE;
                    $pk->entries = $entries;
                    $player->sendDataPacket($pk);
                }
            }
        }
    }

    private function addEntryPacket(array $players, int $type, ?ScorePacketEntry $pk = null) {
        if (is_null($pk)) {
            return;
        }
        foreach ($players as $player) {
            if (!isset($this->entries[$player->getId()])) {
                $this->entries[$player->getId()] = [];
                $this->entries[$player->getId()][SetScorePacket::TYPE_REMOVE] = [];
                $this->entries[$player->getId()][SetScorePacket::TYPE_CHANGE] = [];
            }
            $this->entries[$player->getId()][$type][] = $pk;
        }
    }

    private function getEntriesPacket(Player $player, int $type) : ?array {
        if (isset($this->entries[$player->getId()][$type])) {
            $data = $this->entries[$player->getId()][$type];
            unset($this->entries[$player->getId()]);
            return $data;
        }
        return null;
    }

    public function sendToAll() : void {
        $this->sendToPlayers($this->getPlayers());
    }

    public function __toString(): string
    {
        return "";
    }

}