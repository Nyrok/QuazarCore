<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\librairies\EasyUI\icon\ButtonIcon;
use Nyrok\QuazarCore\managers\DuelsManager;
use Nyrok\QuazarCore\managers\EventsManager;
use Nyrok\QuazarCore\objects\Duel;
use pocketmine\Server;
use Nyrok\QuazarCore\librairies\EasyUI\element\Button;
use Nyrok\QuazarCore\librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\managers\ArenasManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class DuelCommand extends QuazarCommands
{
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
        if (isset($args[0], $args[1])) {
            $type = match ($args[0]) {
                "accept", "a", "confirm", "yes" => true,
                "deny", "d", "cancel", "no" => false,
                default => null
            };

            if ($type === null) {
                $sender->sendMessage($this->getUsage());
                return;
            }
            $target = Server::getInstance()->getPlayer($args[1]);
            $duel = DuelsManager::getDuel($target->getName());
            if(!$duel){
                $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.duel-dont-exists", null, true));
                return;
            }
            if ($sender->getName() !== $duel->getOpponent()->getName()) {
                $message = LanguageProvider::getLanguageMessage("messages.errors.duel-not-opponent", PlayerProvider::toQuazarPlayer($sender), true);
                $sender->sendMessage(str_replace("{host}", $duel->getHost()->getName(), $message));
                return;
            }
            $duel->setAccepted($type);
        } else if (isset($args[0])) {
            $target = $sender->getServer()->getPlayer($args[0]);
            if (!$target instanceof Player) {
                $sender->sendMessage(LanguageProvider::getLanguageMessage("messages.errors.player-not-connected", PlayerProvider::toQuazarPlayer($sender), true));
                return;
            }
            $this->duelsForm($sender, $target);
        }
    }

    public function duelsForm(Player $player, Player $target): void
    {
        $form = new SimpleForm("§m§a" . "Duels", "Affronter " . $target->getName() . " pour un duel ?");
        foreach (ArenasManager::getModes() as $mode) {
            $form->addButton(new Button($mode->getName(), new ButtonIcon("textures/ui/icon_recipe_equipment", ButtonIcon::TYPE_PATH), fn(Player $player) => DuelsManager::addDuel(new Duel($player, $target, $mode))));
        }
        $player->sendForm($form);
    }
}