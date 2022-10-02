<?php
namespace Nyrok\QuazarCore;

use jacknoordhuis\combatlogger\CombatLogger;
use Nyrok\QuazarCore\databases\MuteListDatabase;
use Nyrok\QuazarCore\databases\SanctionsDatabase;
use Nyrok\QuazarCore\databases\UserDatabase;
use Nyrok\QuazarCore\managers\ArenasManager;
use Nyrok\QuazarCore\managers\CommandsManager;
use Nyrok\QuazarCore\managers\CooldownManager;
use Nyrok\QuazarCore\managers\CosmeticsManager;
use Nyrok\QuazarCore\managers\CPSManager;
use Nyrok\QuazarCore\managers\FFAManager;
use Nyrok\QuazarCore\managers\FloatingTextManager;
use Nyrok\QuazarCore\managers\ListenersManager;
use Nyrok\QuazarCore\managers\LanguageManager;
use Nyrok\QuazarCore\databases\ConfigDatabase;
use Nyrok\QuazarCore\managers\LobbyManager;
use Nyrok\QuazarCore\managers\ScoreBoardManager;
use Nyrok\QuazarCore\managers\SoupManager;
use Nyrok\QuazarCore\managers\StaffManager;
use Nyrok\QuazarCore\managers\TimeManager;
use Nyrok\QuazarCore\providers\LanguageProvider;
use Nyrok\QuazarCore\providers\PlayerProvider;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class Core extends PluginBase
{
    use SingletonTrait;
    /**
     * @var ConfigDatabase
     */
    public ConfigDatabase $config;
    /**
     * @var UserDatabase
     */
    public UserDatabase $data;

    /**
     * @var SanctionsDatabase
     */
    public SanctionsDatabase $sanctions;

    /**
     * @var MuteListDatabase
     */
    public MuteListDatabase $muteList;

    /**
     * @var string
     */
    private string $filePath;

    public function onEnable(): void
    {
        $this::setInstance($this);
        $this->saveResource("config.yml", true);

        $this->filePath = $this->getFile();
        $this->config = new ConfigDatabase($this->getDataFolder().'config.yml', Config::YAML);
        $this->data = new UserDatabase($this->getDataFolder().'data.json', Config::JSON);
        $this->sanctions = new SanctionsDatabase($this->getDataFolder().'sanctions.json', Config::JSON);
        $this->muteList = new MuteListDatabase($this->getDataFolder().'muteList.json', Config::JSON);

        LanguageManager::initLanguages();
        CommandsManager::initCommands();
        ScoreBoardManager::initScoreBoard();
        ListenersManager::initListeners($this);
        LobbyManager::initLobby();
        FFAManager::initFFAs();
        SoupManager::initSoups();
        FloatingTextManager::initFloatingTexts();
        CooldownManager::initCooldowns();
        CPSManager::initCPS();
        StaffManager::initItems();
        TimeManager::initTime();
        ArenasManager::initArenas();
        CosmeticsManager::initCosmetics();

        $this->getLogger()->warning("By @Nyrok10 on Twitter and RemBog.");
    }

    public function onDisable(): void
    {
        CombatLogger::getInstance()->taggedPlayers = [];
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            $player->kick(LanguageProvider::getLanguageMessage("messages.shutoff", PlayerProvider::toQuazarPlayer($player), false), false);
        }
    }

    /**
     * @return ConfigDatabase
     */
    public function getConfig(): ConfigDatabase {
        return $this->config;
    }

    /**
     * @return UserDatabase
     */
    public function getData(): UserDatabase {
        return $this->data;
    }

    /**
     * @return SanctionsDatabase
     */
    public function getSanctions(): SanctionsDatabase
    {
        return $this->sanctions;
    }

    public function getMuteList(): MuteListDatabase {
        return $this->muteList;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

}
