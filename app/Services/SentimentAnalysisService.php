<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SentimentAnalysisService
{
    private $dentalTerms;
    private $intensifiers;
    private $negators;

    public function __construct()
    {
        $this->dentalTerms = [
            'positive' => [
                'nyaman' => 3,
                'puas' => 3,
                'sembuh' => 4,
                'membaik' => 3,
                'bersih' => 2,
                'sehat' => 3,
                'berhasil' => 4,
                'efektif' => 3,
                'lega' => 2,
                'bagus' => 2
            ],
            'negative' => [
                'sakit' => -4,
                'nyeri' => -3,
                'bengkak' => -3,
                'infeksi' => -4,
                'berdarah' => -3,
                'sensitif' => -2,
                'berlubang' => -3,
                'masalah' => -2,
                'khawatir' => -2,
                'takut' => -3
            ],
            'neutral' => [
                'gigi',
                'gusi',
                'mulut',
                'dokter',
                'perawatan',
                'pembersihan',
                'pemeriksaan',
                'prosedur',
                'tambalan',
                'kawat gigi'
            ]
        ];

        $this->intensifiers = ['sangat', 'sekali', 'amat', 'sungguh', 'benar-benar'];
        $this->negators = ['tidak', 'bukan', 'belum', 'jangan', 'tanpa'];
    }

    public function analyze($text)
    {
        Log::info('SentimentAnalysisService: Menganalisis teks', ['teks' => $text]);

        $words = $this->tokenize($text);
        $score = 0;
        $relevantWords = 0;
        $lastSentiment = 0;
        $intensifierActive = false;
        $negationActive = false;
        $lastRelevantIndex = -1;

        foreach ($words as $index => $word) {
            if (in_array($word, $this->intensifiers)) {
                $intensifierActive = true;
                continue;
            }

            if (in_array($word, $this->negators)) {
                $negationActive = !$negationActive;
                continue;
            }

            $wordScore = $this->scoreDentalTerm($word);
            if ($wordScore !== 0) {
                if ($negationActive) {
                    $wordScore *= -1;
                }
                if ($intensifierActive) {
                    $wordScore *= 1.5;
                    $intensifierActive = false;
                }
                $score += $wordScore;
                $relevantWords++;
                $lastSentiment = $wordScore;
                $lastRelevantIndex = $index;
            } elseif (in_array($word, $this->dentalTerms['neutral'])) {
                $relevantWords++;
                $lastRelevantIndex = $index;
            }

            // Reset negation after punctuation or if too far from last relevant word
            if (in_array($word, ['.', '!', '?']) || ($lastRelevantIndex !== -1 && $index - $lastRelevantIndex > 3)) {
                $negationActive = false;
            }
        }

        $sentiment = $this->determineSentiment($score, $relevantWords);
        $confidence = $this->calculateConfidence($score, $relevantWords, count($words));

        $result = [
            'sentiment' => $sentiment,
            'score' => $score,
            'confidence' => $confidence
        ];

        Log::info('SentimentAnalysisService: Analisis selesai', $result);
        return $result;
    }

    private function tokenize($text)
    {
        // Pisahkan teks menjadi kata-kata, pertahankan tanda baca penting
        return preg_split('/\s+|([.!?])/', strtolower($text), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }

    private function scoreDentalTerm($word)
    {
        if (isset($this->dentalTerms['positive'][$word])) {
            return $this->dentalTerms['positive'][$word];
        } elseif (isset($this->dentalTerms['negative'][$word])) {
            return $this->dentalTerms['negative'][$word];
        }
        return 0;
    }

    private function determineSentiment($score, $relevantWords)
    {
        if ($relevantWords == 0) {
            return 'neutral';
        }
        $averageScore = $score / $relevantWords;
        if ($averageScore > 0.5) {
            return 'positive';
        } elseif ($averageScore < -0.5) {
            return 'negative';
        }
        return 'neutral';
    }

    private function calculateConfidence($score, $relevantWords, $totalWords)
    {
        if ($relevantWords == 0) {
            return 0.5; // Netral dengan kepercayaan sedang
        }
        $relevanceRatio = $relevantWords / $totalWords;
        $scoreMagnitude = abs($score / $relevantWords);
        return min(($relevanceRatio + $scoreMagnitude) / 2, 1);
    }
}