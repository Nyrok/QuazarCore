<?php
namespace Nyrok\QuazarCore\Utils;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\Managers\FFAManager;
use Nyrok\QuazarCore\Managers\LobbyManager;
use Nyrok\QuazarCore\Provider\LanguageProvider;
use Nyrok\QuazarCore\Provider\PlayerProvider;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\Player;

abstract class PlayerUtils
{
    const STATS = [
      "Kills", "Deaths", "K/D", "Elo", "KillStreak"
    ];

    public static function teleportToSpawn(Player $player): bool {
        if($player->teleport(LobbyManager::getSpawnPosition())){
            return true;
        }
        return false;
    }

    public static function getStats(PlayerProvider $player, bool $prefix = true): string {
        return str_replace(
            ["{kills}", "{deaths}", "{kdr}", "{elo}", "{killstreak}"],
            [$player->getKills(), $player->getDeaths(), $player->getKDR(), $player->getElo(), $player->getKillStreak()],
            LanguageProvider::getLanguageMessage("messages.stats", $player, $prefix)
        );
    }

    public static function getLeaderboard(string $value): array {
        $top = [];
        foreach(Core::getInstance()->getData()->getAll() as $data){
            $top[$data['name']] = $data[$value];
        }
        arsort($top);
        return array_slice($top, 0, 10);
    }

    public static function rekit(Player $player): void {
        if(array_key_exists($player->getLevel()->getName(), Core::getInstance()->getConfig()->get('ffas'))){
            $ffa = FFAManager::worldToFFA($player->getLevel()->getName());
            $ffa->getKit()->claimFor($player);
        }
    }

    public static function countSoup(Player $player): int {
        $soup = Core::getInstance()->getConfig()->getNested("utils.soup.id", 1337);
        $count = 0;
        foreach ($player->getInventory()->getContents() as $item){
            if($item->getId() === $soup) $count += $item->getCount();
        }
        return $count;
    }

    public static function countPopo(Player $player): int {
        $popo = Item::get(ItemIds::SPLASH_POTION, 22);
        $count = 0;
        foreach ($player->getInventory()->getContents() as $item){
            if($item->getId() === $popo->getId() and $item->getDamage() === $popo->getDamage()) $count += $item->getCount();
        }
        return $count;
    }
}