<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\librairies\CortexPE\DiscordWebhookAPI\Embed;
use Nyrok\QuazarCore\librairies\CortexPE\DiscordWebhookAPI\Message;
use Nyrok\QuazarCore\librairies\CortexPE\DiscordWebhookAPI\Webhook;
use Nyrok\QuazarCore\librairies\EasyUI\element\Input;
use Nyrok\QuazarCore\librairies\EasyUI\utils\FormResponse;
use Nyrok\QuazarCore\librairies\EasyUI\variant\CustomForm;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use Nyrok\QuazarCore\librairies\EasyUI\element\Dropdown;
use Nyrok\QuazarCore\librairies\EasyUI\element\Option;

final class ReportCommand extends QuazarCommands
{
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        if($sender instanceof Player){
            if(isset($args[0])){
                if($target = Server::getInstance()->getPlayer($args[0]) and $target->getName() !== $sender->getName()) $this->reportPlayer($sender, $target);
                else $sender->sendMessage(LanguageProvider::getLanguageMessage('messages.errors.player-not-connected', PlayerProvider::toQuazarPlayer($sender), true));
            }
            else {
                $this->reportMenu($sender);
            }
        }
        else $sender->sendMessage(LanguageProvider::getLanguageMessage('messages.errors.not-a-player', null, true));
    }

    /**
     * @param Player $player
     */
    public function reportMenu(Player $player): void {
        $form = new CustomForm('§m§a'.LanguageProvider::getLanguageMessage('forms.report.1.title', PlayerProvider::toQuazarPlayer($player), false));
        $dropdown = new Dropdown(LanguageProvider::getLanguageMessage('forms.report.1.1', PlayerProvider::toQuazarPlayer($player), false));
        foreach (Server::getInstance()->getOnlinePlayers() as $target){
            if($target->getName() !== $player->getName()) $dropdown->addOption(new Option($target->getName(), $target->getName()));
        }
        $form->addElement("player", $dropdown);
        $form->setSubmitListener(function (Player $player, FormResponse $response){
            if($target = Server::getInstance()->getPlayer($response->getDropdownSubmittedOptionId("player"))) $this->reportPlayer($player, $target);
            else $player->sendMessage(LanguageProvider::getLanguageMessage('messages.errors.error', PlayerProvider::toQuazarPlayer($player), true));
        });
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param Player $target
     */
    public function reportPlayer(Player $player, Player $target): void {
        $form = new CustomForm(str_replace("{name}", $target->getName(), LanguageProvider::getLanguageMessage('forms.report.2.title', PlayerProvider::toQuazarPlayer($player), false)));
        $dropdown = new Dropdown(LanguageProvider::getLanguageMessage('forms.report.2.1.text', PlayerProvider::toQuazarPlayer($player), false));
        foreach (LanguageProvider::getLanguageArray('forms.report.2.1.types', PlayerProvider::toQuazarPlayer($player)) as $type){
            $dropdown->addOption(new Option($type, $type));
        }
        $input = new Input(LanguageProvider::getLanguageMessage('forms.report.2.2.header', PlayerProvider::toQuazarPlayer($player), false), LanguageProvider::getLanguageMessage('forms.report.2.2.default', PlayerProvider::toQuazarPlayer($player), false), LanguageProvider::getLanguageMessage('forms.report.2.2.place-holder', PlayerProvider::toQuazarPlayer($player), false));
        $form->addElement("reason", $dropdown);
        $form->addElement("details", $input);
        $form->setSubmitListener(function (Player $player, FormResponse $response) use ($target){
            $webhook = new Webhook(Core::getInstance()->getConfig()->getNested("report.webhook"));
            $message = new Message();
            $embed = new Embed();
            $embed->addField("**Voici les détails du report:**", "`Pseudo:` **{$player->getName()}**\n`Joueur Report:` **{$target->getName()}**\n`Raison:` **{$response->getDropdownSubmittedOptionId("reason")}**\n`Détails:` **{$response->getInputSubmittedText("details")}**");
            $embed->setColor(16711680);
            $embed->setFooter("@Nyrok10 on Twitter", "https://images-ext-2.discordapp.net/external/PjKNkC8NT3nO0carZe1i47KKVMUxFI61FPoW3cLb47U/%3Fv%3D1/https/cdn.discordapp.com/emojis/590848931852713984.png");
            $message->addEmbed($embed);
            $message->setContent(Core::getInstance()->getConfig()->getNested("report.message"));
            $webhook->send($message);
        });
        $player->sendForm($form);
    }
}