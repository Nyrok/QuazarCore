<?php

namespace Nyrok\QuazarCore\objects;

final class Rank
{
    private string $name;
    private int $elo;

    public function __construct($name, $elo)
    {
        $this->name = $name;
        $this->elo = $elo;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getElo(): int
    {
        return $this->elo;
    }
}