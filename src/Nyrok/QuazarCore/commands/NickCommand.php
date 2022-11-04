<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\librairies\EasyUI\element\Button;
use Nyrok\QuazarCore\librairies\EasyUI\element\Input;
use Nyrok\QuazarCore\librairies\EasyUI\icon\ButtonIcon;
use Nyrok\QuazarCore\librairies\EasyUI\utils\FormResponse;
use Nyrok\QuazarCore\librairies\EasyUI\variant\CustomForm;
use Nyrok\QuazarCore\librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\Player;

final class NickCommand extends QuazarCommands
{
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$this->testPermissionSilent($sender)) return;
        if (isset($args[0]) and $sender->hasPermission(Permission::DEFAULT_OP)) {
            $target = $sender->getServer()->getPlayer($args[0]);
            if ($target) {
                if (isset($args[1])) {
                    self::setNick($target, $args[1] === "off" ? null : $args[1]);
                } else if ($sender instanceof Player) {
                    $form = new SimpleForm("§m§a" . "§fNick");
                    $form->addButton(new Button("Add", new ButtonIcon("textures/ui/color_plus", ButtonIcon::TYPE_PATH), function (Player $player) use ($target) {
                        $form = new CustomForm("Ajouter un Nick à " . $target->getName());
                        $form->setSubmitListener(function (Player $player, FormResponse $formResponse) use ($target) {
                            self::setNick($target, $formResponse->getInputSubmittedText('nick') ?: null);
                        });
                        $form->addElement("nick", new Input("Nick", "", $this->getRandomNick()));
                        $player->sendForm($form);
                    }));
                    $form->addButton(new Button("Random", new ButtonIcon("textures/ui/random_dice", ButtonIcon::TYPE_PATH), function (Player $player) use ($target) {
                        self::setNick($target, $this->getRandomNick());
                    }));
                    $form->addButton(new Button("Remove", new ButtonIcon("textures/ui/trash", ButtonIcon::TYPE_PATH), function (Player $player) use ($target) {
                        self::setNick($target, null);
                    }));
                    $sender->sendForm($form);
                } else {
                    $sender->sendMessage("messages.errors.not-a-player"); // À faire
                }
            } else {
                $sender->sendMessage("messages.errors.player-not-connected"); // À faire
            }
        } else {
            if ($sender instanceof Player) {
                $form = new SimpleForm("§m§a" . "§fNick");
                $form->addButton(new Button("Add", new ButtonIcon("textures/ui/color_plus", ButtonIcon::TYPE_PATH), function (Player $player) {
                    $form = new CustomForm("Définir votre Nick");
                    $form->setSubmitListener(function (Player $player, FormResponse $formResponse) {
                        self::setNick($player, $formResponse->getInputSubmittedText('nick') ?: null);
                    });
                    $form->addElement("nick", new Input("Nick", "", $this->getRandomNick()));
                    $player->sendForm($form);
                }));
                $form->addButton(new Button("Random", new ButtonIcon("textures/ui/random_dice", ButtonIcon::TYPE_PATH), function (Player $player) {
                    self::setNick($player, $this->getRandomNick());
                }));
                $form->addButton(new Button("Remove", new ButtonIcon("textures/ui/trash", ButtonIcon::TYPE_PATH), function (Player $player) {
                    self::setNick($player, null);
                }));
                $sender->sendForm($form);
            }
        }
        // TODO: Stocker le nick dans une db
    }

    public static function setNick(Player $player, ?string $nick): void
    {
        $player->setDisplayName($nick ?? $player->getName());
        $player->setNameTag(str_replace($player->getName(), $nick ?? $player->getName(), $player->getNameTag()));
        PlayerProvider::toQuazarPlayer($player)->setData("nick", $nick ?? "off", false, PlayerProvider::TYPE_STRING);
    }

    public function getRandomNick(): ?string
    {
        $array = Core::getInstance()->getConfig()->get('nicks', []);
        if (!$array) {
            return null;
        }
        return $array[array_rand($array)];
    }

}