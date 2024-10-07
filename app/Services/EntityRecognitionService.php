<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EntityRecognitionService
{
    private $entityDictionaries;

    public function __construct()
    {
        $this->entityDictionaries = [
            'SYMPTOM' => [
                'sakit gigi',
                'gusi bengkak',
                'gigi sensitif',
                'bau mulut',
                'gigi berlubang',
                'gusi berdarah',
                'nyeri saat mengunyah',
                'gigi goyang',
                'plak gigi'
            ],
            'PROCEDURE' => [
                'pembersihan karang gigi',
                'pencabutan gigi',
                'pemasangan kawat gigi',
                'perawatan saluran akar',
                'penambalan gigi',
                'pemutihan gigi',
                'implan gigi',
                'veneer gigi',
                'pemasangan gigi palsu',
                'bedah gusi'
            ],
            'MEDICATION' => [
                'ibuprofen',
                'paracetamol',
                'amoxicillin',
                'chlorhexidine',
                'fluoride',
                'benzocaine',
                'hydrogen peroxide',
                'lidocaine'
            ],
            'DENTAL_TERM' => [
                'enamel',
                'dentin',
                'pulpa',
                'gingiva',
                'periodontitis',
                'karies',
                'bruxism',
                'malocclusion',
                'abscess',
                'tartar'
            ],
            'ORAL_HYGIENE_PRODUCT' => [
                'sikat gigi',
                'pasta gigi',
                'benang gigi',
                'obat kumur',
                'pembersih lidah',
                'tusuk gigi',
                'sikat interdental',
                'gel fluoride'
            ]
        ];
    }

    public function recognize($text)
    {
        Log::info('EntityRecognitionService: Memulai pengenalan entitas', ['text' => $text]);

        $recognizedEntities = [];

        foreach ($this->entityDictionaries as $entityType => $dictionary) {
            foreach ($dictionary as $term) {
                if (stripos($text, $term) !== false) {
                    $recognizedEntities[] = [
                        'type' => $entityType,
                        'value' => $term,
                        'position' => stripos($text, $term)
                    ];
                }
            }
        }

        // Menangani entitas gabungan (mis. "sakit gigi belakang")
        $recognizedEntities = $this->handleCompoundEntities($recognizedEntities, $text);

        // Mengurutkan entitas berdasarkan posisi dalam teks
        usort($recognizedEntities, function ($a, $b) {
            return $a['position'] - $b['position'];
        });

        Log::info('EntityRecognitionService: Pengenalan entitas selesai', [
            'recognizedEntitiesCount' => count($recognizedEntities)
        ]);

        return $recognizedEntities;
    }

    private function handleCompoundEntities($entities, $text)
    {
        $compoundEntities = [];
        $skipIndices = [];

        for ($i = 0; $i < count($entities); $i++) {
            if (in_array($i, $skipIndices)) continue;

            $currentEntity = $entities[$i];
            $nextEntityIndex = $i + 1;

            if (isset($entities[$nextEntityIndex])) {
                $nextEntity = $entities[$nextEntityIndex];
                $compoundTerm = substr(
                    $text,
                    $currentEntity['position'],
                    $nextEntity['position'] + strlen($nextEntity['value']) - $currentEntity['position']
                );

                if (stripos($compoundTerm, $currentEntity['value'] . ' ' . $nextEntity['value']) !== false) {
                    $compoundEntities[] = [
                        'type' => $currentEntity['type'],
                        'value' => trim($compoundTerm),
                        'position' => $currentEntity['position']
                    ];
                    $skipIndices[] = $nextEntityIndex;
                    continue;
                }
            }

            $compoundEntities[] = $currentEntity;
        }

        return $compoundEntities;
    }
}
