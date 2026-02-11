<?php

namespace App\Constants;

class Governorate
{
    public const TUNIS = 'TUNIS';
    public const ARIANA = 'ARIANA';
    public const BEN_AROUS = 'BEN_AROUS';
    public const MANOUBA = 'MANOUBA';
    public const BIZERTE = 'BIZERTE';
    public const NABEUL = 'NABEUL';
    public const BEJA = 'BEJA';
    public const JENDOUBA = 'JENDOUBA';
    public const ZAGHOUAN = 'ZAGHOUAN';
    public const SILIANA = 'SILIANA';
    public const KEF = 'KEF';
    public const SOUSSE = 'SOUSSE';
    public const MONASTIR = 'MONASTIR';
    public const MAHDIA = 'MAHDIA';
    public const SFAX = 'SFAX';
    public const KAIROUAN = 'KAIROUAN';
    public const KASSERINE = 'KASSERINE';
    public const SIDI_BOUZID = 'SIDI_BOUZID';
    public const GABES = 'GABES';
    public const MEDENINE = 'MEDENINE';
    public const TATAOUINE = 'TATAOUINE';
    public const GAFSA = 'GAFSA';
    public const TOZEUR = 'TOZEUR';
    public const KEBILI = 'KEBILI';

    public static function getChoices(): array
    {
        return [
            'Tunis' => self::TUNIS,
            'Ariana' => self::ARIANA,
            'Ben Arous' => self::BEN_AROUS,
            'Manouba' => self::MANOUBA,
            'Bizerte' => self::BIZERTE,
            'Nabeul' => self::NABEUL,
            'Beja' => self::BEJA,
            'Jendouba' => self::JENDOUBA,
            'Zaghouan' => self::ZAGHOUAN,
            'Siliana' => self::SILIANA,
            'Kef' => self::KEF,
            'Sousse' => self::SOUSSE,
            'Monastir' => self::MONASTIR,
            'Mahdia' => self::MAHDIA,
            'Sfax' => self::SFAX,
            'Kairouan' => self::KAIROUAN,
            'Kasserine' => self::KASSERINE,
            'Sidi Bouzid' => self::SIDI_BOUZID,
            'Gabes' => self::GABES,
            'Medenine' => self::MEDENINE,
            'Tataouine' => self::TATAOUINE,
            'Gafsa' => self::GAFSA,
            'Tozeur' => self::TOZEUR,
            'Kebili' => self::KEBILI,
        ];
    }

    public static function getDisplayName(string $governorate): string
    {
        $choices = self::getChoices();
        $flipped = array_flip($choices);

        return $flipped[$governorate] ?? $governorate;
    }
}
