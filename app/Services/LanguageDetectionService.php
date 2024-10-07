<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LanguageDetectionService
{
    private $languageProfiles;

    public function __construct()
    {
        $this->languageProfiles = [
            'id' => [
                'dan',
                'yang',
                'di',
                'itu',
                'dengan',
                'untuk',
                'tidak',
                'ini',
                'dari',
                'dalam',
                'akan',
                'pada',
                'saya',
                'se',
                'orangnya',
                'ia',
                'bahwa',
                'oleh',
                'satu',
                'maka'
            ],
            'en' => [
                'the',
                'be',
                'to',
                'of',
                'and',
                'a',
                'in',
                'that',
                'have',
                'I',
                'it',
                'for',
                'not',
                'on',
                'with',
                'he',
                'as',
                'you',
                'do',
                'at'
            ]
        ];
    }

    public function detect($text)
    {
        Log::info('LanguageDetectionService: Memulai deteksi bahasa', ['textLength' => strlen($text)]);

        $words = str_word_count(strtolower($text), 1);
        $scores = [];

        foreach ($this->languageProfiles as $lang => $profile) {
            $scores[$lang] = 0;
            foreach ($words as $word) {
                if (in_array($word, $profile)) {
                    $scores[$lang]++;
                }
            }
        }

        arsort($scores);
        $detectedLanguage = key($scores);

        // Jika skor terlalu rendah, kembalikan 'unknown'
        if ($scores[$detectedLanguage] < 2) {
            $detectedLanguage = 'unknown';
        }

        Log::info('LanguageDetectionService: Deteksi bahasa selesai', [
            'detectedLanguage' => $detectedLanguage,
            'scores' => $scores
        ]);

        return $detectedLanguage;
    }

    public function addLanguageProfile($languageCode, $words)
    {
        if (!isset($this->languageProfiles[$languageCode])) {
            $this->languageProfiles[$languageCode] = $words;
            Log::info('LanguageDetectionService: Profil bahasa baru ditambahkan', ['languageCode' => $languageCode]);
        } else {
            Log::warning('LanguageDetectionService: Profil bahasa sudah ada', ['languageCode' => $languageCode]);
        }
    }

    public function getSupportedLanguages()
    {
        return array_keys($this->languageProfiles);
    }
}
