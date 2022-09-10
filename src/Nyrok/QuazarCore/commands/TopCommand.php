<?php

namespace Nyrok\QuazarCore\commands;

use Nyrok\QuazarCore\librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\librairies\EasyUI\element\Button;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\utils\PlayerUtils;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class TopCommand extends QuazarCommands
{
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if(!$this->testPermissionSilent($sender)) return;
        if($sender instanceof Player){
            $form = new SimpleForm("§m§a"."§f".LanguageProvider::getLanguageMessage('forms.top.title', PlayerProvider::toQuazarPlayer($sender), false));
            foreach (PlayerUtils::STATS as $STAT){
                $form->addButton(new Button("Top $STAT", null, function(Player $player) use ($STAT) {
                    $form = new SimpleForm("§m§c"."§fTop $STAT");
                    $top = "\n";
                    foreach($leaderboard = PlayerUtils::getLeaderboard(strtolower($STAT)) as $name => $value){
                        $top .= str_replace(["{position}", "{name}", "{value}"], [array_search($name, array_keys($leaderboard)) + 1, $name, $value], "                      ".LanguageProvider::getLanguageMessage('forms.top.format'));
                    }
                    $form->setHeaderText($top);
                    $player->sendForm($form);
                }));
            }
            $sender->sendForm($form);
        }
        else $sender->sendMessage(LanguageProvider::getLanguageMessage('messages.errors.not-a-player', null, true));
    }
}