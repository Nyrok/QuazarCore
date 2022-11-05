<?php

namespace Nyrok\QuazarCore\objects;

use pocketmine\Player;
use pocketmine\item\Item;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\managers\LobbyManager;

final class Event
{
    /**
     * @var Player[]
     */
    private array $players = [];

    /**
     * @var int
     */
    private int $startIn;

    /**
     * @var bool
     */
    private bool $start = false;

    /**
     * @var array
     */
    private array $fought = [];
    
    /**
     * @param string $name
     * @param string $type
     * @param Player $host
     */
    public function __construct(private string $name, private string $type)
    {
        $this->addPlayer($name);
        $this->startIn = time() + 120;
    }
    
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
    
    /**
     * @return array
     */
    public function getPlayers(): array
    {
        return $this->players;
    }
    
    /**
     * @return int
     */
    public function getStartIn(): int
    {
        return $this->startIn;
    }
    
    /**
     * @return bool
     */
    public function getStart(): bool
    {
        return $this->start;
    }
    
    /**
     * @return void
     */
    public function setStart(): void
    {
        $this->start = true;
    }
    
    /**
     * @return array
     */
    public function getFought(): array
    {
        return $this->fought;
    }
    
    /**
     * @param string $pName
     * @return void
     */
    public function setFought(string $pName): void
    {
        array_push($this->fought, $pName);
    }
    
    /**
     * @return void
     */
    public function resetFought(): void
    {
        foreach($this->fought as $pName)
        {
            unset($pName);
        }
    }

    /**
     * @param Player $player
     * @param bool $giveItem
     * @param bool $sendMsg
     * @return void
     */
    public function addPlayer(Player $player, bool $giveItem = false, bool $sendMsg = false): void
    {
        array_push($this->players, $player->getName());
        
        $player->removeAllEffects();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        
        if($giveItem) {
            $item = [4 => Item::get(152)->setCustomName("§4Leave")];
            $player->getInventory()->setContents($item);
        }
        
        if($sendMsg) {
            $message = LanguageProvider::getLanguageMessage("messages.events.event-join", PlayerProvider::toQuazarPlayer($player), true);
            $player->sendMessage($message);
        }
    }
    
    /**
     * @param string $player
     * @return void
     */
    public function removePlayer(string $player): void
    {
        unset($this->players[array_search($player, $this->players)]);
        if(empty($this->players)){
            $this->cancel();
        }
    }
    
    public function cancel(): void
    {
        $this->players = [];
    }
}