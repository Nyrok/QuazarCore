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
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$this->testPermissionSilent($sender)) return;
        if (!$sender instanceof Player) {
            $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.not-a-player", null, true));
            return;
        }
        $this->eventsForm($sender);
    }
    
    public function eventsForm(Player $player): void
    {
        $title = LanguageProvider::getLanguageMessage("forms.events.1.title", PlayerProvider::toQuazarPlayer($player), false));
        $form = new SimpleForm($title);
        $form->addButton(new Button());
        $player->sendForm($form);
    }
    
    public function eventsJoinForm(Player $player): void
    {
        $form = new SimpleForm()
        foreach (EventsManager::getEvents() as $event) {
            $form->addButton(new Button($event->getName(), null, ));
        }
        $player->sendForm($form);
    }
}