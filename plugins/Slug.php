<?php

namespace Muzikdj\Plugins;

class Slug extends \Phalcon\Mvc\User\Plugin {

    const TRANSLITERATOR_ID = 'Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();';

    public static function get($string = '') {
        if (empty($string)) {
            return '';
        } else {
            $transliterator = \Transliterator::create(self::TRANSLITERATOR_ID);
            $string = $transliterator->transliterate($string);
            $string = preg_replace('/[^a-z0-9 ]/', '', $string);
            $string = trim($string);
            return \Phalcon\Tag::friendlyTitle($string);
        }
    }
}