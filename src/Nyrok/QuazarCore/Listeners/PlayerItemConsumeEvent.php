<?php

namespace Nyrok\QuazarCore\Listeners;

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
            $event->getPlayer()->addEffect(new EffectInstance(
                Effect::getEffect(Core::getInstance()->getConfig()->getNested('actions.golden-apple.effect.id')),
                Core::getInstance()->getConfig()->getNested('actions.golden-apple.effect.duration') * 20,
                Core::getInstance()->getConfig()->getNested('actions.golden-apple.effect.amplifier'),
                Core::getInstance()->getConfig()->getNested('actions.golden-apple.effect.visible')
            ));
        }
    }
}