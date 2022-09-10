<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\librairies\CortexPE\DiscordWebhookAPI\Embed;
use Nyrok\QuazarCore\librairies\CortexPE\DiscordWebhookAPI\Message;
use Nyrok\QuazarCore\librairies\CortexPE\DiscordWebhookAPI\Webhook;
use Nyrok\QuazarCore\providers\PlayerProvider;
use Nyrok\QuazarCore\tasks\CPSTask;
use pocketmine\Player;

abstract class CPSManager
{
    private const ARRAY_MAX_SIZE = 100;
    public static array $users = [];
    public static array $clicksData = [];

    /** @var array[] */

    public static function initCPS(){
        Core::getInstance()->getScheduler()->scheduleRepeatingTask(new CPSTask(), 1);

    }

    /**
     * @param Player $player
     */
    public static function initPlayerClickData(Player $player): void
    {
        self::$clicksData[$player->getName()] = [];
    }

    /**
     * @param Player $player
     */
    public static function addClick(Player $player): void
    {
        array_unshift(self::$clicksData[$player->getName()], microtime(true));
        if (count(self::$clicksData[$player->getName()]) >= self::ARRAY_MAX_SIZE) {
            array_pop(self::$clicksData[$player->getName()]);
        }
    }

    /**
     * @param Player $player
     * @param float $deltaTime Interval of time (in seconds) to calculate CPS in
     * @param int $roundPrecision
     * @return float
     */
    public static function getCps(Player $player, float $deltaTime = 1.0, int $roundPrecision = 1): float
    {
        if (!isset(self::$clicksData[$player->getName()]) || empty(self::$clicksData[$player->getName()])) {
            return 0.0;
        }
        $ct = microtime(true);
        return round(count(array_filter(self::$clicksData[$player->getName()], static function (float $t) use ($deltaTime, $ct) : bool {
                return ($ct - $t) <= $deltaTime;
            })) / $deltaTime, $roundPrecision);
    }

    /**
     * @param Player $player
     */
    public static function removePlayerClickData(Player $player): void
    {
        unset(self::$clicksData[$player->getName()]);
    }

    /**
     * @param Player $player
     */
    public static function load(Player $player){
        if(!PlayerProvider::toQuazarPlayer($player)->getData()['cps']){
            unset(self::$users[$player->getName()]);
        }
        else {
            self::activate($player);
        }
        self::initPlayerClickData($player);
    }

    /**
     * @param Player $player
     */
    public static function unload(Player $player){
        self::removePlayerClickData($player);
    }

    /**
     * @param Player $player
     */
    public static function activate(Player $player){
        self::$users[$player->getName()] = true;
    }

    /**
     * @param Player $player
     */
    public static function desactivate(Player $player){
        unset(self::$users[$player->getName()]);
    }

    /**
     * @param Player $player
     * @param float $cps
     * @param bool $webhook
     */
    public static function addAlert(Player $player, float $cps, bool $webhook = false): void {
        foreach (Core::getInstance()->getServer()->getOnlinePlayers() as $staff){
            if($staff->hasPermission(self::getCPSPermission())){
                $staff->sendMessage(str_replace(["{player}", "{cps}", "{ping}"], [$player->getName(), $cps, $player->getPing()], self::getAlertMessage()));
            }
        }
        if($webhook){
            $webhook = new Webhook(self::getWebhook());
            $message = new Message();
            $embed = new Embed();
            $embed->addField("**ALERT CPS:**", "`Pseudo:` **{$player->getName()}**\n`CPS:` **$cps**\n`PING:` **{$player->getPing()}**");
            $embed->setColor(16711680);
            $embed->setFooter("@Nyrok10 on Twitter", "https://images-ext-2.discordapp.net/external/PjKNkC8NT3nO0carZe1i47KKVMUxFI61FPoW3cLb47U/%3Fv%3D1/https/cdn.discordapp.com/emojis/590848931852713984.png");
            $message->addEmbed($embed);
            $message->setContent(Core::getInstance()->getConfig()->getNested("cps.webhook-message"));
            $webhook->send($message);
        }
    }

    /**
     * @param string $type
     * @return int
     */
    public static function getAlertCPS(string $type): int {
        return Core::getInstance()->getConfig()->getNested("cps.alert-$type", 0);
    }

    /**
     * @return string
     */
    public static function getCPSMessage(): string {
        return Core::getInstance()->getConfig()->getNested("cps.text", "{cps}");
    }

    /**
     * @return string
     */
    public static function getAlertMessage(): string {
        return Core::getInstance()->getConfig()->getNested("cps.alert-text", "§f[§c!§f] §4{player} §fest à §c{cps} CPS!");
    }

    /**
     * @return string
     */
    public static function getCPSPermission(): string {
        return Core::getInstance()->getConfig()->getNested("cps.permission", "core.cps.alert");
    }

    /**
     * @return string
     */
    public static function getWebhook(): string {
        return Core::getInstance()->getConfig()->getNested("cps.webhook", "https://discord.com/api/webhooks/982922352922869780/pzbvwGBRDzPzBr55trNCEXG9ZLFmgah3l4DgEXqr86N8FG29h7_nzQ1ThA8giltxYaMM");
    }

    /**
     * @return int
     */
    public static function getPingMinimum(): int {
        return Core::getInstance()->getConfig()->getNested("cps.ping", 100);
    }

}