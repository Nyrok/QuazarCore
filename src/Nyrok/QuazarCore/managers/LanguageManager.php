<?php

namespace Nyrok\QuazarCore\managers;

use Nyrok\QuazarCore\Core;
use Nyrok\QuazarCore\providers\LanguageProvider;

abstract class LanguageManager
{
    public static function initLanguages(): void {
        @mkdir(Core::getInstance()->getDataFolder()."languages");
        foreach (LanguageProvider::LANGUAGES as $LANGUAGE){
            Core::getInstance()->saveResource('languages/lang_'.$LANGUAGE.'.yml', false);
            Core::getInstance()->getLogger()->notice('[LANGUAGES] Lang: lang_'.$LANGUAGE.'.yml Loaded');
        }
    }

}