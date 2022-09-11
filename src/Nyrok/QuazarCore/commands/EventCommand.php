<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\managers\EventsManager;
use Nyrok\QuazarCore\objects\Event;
use pocketmine\Server;
use Nyrok\QuazarCore\librairies\EasyUI\element\Button;
use Nyrok\QuazarCore\librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\managers\ArenasManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class EventCommand extends QuazarCommands
{
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$this->testPermissionSilent($sender)) return;
        if (!$sender instanceof Player) {
            $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.not-a-player", null, true));
            return;
        }
        $this->eventsForm($sender);
    }
    
    /**
     * @param Player $player
     * @return void
     */
    public function eventsForm(Player $player): void
    {
        $title = LanguageProvider::getLanguageMessage("forms.events.1.title", PlayerProvider::toQuazarPlayer($player), false);
        $form = new SimpleForm($title);
        
        $button1 = LanguageProvider::getLanguageMessage("forms.events.1.1", PlayerProvider::toQuazarPlayer($player), false);
        $form->addButton(new Button($button1, null, $this->eventsCreateForm($player)));
        
        $button2 = LanguageProvider::getLanguageMessage("forms.events.1.2", PlayerProvider::toQuazarPlayer($player), false);
        $form->addButton(new Button($button2, null, $this->eventsJoinForm($player)));
        
        $player->sendForm($form);
    }
    
    /**
     * @param Player $player
     * @return void
     */
    public function eventsCreateForm(Player $player): void
    {
        $title = LanguageProvider::getLanguageMessage("forms.events.2.title", PlayerProvider::toQuazarPlayer($player), false));
        $form = new SimpleForm($title);
        $player->sendForm($form);
    }
    
    /**
     * @param Player $player
     * @return void
     */
    public function eventsJoinForm(Player $player): void
    {
        $title = LanguageProvider::getLanguageMessage("forms.events.3.title", PlayerProvider::toQuazarPlayer($player), false);
        $form = new SimpleForm($title);
        
        $joinButton = LanguageProvider::getLanguageMessage("forms.events.3.button-event", PlayerProvider::toQuazarPlayer($player), false));
        
        foreach (EventsManager::getEvents() as $event) {
            $button = str_replace("{host}", $event->getHost(), $joinButton);
            $button = str_replace("{type}", $event->getType(), $button);
            $form->addButton(new Button($button, null, $event->addPlayer($player)));
        }
    }
}