<?php
class ProfanityFilter {
    public static function filtrerTexteAvance($texte, $badWords, $censChar = '*') {
        $mots = preg_split('/(\s+)/u', $texte, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($mots as &$mot) {
            $nettoye = preg_replace('/[^\p{L}\p{N}]/u', '', $mot);
            foreach ($badWords as $interdit) {
                if (mb_strtolower($nettoye) === mb_strtolower($interdit)) {
                    $mot = preg_replace('/[\p{L}\p{N}]/u', $censChar, $mot);
                    break;
                }
            }
        }
        return implode('', $mots);
    }

    public static function getListeBadWords() {
        return [
            'putain', 'merde', 'connard', 'connasse', 'salope', 'enculé', 'encule', 'niquer', 'nique', 'batard',
            'pédé', 'pd', 'fdp', 'ta gueule', 'tg', 'chiant', 'chiotte', 'bordel', 'bite', 'couille', 'chatte',
            'fuck', 'fucking', 'fuckyou', 'shit', 'bitch', 'bastard', 'dick', 'asshole', 'slut', 'cunt',
            'motherfucker', 'bullshit', 'jerk', 'retard', 'dumbass',
            'zebi', 'zob', 'nik', 'nikmah', 'zml', 'khra', 't9awd', 'tfa9', 'kleb', 'zebb', 'kaboul',
            '3assba', 'mchmt', 'kess', 'zok', 'boush', 'nab', 'noub', 'miboun', '9a7ba', 'no9ba', '3asba'
        ];
    }
}
