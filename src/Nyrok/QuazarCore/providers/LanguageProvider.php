<?php

namespace Nyrok\QuazarCore\providers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\databases\LangDatabase;
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
        return new LangDatabase(
            Core::getInstance()->getDataFolder()."languages/lang_".($player?->getLanguage() ?? self::DEFAULT).".yml",
            Config::YAML,
            yaml_parse(file_get_contents(Core::getInstance()->getFilePath() . "resources/languages/" . "lang_".($player?->getLanguage() ?? self::DEFAULT).".yml")));
    }

    /**
     * @param string $message
     * @param PlayerProvider|null $player
     * @param bool $prefix
     * @return string
     */
    public static function getLanguageMessage(string $message, ?PlayerProvider $player = null, bool $prefix = false): string {
        return ($prefix ? self::getPrefix() : "") . self::getLanguageFile($player)->getNested($message, $message);
    }

    /**
     * @param string $message
     * @param PlayerProvider|null $player
     * @return array
     */
    public static function getLanguageArray(string $message, ?PlayerProvider $player = null): array {
        return self::getLanguageFile($player)->getNested($message, []);
    }

    /**
     * @param string $language
     * @return int
     */
    public static function langToIndex(string $language): int {
        return array_search($language, self::LANGUAGES);
    }
}