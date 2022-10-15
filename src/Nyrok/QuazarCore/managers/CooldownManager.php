<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\objects\Cooldown;
use pocketmine\Player;
use pocketmine\Server;

abstract class CooldownManager
{
    /**
     * @var Cooldown[]
     */
    public static array $cooldowns = [];

    public static function initCooldowns(): void {
        $config = Core::getInstance()->getConfig();
        
        foreach ($config->getNested("cooldowns") as $id => $cooldown){
            $levels = [];
            
            foreach(Server::getInstance()->getLevels() as $serverLevel) {
                $levelName = $serverLevel->getName();
                
                if(!isset($cooldown['cooldown'][$levelName])) {
                    $config->setNested("cooldowns.".$id.".cooldown.".$levelName, 0);
                }
                
                $levelCooldown = $config->getNested("cooldowns.".$id.".cooldown.".$levelName);
                $levels[$levelName] = $levelCooldown;
            }
            
            $class = new Cooldown($cooldown['name'], (int)$id, $levels);
            self::$cooldowns[$id] = $class;
            Core::getInstance()->getLogger()->notice("[COOLDOWNS] Cooldown: ({$class->getName()}) ".$class->getItem()->getVanillaName()." seconds Loaded");
        }
    }

    /**
     * @return array
     */
    public static function getCooldowns(): array
    {
        return self::$cooldowns;
    }
    
    /**
     * @param Player $player
     * @return void
     */
    public static function resetPlayerCooldown(Player $player): void
    {
        foreach (self::getCooldowns() as $cooldown){
            $cooldown->resetCooldown($player);
        }
    }

}