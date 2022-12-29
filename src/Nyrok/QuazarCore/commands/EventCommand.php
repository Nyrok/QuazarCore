<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\managers\EventsManager;
use Nyrok\QuazarCore\objects\Event;
use Nyrok\QuazarCore\librairies\EasyUI\element\{Button, Dropdown, Option};
use Nyrok\QuazarCore\librairies\EasyUI\variant\{SimpleForm, CustomForm};
use Nyrok\QuazarCore\librairies\EasyUI\utils\FormResponse;
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
        if(EventsManager::getIfPlayerIsInEvent($sender)) {
            $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.events.unauthorized-command", PlayerProvider::toQuazarPlayer($sender), true));
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
        $form->addButton(new Button($button1, null, function (Player $player) {
            $this->eventsCreateForm($player);
        }));

        $button2 = LanguageProvider::getLanguageMessage("forms.events.1.2", PlayerProvider::toQuazarPlayer($player), false);
        $form->addButton(new Button($button2, null, function (Player $player) {
            $this->eventsJoinForm($player);
        }));

        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function eventsCreateForm(Player $player): void
    {
        $title = LanguageProvider::getLanguageMessage("forms.events.2.title", PlayerProvider::toQuazarPlayer($player), false);
        $form = new CustomForm($title);

        $dropdownTitle = LanguageProvider::getLanguageMessage("forms.events.2.1.text", PlayerProvider::toQuazarPlayer($player), false);
        $dropdown = new Dropdown($dropdownTitle);

        $dropdown1 = LanguageProvider::getLanguageMessage("forms.events.2.1.types.ndb", PlayerProvider::toQuazarPlayer($player), false);
        $dropdown->addOption(new Option(0, $dropdown1));

        $dropdown2 = LanguageProvider::getLanguageMessage("forms.events.2.1.types.soup", PlayerProvider::toQuazarPlayer($player), false);
        $dropdown->addOption(new Option(1, $dropdown2));

        $dropdown3 = LanguageProvider::getLanguageMessage("forms.events.2.1.types.sumo", PlayerProvider::toQuazarPlayer($player), false);
        $dropdown->addOption(new Option(2, $dropdown3));

        $dropdown->setDefaultIndex(0);

        $form->addElement("type", $dropdown);

        $form->setSubmitListener(function (Player $player, FormResponse $formResponse) use ($dropdown1, $dropdown2, $dropdown3): void {
            if(EventsManager::getIfPlayerIsInEvent($player)) {
                $message = LanguageProvider::getLanguageMessage("messages.events.already-in-event", PlayerProvider::toQuazarPlayer($player), true);
                $player->sendMessage($message);
                return;
            }
            
            $typeFR = $formResponse->getDropdownSubmittedOptionId('type');
            $type = "";
            
            if($typeFR == 0) $type = "nodebuff";
            if($typeFR == 1) $type = "soup";
            if($typeFR == 2) $type = "sumo";
            
            if(!EventsManager::getIfEventTypeUsed($type))
            {
                $event = new Event($player->getName(), $type);
                EventsManager::addEvent($event);
                EventsManager::teleportPlayerToEvent($player, $event);
                $player->removeAllEffects();
                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();
                $message = LanguageProvider::getLanguageMessage("messages.events.event-create", PlayerProvider::toQuazarPlayer($player), true);
                $player->sendMessage($message);
            } else {
                $message = LanguageProvider::getLanguageMessage("messages.events.type-already-used", PlayerProvider::toQuazarPlayer($player), true);
                $player->sendMessage($message);
            }
        });
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

        $joinButton = LanguageProvider::getLanguageMessage("forms.events.3.button-event", PlayerProvider::toQuazarPlayer($player), false);

        foreach (EventsManager::getEvents() as $event) {
            $button = str_replace("{host}", $event->getName(), $joinButton);
            $button = str_replace("{type}", $event->getType(), $button);
            $form->addButton(new Button($button, null, function (Player $player) use ($event){
                if(!EventsManager::getIfPlayerIsInEvent($player)) {
                    EventsManager::addPlayerToEvent($player, $event, true, true);
                } else {
                    $message = LanguageProvider::getLanguageMessage("messages.events.already-in-event", PlayerProvider::toQuazarPlayer($player), true);
                    $player->sendMessage($message);
                }
            }));
        }

        $returnButton = LanguageProvider::getLanguageMessage("forms.events.3.button-return", PlayerProvider::toQuazarPlayer($player), false);
        $form->addButton(new Button($returnButton, null, function (Player $player){
            $this->eventsForm($player);
        }));

        $player->sendForm($form);
    }
}