<?php

namespace Nyrok\QuazarCore\Provider;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\Databases\LangDatabase;
use pocketmine\utils\Config;

abstract class LanguageProvider
{
    const LANGUAGES = ["FR", "EN"];
    const DEFAULT = self::LANGUAGES[0];

    /**
     * @return string|null
     */
    public static function getPrefix(): ?string {
        return Core::getInstance()->getConfig()->get('prefix', "");
    }

    /**
     * @param PlayerProvider|null $player
     * @return LangDatabase|null
     */
    public static function getLanguageFile(?PlayerProvider $player = null): ?LangDatabase {
        return new LangDatabase(Core::getInstance()->getDataFolder()."languages/lang_".($player?->getLanguage() ?? self::DEFAULT).".yml", Config::YAML);
    }

    /**
     * @param string $message
     * @param PlayerProvider|null $player
     * @param bool $prefix
     * @return string
     */
    public static function getLanguageMessage(string $message, ?PlayerProvider $player = null, bool $prefix = false): string {
        return ($prefix ? self::getPrefix() : "") . self::getLanguageFile($player)->getNested($message, "");
    }

    /**
     * @param string $message
     * @param PlayerProvider|null $player
     * @return array
     */
    public static function getLanguageArray(string $message, ?PlayerProvider $player = null): array {
        return self::getLanguageFile($player)->getNested($message, []);
    }

    public static function langToIndex(string $language): int {
        return array_search($language, self::LANGUAGES);
    }
}