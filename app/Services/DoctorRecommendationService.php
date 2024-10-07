<?php

namespace App\Services;

use App\Models\Doctor;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DoctorRecommendationService
{
    public function getRecommendedDoctor($userId, $keywords, $appointmentDate = null)
    {
        Log::info('DoctorRecommendationService: Memulai pencarian dokter', [
            'userId' => $userId,
            'keywords' => $keywords,
            'appointmentDate' => $appointmentDate
        ]);

        // Dapatkan semua dokter
        $doctors = Doctor::all();

        if ($doctors->isEmpty()) {
            Log::info('DoctorRecommendationService: Tidak ada dokter ditemukan');
            return null;
        }

        $bestDoctor = null;
        $bestScore = -1;

        foreach ($doctors as $doctor) {
            $score = $this->calculateDoctorScore($doctor, $keywords);

            Log::debug('DoctorRecommendationService: Skor dokter', [
                'doctorId' => $doctor->id,
                'doctorName' => $doctor->name,
                'score' => $score
            ]);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestDoctor = $doctor;
            }
        }

        if ($bestDoctor) {
            Log::info('DoctorRecommendationService: Dokter terbaik ditemukan', [
                'doctorId' => $bestDoctor->id,
                'doctorName' => $bestDoctor->name,
                'score' => $bestScore
            ]);
        } else {
            Log::info('DoctorRecommendationService: Tidak ada dokter yang cocok ditemukan');
        }

        return $bestDoctor;
    }

    private function calculateDoctorScore($doctor, $keywords)
    {
        $score = 0;

        // Cek kecocokan spesialisasi
        $specialization = strtolower($doctor->spesialis);
        foreach ($keywords as $keyword) {
            if (Str::contains($specialization, strtolower($keyword))) {
                $score += 10;
                Log::debug('DoctorRecommendationService: Kecocokan spesialisasi ditemukan', [
                    'doctorId' => $doctor->id,
                    'keyword' => $keyword,
                    'scoreAdded' => 10
                ]);
                break;  // Hanya berikan skor sekali untuk spesialisasi
            }
        }

        // Cek kecocokan nama dokter
        $doctorName = strtolower($doctor->name);
        foreach ($keywords as $keyword) {
            if (Str::contains($doctorName, strtolower($keyword))) {
                $score += 5;
                Log::debug('DoctorRecommendationService: Kecocokan nama dokter ditemukan', [
                    'doctorId' => $doctor->id,
                    'keyword' => $keyword,
                    'scoreAdded' => 5
                ]);
            }
        }

        // Berikan skor tambahan berdasarkan panjang nama spesialisasi
        $specializationScore = min(strlen($doctor->spesialis) / 5, 10);
        $score += $specializationScore;
        Log::debug('DoctorRecommendationService: Skor tambahan untuk panjang spesialisasi', [
            'doctorId' => $doctor->id,
            'specializationLength' => strlen($doctor->spesialis),
            'scoreAdded' => $specializationScore
        ]);

        return $score;
    }

    private function extractKeywordsFromSpecialization($specialization)
    {
        $words = explode(' ', strtolower($specialization));
        $commonWords = ['dokter', 'gigi', 'spesialis', 'dan'];
        $keywords = array_diff($words, $commonWords);

        Log::debug('DoctorRecommendationService: Kata kunci diekstrak dari spesialisasi', [
            'specialization' => $specialization,
            'extractedKeywords' => $keywords
        ]);

        return $keywords;
    }
}