<?php

namespace Nyrok\QuazarCore\Managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\Provider\LanguageProvider;

abstract class LanguageManager
{
    public static function initLanguages(): void {
        @mkdir(Core::getInstance()->getDataFolder()."languages");
        foreach (LanguageProvider::LANGUAGES as $LANGUAGE){
            Core::getInstance()->saveResource('languages/lang_'.$LANGUAGE.'.yml', true);
            Core::getInstance()->getLogger()->alert('[LANGUAGES] Lang: lang_'.$LANGUAGE.'.yml Loaded');
        }
    }

}