<?php

namespace Nyrok\QuazarCore\librairies\Voltage\Api\module\types;

use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

class ScoreBoardLine
{
    private int $score;
    private string $message;
    private ?int $scoreid;
    private int $type;
    private ?string $objectivename = null;

    public function __construct(int $score, string $message = "", ?int $scoreid = null, int $type = ScorePacketEntry::TYPE_FAKE_PLAYER)
    {
        $this->score = $score;
        if (is_null($scoreid)) {
            $this->scoreid = $score;
        }
        if($score > 15 or $score < 1){
        }
        $this->message = $message;
        $this->type = $type;
    }
    
    public function setObjectiveName(string $name) : void {
        $this->objectivename = $name;
    }

    public function getObjectiveName() : string {
        return $this->objectivename;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getMessage() : string {
        return $this->message;
    }

    public function setScore(int $score) : void {
        $this->score = $score;
    }

    public function getScore() : int {
        return $this->score;
    }

    public function setScoreId(int $score) : void {
        $this->scoreid = $score;
    }

    public function getScoreId() : int {
        return $this->scoreid;
    }

    public function setType(int $type) : void {
        $this->type = $type;
    }

    public function getType() : int {
        return $this->type;
    }
    
    public function getPacketEntry() : ?ScorePacketEntry {
        if (is_null($this->objectivename)) {
            return null;
        }
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $this->objectivename;
        $entry->type = $this->type;
        $entry->customName = $this->message;
        $entry->score = $this->score;
        $entry->scoreboardId = $this->scoreid;
        return $entry;
    }

    public function __toString(): string
    {
        return "";
    }
}