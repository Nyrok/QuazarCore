<?php

namespace Nyrok\QuazarCore\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent as ClassEvent;

final class PlayerCommandPreprocessEvent implements Listener
{
    const NAME = "PlayerCommandPreprocessEvent";

    /**
     * @param ClassEvent $event
     * @priority LOWEST
     */
    public function onEvent(ClassEvent $event)
    {
        $message = $event->getMessage();
        $msg = explode(' ', trim($message));
        $m = substr("$message", 0, 1);
        $whitespace_check = substr($message, 1, 1);
        $slash_check = substr($msg[0], -1, 1);
        $quote_mark_check = substr($message, 1, 1) . substr($message, -1, 1);

        if ($m == '/') {
            if ($whitespace_check === ' ' or $whitespace_check === '\\' or $slash_check === '\\' or $quote_mark_check === '""') {
                $event->setCancelled();
            }
        }
    }
}