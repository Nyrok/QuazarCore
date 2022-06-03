<?php

namespace Nyrok\QuazarCore\Commands;

use Nyrok\QuazarCore\Librairies\EasyUI\variant\SimpleForm;
use Nyrok\QuazarCore\Provider\LanguageProvider;
use Nyrok\QuazarCore\Librairies\EasyUI\element\Button;
use Nyrok\QuazarCore\Provider\PlayerProvider;
use Nyrok\QuazarCore\Utils\PlayerUtils;
use pocketmine\command\CommandSender;
use pocketmine\Player;

final class TopCommand extends QuazarCommands
{
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if($sender instanceof Player){
            $form = new SimpleForm("§m§a".LanguageProvider::getLanguageMessage('forms.top.title', PlayerProvider::toQuazarPlayer($sender), false));
            foreach (PlayerUtils::STATS as $STAT){
                $form->addButton(new Button("Top $STAT", null, function(Player $player) use ($STAT) {
                    $form = new SimpleForm("§m§a"."Top $STAT");
                    $top = "";
                    foreach($leaderboard = PlayerUtils::getLeaderboard(strtolower($STAT)) as $name => $value){
                        $top .= str_replace(["{position}", "{name}", "{value}"], [array_search($name, array_keys($leaderboard)) + 1, $name, $value], LanguageProvider::getLanguageMessage('forms.top.format'));
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