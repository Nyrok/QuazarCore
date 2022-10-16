<?php

namespace Nyrok\QuazarCore\managers;

use JetBrains\PhpStorm\Pure;
use Nyrok\QuazarCore\objects\Rank;
use Nyrok\QuazarCore\Core;

abstract class RanksManager
{
    /**
     * @var Rank[]
     */
    private static array $ranks = [];

    public static function initRanks(): void
    {
        $ranks = Core::getInstance()->getConfig()->get('ranks', []);
        ksort($ranks, SORT_NUMERIC);
        foreach ($ranks as $elo => $rank) {
            if (gettype($elo) === 'integer') {
                self::$ranks[$rank] = new Rank($rank, $elo);
                Core::getInstance()->getLogger()->alert("[RANKS] Rank: $rank ($elo) Loaded");
            } else {
                Core::getInstance()->getLogger()->critical("[RANKS] Rank: $rank ($elo) Not Loaded");
            }
        }
    }

    /**
     * @return Rank[]
     */
    public static function getRanks(): array
    {
        return self::$ranks;
    }

    public static function getDefaultRank(): ?Rank
    {
        return self::getRank(EloManager::getDefaultElo());
    }

    #[Pure] public static function getRank(int $elo): ?Rank
    {
        $current = null;
        foreach (self::getRanks() as $rank) {
            if ($elo >= $rank->getElo()) {
                $current = $rank;
            }
        }
        return $current;
    }


}