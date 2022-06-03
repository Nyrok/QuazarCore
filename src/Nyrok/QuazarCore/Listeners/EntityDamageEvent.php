<?php

namespace Nyrok\QuazarCore\Listeners;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent as ClassEvent;

final class EntityDamageEvent implements Listener
{
    const NAME = "EntityDamageEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        match ($event->getCause() ?? 0){
            ClassEvent::CAUSE_DROWNING, ClassEvent::CAUSE_VOID, ClassEvent::CAUSE_FALL => $event->cancel(),
            default => null
        };
    }
}