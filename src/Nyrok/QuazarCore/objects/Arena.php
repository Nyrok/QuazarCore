<?php

namespace Nyrok\QuazarCore\objects;

use JetBrains\PhpStorm\Pure;
use jkorn\pvpcore\PvPCore;
use jkorn\pvpcore\utils\Utils;
use jkorn\pvpcore\world\PvPCWorld;
use Nyrok\QuazarCore\managers\ArenasManager;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

final class Arena
{
    public function __construct(private array $players, private Level $level)
    {
    }

    /**
     * @return string
     */
    #[Pure] public function getName(): string
    {
        return $this->level->getName();
    }

    /**
     * @return Level
     */
    public function getLevel(): Level
    {
        return $this->level;
    }

    /**
     * @return Position
     */
    public function getPlayer1(): Position
    {
        $pos = reset($this->players);
        return new Position($pos["x"], $pos["y"], $pos["z"], $this->level);
    }

    public function getPlayer2(): Position
    {
        $pos = end($this->players);
        return new Position($pos["x"], $pos["y"], $pos["z"], $this->level);
    }

    public function generate(Player $player): ?Level
    {
        $level = ArenasManager::copyWorld($this->level, "duel.".$this->getName()."-".$player->getName());
        Server::getInstance()->loadLevel($level);
        if(($world = PvPCore::getWorldHandler()->getWorld($this->getName())) instanceof PvPCWorld){
            $kb = clone $world->getKnockback();
            $new = PvPCore::getWorldHandler()->getWorld($this->getName());
            $new->getKnockback()->update(Utils::X_KB, $kb->getXZKb());
            $new->getKnockback()->update(Utils::Y_KB, $kb->getYKb());
            $new->getKnockback()->update(Utils::SPEED_KB, $kb->getSpeed());
        }
        return Server::getInstance()->getLevelByName($level);
    }
}