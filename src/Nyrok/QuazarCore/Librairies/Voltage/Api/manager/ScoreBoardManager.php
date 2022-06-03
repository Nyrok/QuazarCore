<?php

namespace Nyrok\QuazarCore\Librairies\Voltage\Api\manager;

use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use Nyrok\QuazarCore\Librairies\Voltage\Api\module\ScoreBoard;

final class ScoreBoardManager
{
    private int $id = 0;
    private array $scoreboards = [];

    public function createScoreBoard(?int $id = null, string $displayName = "",?string $objectiveName = null, string $displaySlot = ScoreBoard::SLOT_SIDEBAR, int $sortOrder = ScoreBoard::SORT_ASCENDING, int $slotOrder = SetDisplayObjectivePacket::SORT_ORDER_ASCENDING,?array $players = null, bool $send = false) : int {
        if (is_null($id)) {
            $id = $this->id++;
        }

        if (!$this->issetScoreBoard($id)) {
            $this->scoreboards[$id] = new ScoreBoard($displayName, $objectiveName, $displaySlot, $sortOrder, $slotOrder, $players, $send);
        }
        return $id;
    }

    public function removeScoreBoard(int $id) : void {
        if ($this->issetScoreBoard($id)) {
            unset($this->scoreboards[$id]);
        }
    }

    public function getScoreBoard(int $id) : ?ScoreBoard {
        if ($this->issetScoreBoard($id)) {
            return $this->scoreboards[$id];
        }
        return null;
    }

    public function issetScoreBoard(int $id) : bool {
        return isset($this->scoreboards[$id]);
    }

    /**
     * @return ScoreBoard[]
     */
    public function getAllScoreBoard() : array {
        return $this->scoreboards;
    }

}