<?php

namespace Nyrok\QuazarCore\listeners;

use Nyrok\QuazarCore\Core;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemConsumeEvent as ClassEvent;
use pocketmine\item\GoldenApple;

final class PlayerItemConsumeEvent implements Listener
{
    const NAME = "PlayerItemConsumeEvent";

    /**
     * @param ClassEvent $event
     */
    public function onEvent(ClassEvent $event){
        if($event->getItem() instanceof GoldenApple){
            foreach (Core::getInstance()->getConfig()->getNested('actions.golden-apple.effects') as $effect){
                $event->getPlayer()->addEffect(new EffectInstance(
                    Effect::getEffect($effect['id']), $effect['duration'] * 20, $effect['amplifier'], $effect['visible'])
                );
            }
        }
    }
}