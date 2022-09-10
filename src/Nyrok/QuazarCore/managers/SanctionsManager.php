<?php

namespace Nyrok\QuazarCore\managers;

use DateTime;
use DateTimeZone;
use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\librairies\EasyUI\element\Label;
use Nyrok\QuazarCore\librairies\EasyUI\variant\CustomForm;
use pocketmine\Player;

abstract class SanctionsManager
{
    const TYPE_KICK = 1;
    const TYPE_BAN = 2;
    const TYPE_MUTE = 3;
    const TYPE_WARN = 4;

    public static function addSanction(string $name, string $reason, string $staff, int $type, int $duree = 0){
        $date = new DateTime();
        $sanctions = Core::getInstance()->getSanctions();
        $i = count(gettype($sanctions->get(strtolower($name))) === "array" ? $sanctions->get(strtolower($name)) : array()) + 1;
        $sanction = array("Date :" => $date->setTimezone(new DateTimeZone("Europe/Paris"))->getTimestamp(),"Type :" => match ($type){
            self::TYPE_KICK => "KICK",
            self::TYPE_BAN => "BAN",
            self::TYPE_MUTE => "MUTE",
            self::TYPE_WARN => "WARN",
        }, "Raison :" => "$reason", "Staff :" => strtolower($staff));
        if(in_array($type, [self::TYPE_BAN, self::TYPE_MUTE])){
            $sanction["Durée :"] = $duree === 0 ? "Permanent" : date("d/m/Y H:i", $duree);
        }
        $sanctions->setNested(strtolower($name).".$i", $sanction);
        $sanctions->save();
    }


    public static function Sanctions(Player $player, $target){
        $date = new DateTime();
        $message = "";
        $sanctions = Core::getInstance()->getSanctions()->get(strtolower($target->getName())) ?? array();
        if(!empty($sanctions)){
            foreach ($sanctions as $index => $array) {
                $message .= "§cSanction §4[§e$index"."§4] §f:\n";
                foreach ($array as $key => $value) {
                    if($key !== "Date :"){
                        $message .= "§f- §f$key §c$value\n";
                    }
                    else {
                        $message .= "§f- §f$key §c{$date->setTimezone(new DateTimeZone("Europe/Paris"))->setTimestamp($value)->format("d/m H:i")}\n";
                    }
                }
            }
        }
        else $message .= "§c".$target->getName()." §fn'a aucune sanction !\n";
        $form = new CustomForm("§4[ §c{$target->getName()} §4ù] §f- §cSANCTIONS", function (){ return true; });
        $form->addElement("Sanctions", new Label($message));
        $player->sendForm($form);
    }

    public static function StaffSanctions(Player $player, $staff){
        $date = new DateTime();
        $i = 0;
        $message = "§lVoici la liste des sanctions accomplies par §c$staff §f:§r\n";
        foreach (Core::getInstance()->getSanctions()->getAll() as $name => $sanctions) {
            foreach ($sanctions as $index => $array) {
                if ($array["Staff :"] === $staff) {
                    $message .= "§cSanction §4[§e$index" . "§4] §fJoueur : §c$name §f:\n";
                    foreach ($array as $key => $value) {
                        if ($key !== "Date :") {
                            $message .= "§f- §f$key §c$value\n";
                        } else {
                            $message .= "§f- §f$key §c{$date->setTimezone(new DateTimeZone("Europe/Paris"))->setTimestamp($value)->format("d/m H:i")}\n";
                        }
                    }
                    $i++;
                }
            }
        }
        if ($i === 0) $message .= "> §e$staff §fn'a accomplie §faucune §cSanction.";
        $form = new CustomForm("§4[ §c$staff §4] §f- §cSANCTIONS", function (){ return true; });
        $form->addElement("Sanctions", new Label($message));
        $player->sendForm($form);
    }

}