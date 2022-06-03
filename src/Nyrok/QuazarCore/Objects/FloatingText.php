<?php

namespace Nyrok\QuazarCore\Objects;

use AndreasHGK\EasyKits\Kit;
use AndreasHGK\EasyKits\manager\KitManager;
use pocketmine\level\Level;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Position;
use pocketmine\Player;

final class FloatingText
{
    public function __construct(private string $title, private string $text, private Position $position)
    {
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->position;
    }

    /**
     * @return Level
     */
    public function getLevel(): Level
    {
        return $this->position->getLevel();
    }

    public function getParticle(): FloatingTextParticle {
        return new FloatingTextParticle($this->getPosition(), $this->getText(), $this->getTitle());
    }

    public function spawn(Player $player): void {
        if($player->getLevel()->getName() === $this->getLevel()->getName()){
            $this->getLevel()->addParticle($this->getParticle(), [$player]);
        }
    }
}