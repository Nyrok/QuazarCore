<?php
namespace Nyrok\QuazarCore;

use jacknoordhuis\combatlogger\CombatLogger;
use Nyrok\QuazarCore\Databases\UserDatabase;
use Nyrok\QuazarCore\Managers\CommandsManager;
use Nyrok\QuazarCore\Managers\FFAManager;
use Nyrok\QuazarCore\Managers\FloatingTextManager;
use Nyrok\QuazarCore\Managers\ListenersManager;
use Nyrok\QuazarCore\Managers\LanguageManager;
use Nyrok\QuazarCore\Databases\ConfigDatabase;
use Nyrok\QuazarCore\Managers\LobbyManager;
use Nyrok\QuazarCore\Managers\ScoreBoardManager;
use Nyrok\QuazarCore\Managers\SoupManager;
use Nyrok\QuazarCore\Provider\LanguageProvider;
use Nyrok\QuazarCore\Provider\PlayerProvider;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class Core extends PluginBase
{
    use SingletonTrait;
    public ConfigDatabase $config;
    public UserDatabase $data;

    public function onEnable(): void
    {
        $this::setInstance($this);
        $this->saveResource("config.yml", true);
        $this->config = new ConfigDatabase($this->getDataFolder().'config.yml', Config::YAML);
        $this->data = new UserDatabase($this->getDataFolder().'data.json', Config::JSON);

        LanguageManager::initLanguages();
        CommandsManager::initCommands();
        ScoreBoardManager::initScoreBoard();
        ListenersManager::initListeners($this);
        LobbyManager::initLobby();
        FFAManager::initFFAs();
        SoupManager::initSoups();
        FloatingTextManager::initFloatingTexts();

        $this->getLogger()->notice("By @Nyrok10 on Twitter.");
    }

    public function onDisable()
    {
        CombatLogger::getInstance()->taggedPlayers = [];
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            $player->kick(LanguageProvider::getLanguageMessage("messages.shutoff", PlayerProvider::toQuazarPlayer($player), true), false);
        }
    }

    public function getConfig(): ConfigDatabase {
        return $this->config;
    }

    public function getData(): UserDatabase {
        return $this->data;
    }

}