<?php

namespace Nyrok\QuazarCore\objects;

use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\utils\PlayerUtils;
use pocketmine\level\Level;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Position;
use pocketmine\Server;

final class FloatingText
{
    private FloatingTextParticle $particle;

    /**
     * @param string $title
     * @param string $text
     * @param Position $position
     * @param string $type
     */
    public function __construct(private string $title, private string $text, private Position $position, private string $type)
    {
        $this->particle = new FloatingTextParticle($this->getPosition(), $this->getText(), $this->getTitle());
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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return Level
     */
    public function getLevel(): Level
    {
        return $this->position->getLevel();
    }

    /**
     * @return FloatingTextParticle
     */
    public function getParticle(): FloatingTextParticle {
        return $this->particle;
    }


    public function update(): void {
        $this->text = "";
        foreach($leaderboard = PlayerUtils::getLeaderboard($this->getType()) as $name => $value){
            $this->text .= str_replace(["{position}", "{name}", "{value}"], [array_search($name, array_keys($leaderboard)) + 1, $name, $value], LanguageProvider::getLanguageMessage('forms.top.format'));
        }
    }

    /**
     */
    public function spawn(): void {
        self::delete();
        self::update();
        $this->getLevel()->addParticle($this->particle, array_filter(Server::getInstance()->getOnlinePlayers(), function ($player, $_){
            if($player->getLevel()->getName() === $this->getLevel()->getName()) return true;
            return false;
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function delete(): void {
        $this->particle->setInvisible(true);
        $this->getLevel()->addParticle($this->particle, Server::getInstance()->getOnlinePlayers());
        $this->particle = new FloatingTextParticle($this->getPosition(), $this->getText(), $this->getTitle());
    }
}