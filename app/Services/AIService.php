<?php

namespace App\Services;

use GeminiAPI\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;
use App\Services\SentimentAnalysisService;
use App\Services\DoctorRecommendationService;
use App\Services\EntityRecognitionService;
use App\Services\LanguageDetectionService;
use App\Exceptions\AIServiceException;

class AIService
{
    protected $sentimentService;
    protected $doctorRecommendationService;
    protected $entityRecognitionService;
    protected $languageDetectionService;
    protected $userState;
    protected $sessionData;
    protected $currentLanguage;
    protected $medicalHistory;
    protected $treatmentPlan;
    protected $emergencyProtocol;

    const STATE_INITIAL = 'awal';
    const STATE_IDENTITY_GATHERED = 'identitas_terkumpul';
    const STATE_MEDICAL_HISTORY = 'riwayat_medis';
    const STATE_CONSULTATION = 'konsultasi';
    const STATE_DIAGNOSIS = 'diagnosis';
    const STATE_TREATMENT = 'perawatan';
    const STATE_FOLLOW_UP = 'tindak_lanjut';
    const STATE_EMERGENCY = 'darurat';

    const MAX_RETRY_ATTEMPTS = 3;
    const EMERGENCY_KEYWORDS = ['darurat', 'pendarahan hebat', 'kecelakaan', 'trauma', 'tidak sadarkan diri'];

    private const APP_NAME = 'E-tooth';
    private const INSTITUTION = 'Poltekkes Kemenkes Yogyakarta';
    
    public function __construct(
        SentimentAnalysisService $sentimentService,
        DoctorRecommendationService $doctorRecommendationService,
        EntityRecognitionService $entityRecognitionService,
        LanguageDetectionService $languageDetectionService
    ) {
        $this->APP_NAME = 'E-tooth';
        $this->INSTITUTION = 'Poltekkes Kemenkes Yogyakarta';
        $this->sentimentService = $sentimentService;
        $this->doctorRecommendationService = $doctorRecommendationService;
        $this->entityRecognitionService = $entityRecognitionService;
        $this->languageDetectionService = $languageDetectionService;
        $this->userState = self::STATE_INITIAL;
        $this->sessionData = [];
        $this->currentLanguage = 'id';
        $this->medicalHistory = [];
        $this->treatmentPlan = [];
        $this->emergencyProtocol = $this->initializeEmergencyProtocol();
    }

    public function generateResponse($message, $userId, $context = [])
    {
        Log::info('AIService: generateResponse dipanggil', [
            'pesan' => $message,
            'idPengguna' => $userId,
            'tipeIdPengguna' => gettype($userId),
            'tipeKonteks' => gettype($context)
        ]);

        if (!is_string($userId)) {
            Log::error('AIService: Tipe ID pengguna tidak valid', [
                'idPengguna' => $userId,
                'tipe' => gettype($userId)
            ]);
            throw new \InvalidArgumentException('ID pengguna harus berupa string');
        }

        $this->loadUserState($userId);
        $this->processMessage($message);

        if ($this->isEmergency($message)) {
            return $this->handleEmergency($message, $userId);
        }

        $prompt = $this->buildPrompt($message, $context);

        $retryCount = 0;
        do {
            try {
                $response = Gemini::generateText($prompt);
                if (empty($response)) {
                    throw new AIServiceException("Respons kosong dari API Gemini");
                }

                $processedResponse = $this->processResponse($response, $userId);
                $this->saveUserState($userId);

                Log::info('AIService: Respons berhasil dihasilkan', [
                    'idPengguna' => $userId,
                    'statusPengguna' => $this->userState
                ]);

                return $processedResponse;
            } catch (AIServiceException $e) {
                Log::error('AIService: Kesalahan menghasilkan respons', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'percobaan' => $retryCount + 1
                ]);
                $retryCount++;
                if ($retryCount >= self::MAX_RETRY_ATTEMPTS) {
                    return $this->generateErrorResponse($e->getMessage());
                }
                sleep(pow(2, $retryCount)); // Exponential backoff
            }
        } while ($retryCount < self::MAX_RETRY_ATTEMPTS);
    }
    
     private function generateErrorResponse($errorMessage)
    {
        return [
            'content' => "Maaf, terjadi kesalahan: $errorMessage",
            'error' => true,
            'user_state' => $this->userState
        ];
    }

    private function initializeEmergencyProtocol()
    {
        return new class {
            public function getResponse($message)
            {
                if (stripos($message, 'pendarahan') !== false) {
                    return "DARURAT: Segera tekan area yang berdarah dengan kain bersih. Jika pendarahan tidak berhenti dalam 15 menit, segera ke unit gawat darurat terdekat.";
                }
                if (stripos($message, 'kecelakaan') !== false) {
                    return "DARURAT: Jaga agar pasien tetap tenang. Jika ada gigi yang lepas, simpan dalam susu atau air garam. Segera menuju ke unit gawat darurat gigi terdekat.";
                }
                return "DARURAT: Ini adalah situasi darurat. Tetap tenang dan segera hubungi layanan gawat darurat atau menuju ke rumah sakit terdekat.";
            }
        };
    }

    private function processMessage($message)
    {
        $entities = $this->entityRecognitionService->recognize($message);
        foreach ($entities as $entity) {
            switch ($entity['type']) {
                case 'GEJALA':
                    $this->sessionData['gejala'][] = $entity['value'];
                    break;
                case 'OBAT':
                    $this->sessionData['obat'][] = $entity['value'];
                    break;
                case 'ALERGI':
                    $this->sessionData['alergi'][] = $entity['value'];
                    break;
                case 'PROSEDUR':
                    $this->sessionData['prosedur'][] = $entity['value'];
                    break;
                case 'ISTILAH_GIGI':
                    $this->sessionData['istilah_gigi'][] = $entity['value'];
                    break;
            }
        }

        $this->updateUserStateBasedOnMessage($message);
    }

    private function updateUserStateBasedOnMessage($message)
    {
        if ($this->userState === self::STATE_INITIAL && $this->containsIdentityInfo($message)) {
            $this->userState = self::STATE_IDENTITY_GATHERED;
        } elseif ($this->userState === self::STATE_IDENTITY_GATHERED && $this->containsMedicalHistoryInfo($message)) {
            $this->userState = self::STATE_MEDICAL_HISTORY;
        } elseif ($this->userState === self::STATE_MEDICAL_HISTORY && $this->containsSymptomInfo($message)) {
            $this->userState = self::STATE_CONSULTATION;
        } elseif ($this->userState === self::STATE_CONSULTATION && $this->containsDiagnosisInfo($message)) {
            $this->userState = self::STATE_DIAGNOSIS;
        } elseif ($this->userState === self::STATE_DIAGNOSIS && $this->containsTreatmentInfo($message)) {
            $this->userState = self::STATE_TREATMENT;
        } elseif ($this->userState === self::STATE_TREATMENT && $this->containsFollowUpInfo($message)) {
            $this->userState = self::STATE_FOLLOW_UP;
        }
    }
    
     private function updateUserState($response)
    {
        // Implementasi logika untuk memperbarui user state berdasarkan respons
        if (strpos($response, 'diagnosis') !== false) {
            $this->userState = self::STATE_DIAGNOSIS;
        } elseif (strpos($response, 'perawatan') !== false) {
            $this->userState = self::STATE_TREATMENT;
        } elseif (strpos($response, 'follow-up') !== false) {
            $this->userState = self::STATE_FOLLOW_UP;
        }
    }

    private function containsIdentityInfo($text)
    {
        return preg_match('/\b(?:nama|tanggal lahir|nomor identitas|nik)\b/i', $text);
    }

    private function containsMedicalHistoryInfo($text)
    {
        return preg_match('/\b(?:riwayat|alergi|obat|penyakit)\b/i', $text);
    }

    private function containsSymptomInfo($text)
    {
        return preg_match('/\b(?:gejala|keluhan|sakit|nyeri)\b/i', $text);
    }

    private function containsDiagnosisInfo($text)
    {
        return preg_match('/\b(?:diagnosis|kemungkinan|penyebab|kondisi)\b/i', $text);
    }

    private function containsTreatmentInfo($text)
    {
        return preg_match('/\b(?:perawatan|pengobatan|tindakan|terapi|prosedur)\b/i', $text);
    }

    private function containsFollowUpInfo($text)
    {
        return preg_match('/\b(?:tindak lanjut|kontrol|kunjungan berikutnya|pemeriksaan ulang)\b/i', $text);
    }

    private function buildPrompt($message, $context)
    {
        $basePrompt = $this->getBasePrompt();
        $statePrompt = $this->getStateSpecificPrompt();
        $historyPrompt = $this->buildHistoryPrompt($context);
        $medicalContextPrompt = $this->buildMedicalContextPrompt();

        $fullPrompt = <<<EOT
        {$basePrompt}

        {$statePrompt}

        Riwayat Medis Pasien:
        {$medicalContextPrompt}

        Riwayat Percakapan:
        {$historyPrompt}

        Data Sesi Saat Ini:
        {$this->getFormattedSessionData()}

        Pesan Pengguna: {$message}

        Respons AI:
        EOT;

        Log::debug('AIService: Prompt dibangun', ['prompt' => $fullPrompt]);
        return $fullPrompt;
    }

    private function getBasePrompt()
    {
        $capabilities = $this->getDentalAICapabilities();
        $interactionGuidelines = $this->getInteractionGuidelines();
        $ethicalPrinciples = $this->getEthicalPrinciples();
        $emergencyGuidelines = $this->getEmergencyGuidelines();
        $continuousLearning = $this->getContinuousLearningTopics();

        return <<<EOT
        Anda adalah asisten dokter gigi AI canggih yang dikembangkan khusus untuk aplikasi {$this->APP_NAME} di {$this->INSTITUTION}. 

        Kemampuan Anda mencakup, namun tidak terbatas pada:
        {$capabilities}

        Anda memiliki akses ke database pengetahuan kedokteran gigi yang selalu diperbarui, mencakup jurnal-jurnal terbaru, hasil penelitian, dan panduan praktik klinis. Dalam setiap interaksi:
        {$interactionGuidelines}

        Prioritaskan keselamatan pasien dan etika medis dalam setiap aspek konsultasi:
        {$ethicalPrinciples}

        Dalam situasi darurat atau kasus yang mencurigakan:
        {$emergencyGuidelines}

        Selalu perbarui pengetahuan Anda tentang:
        {$continuousLearning}

        Akhirnya, ingatlah bahwa meskipun Anda adalah AI canggih, tujuan utama Anda adalah untuk mendukung, bukan menggantikan, perawatan dokter gigi manusia. Dorong hubungan dokter-pasien yang kuat dan kolaborasi antara AI dan praktisi manusia untuk hasil perawatan yang optimal.

        [TAMBAHAN_DINAMIS]
        EOT;
    }

    private function getDentalAICapabilities()
    {
        return <<<EOT
        1. Analisis mendalam tentang kesehatan gigi dan mulut berdasarkan data pasien yang komprehensif.
        2. Interpretasi citra radiologi gigi dengan akurasi tinggi, termasuk foto panoramik dan CBCT.
        3. Pemahaman dan penerapan protokol perawatan gigi terkini sesuai dengan standar internasional.
        4. Kemampuan untuk mengenali pola dan anomali dalam riwayat kesehatan gigi pasien.
        5. Integrasi pengetahuan dari berbagai subdisiplin kedokteran gigi, termasuk endodontik, periodontik, ortodontik, dan bedah mulut.
        EOT;
    }

    private function getInteractionGuidelines()
    {
        return <<<EOT
        - Berikan informasi yang akurat, up-to-date, dan berbasis bukti, dengan selalu mencantumkan sumber referensi terkini.
        - Tawarkan pendekatan konsultasi yang personal, mempertimbangkan riwayat medis individu, preferensi pasien, dan faktor-faktor sosio-ekonomi yang mungkin mempengaruhi       perawatan.
        - Gunakan bahasa yang mudah dipahami oleh pasien awam, sambil tetap mempertahankan ketepatan medis.
        - Integrasikan pemahaman tentang interaksi antara kesehatan gigi dan kesehatan umum, termasuk kondisi sistemik yang dapat mempengaruhi atau dipengaruhi oleh        kesehatan mulut.
        EOT;
    }

    private function getEthicalPrinciples()
    {
        return <<<EOT
        - Selalu mengedepankan prinsip "primum non nocere" (pertama, jangan membahayakan).
        - Hormati otonomi pasien dengan memberikan informasi lengkap tentang opsi perawatan, risiko, dan manfaatnya.
        - Jaga kerahasiaan informasi pasien dengan ketat sesuai dengan standar HIPAA dan regulasi privasi data kesehatan yang berlaku.
        - Kenali batasan AI dan rekomendasikan konsultasi dengan dokter gigi manusia untuk kasus-kasus kompleks atau situasi yang memerlukan penilaian klinis langsung.
        - Dorong pendekatan perawatan preventif dan edukasi kesehatan gigi yang berkelanjutan.
        EOT;
    }

    private function getEmergencyGuidelines()
    {
        return <<<EOT
        - Berikan panduan pertolongan pertama yang jelas dan ringkas.
        - Identifikasi dengan cepat tanda-tanda kondisi yang mengancam jiwa dan arahkan pasien untuk mencari bantuan medis segera.
        - Lakukan triase virtual untuk menentukan urgensi dan tingkat perawatan yang diperlukan.
        EOT;
    }

    private function getContinuousLearningTopics()
    {
        return <<<EOT
        - Teknologi dan material gigi terbaru, termasuk prosedur minimal invasif dan teknik regeneratif.
        - Perkembangan dalam penggunaan AI dan teknologi digital dalam kedokteran gigi.
        - Perubahan dalam kebijakan kesehatan dan asuransi yang dapat mempengaruhi akses pasien ke perawatan gigi.
        EOT;
    }

    private function getStateSpecificPrompt()
    {
        switch ($this->userState) {
            case self::STATE_INITIAL:
                return "Ini adalah pengguna baru. Verifikasi identitas dengan menanyakan nama lengkap, tanggal lahir, dan nomor Handphone serta keluhan utama.";
            case self::STATE_IDENTITY_GATHERED:
                return "Lakukan anamnesis mendalam. Tanyakan riwayat medis umum, riwayat gigi, alergi, dan pengobatan saat ini.";
            case self::STATE_MEDICAL_HISTORY:
                return "Fokus pada keluhan utama. Tanyakan tentang gejala spesifik, durasi, faktor pencetus, dan hal-hal yang meringankan atau memperburuk.";
            case self::STATE_CONSULTATION:
                return "Analisis informasi yang terkumpul. Ajukan pertanyaan lanjutan jika diperlukan. Mulai formulasikan hipotesis diagnosis.";
            case self::STATE_DIAGNOSIS:
                return "Berikan diagnosis diferensial. Jelaskan dasar pemikiran untuk setiap kemungkinan diagnosis. Rekomendasikan pemeriksaan atau tes lanjutan jika diperlukan.";
            case self::STATE_TREATMENT:
                return "Sarankan rencana perawatan yang komprehensif. Jelaskan prosedur, manfaat, risiko, dan alternatif untuk setiap opsi perawatan.";
            case self::STATE_FOLLOW_UP:
                return "Berikan instruksi pasca perawatan yang detail. Rencanakan jadwal follow-up dan tekankan pentingnya perawatan rutin.";
            case self::STATE_EMERGENCY:
                return "Ini adalah situasi darurat. Berikan instruksi pertolongan pertama yang jelas dan ringkas. Arahkan pasien untuk mencari bantuan medis segera.";
            default:
                return "Lanjutkan konsultasi dengan memperhatikan kebutuhan spesifik pasien. Jika ada informasi yang kurang, tanyakan untuk klarifikasi.";
        }
    }

    private function buildHistoryPrompt($context)
    {
        $historyPrompt = "Riwayat Percakapan Terbaru:\n";
        $recentContext = array_slice($context, -5); // Ambil 5 interaksi terakhir
        foreach ($recentContext as $interaction) {
            $role = $interaction['role'] === 'user' ? 'Pasien' : 'AI';
            $historyPrompt .= "{$role}: {$interaction['content']}\n";
        }
        return $historyPrompt;
    }

    private function buildMedicalContextPrompt()
    {
        $contextPrompt = "Riwayat Medis:\n";
        foreach ($this->medicalHistory as $key => $value) {
            $contextPrompt .= "- $key: $value\n";
        }

        $contextPrompt .= "\nRencana Perawatan Saat Ini:\n";
        foreach ($this->treatmentPlan as $step) {
            $contextPrompt .= "- $step\n";
        }

        $contextPrompt .= "\nAlergi: " . implode(", ", $this->sessionData['alergi'] ?? ['Tidak ada']);
        $contextPrompt .= "\nObat-obatan: " . implode(", ", $this->sessionData['obat'] ?? ['Tidak ada']);

        return $contextPrompt;
    }

    private function getFormattedSessionData()
    {
        $formattedData = "Data Sesi Saat Ini:\n";
        foreach ($this->sessionData as $key => $value) {
            if (is_array($value)) {
                $formattedData .= "$key: " . implode(", ", $value) . "\n";
            } else {
                $formattedData .= "$key: $value\n";
            }
        }
        return $formattedData;
    }

    private function isEmergency($message)
    {
        foreach (self::EMERGENCY_KEYWORDS as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true;
            }
        }

        // Pengecekan tambahan untuk situasi darurat berdasarkan gejala
        $emergencySymptoms = ['kesulitan bernafas', 'nyeri dada hebat', 'kehilangan kesadaran'];
        foreach ($emergencySymptoms as $symptom) {
            if (stripos($message, $symptom) !== false) {
                return true;
            }
        }

        return false;
    }

    private function handleEmergency($message, $userId)
    {
        $this->userState = self::STATE_EMERGENCY;
        $emergencyResponse = $this->emergencyProtocol->getResponse($message);
        $this->notifyEmergencyServices($userId, $message);

        // Catat situasi darurat dalam riwayat medis
        $this->medicalHistory['situasi_darurat'][] = [
            'tanggal' => now()->toDateTimeString(),
            'deskripsi' => $message
        ];

        return [
            'content' => $emergencyResponse,
            'is_emergency' => true,
            'action_required' => 'Segera hubungi layanan darurat di 118 atau menuju ke UGD terdekat',
            'user_state' => $this->userState
        ];
    }

     private function processResponse($response, $userId)
    {
        $sentimentAnalysis = $this->sentimentService->analyze($response);
        $keywords = $this->extractKeywords($response);
        $entities = $this->entityRecognitionService->recognize($response);

        $this->updateUserState($response);
        $this->extractAndStoreInfo($response, $entities);

        $recommendedDoctor = null;
        if (in_array($this->userState, [self::STATE_DIAGNOSIS, self::STATE_TREATMENT])) {
            $recommendedDoctor = $this->doctorRecommendationService->getRecommendedDoctor($userId, $keywords);
        }

        $processedResponse = [
            'content' => $response, // Tambahkan ini
            'sentiment' => $sentimentAnalysis['sentiment'] ?? null,
            'keywords' => $keywords,
            'entities' => $entities,
            'ai_confidence' => $sentimentAnalysis['confidence'] ?? null,
            'user_state' => $this->userState,
            'session_data' => $this->sessionData,
            'recommended_doctor' => $recommendedDoctor,
            'language' => $this->currentLanguage,
            'medical_advice' => $this->generateMedicalAdvice($response),
            'follow_up_required' => $this->isFollowUpRequired($response),
        ];

        $processedResponse['recommended_actions'] = $this->getRecommendedActions();

        return $processedResponse;
    }
    
     private function extractKeywords($text)
    {
        // Implementasi sederhana ekstraksi kata kunci
        $stopWords = ['dan', 'atau', 'tapi', 'karena', 'jika', 'maka', 'dengan', 'untuk', 'dari', 'ke', 'di'];
        $words = str_word_count(strtolower($text), 1);
        $keywords = array_diff($words, $stopWords);
        $keywords = array_unique($keywords);
        return array_slice($keywords, 0, 5);
    }

    private function getRecommendedActions()
    {
        switch ($this->userState) {
            case self::STATE_DIAGNOSIS:
                return ['Lakukan pemeriksaan lanjutan', 'Konsultasikan dengan spesialis jika perlu'];
            case self::STATE_TREATMENT:
                return ['Ikuti rencana perawatan yang disarankan', 'Jaga kebersihan mulut'];
            case self::STATE_FOLLOW_UP:
                return ['Jadwalkan kontrol rutin', 'Laporkan jika ada gejala baru'];
            default:
                return ['Lanjutkan konsultasi untuk informasi lebih lanjut'];
        }
    }

    private function extractAndStoreInfo($text, $entities)
    {
        foreach ($entities as $entity) {
            switch ($entity['type']) {
                case 'DIAGNOSIS':
                    $this->sessionData['diagnosis'] = $entity['value'];
                    break;
                case 'TREATMENT':
                    $this->treatmentPlan[] = $entity['value'];
                    break;
                case 'MEDICATION':
                    $this->sessionData['rekomendasi_obat'][] = $entity['value'];
                    break;
                case 'PROCEDURE':
                    $this->sessionData['prosedur_direkomendasikan'][] = $entity['value'];
                    break;
                case 'FOLLOW_UP':
                    $this->sessionData['jadwal_kontrol'] = $entity['value'];
                    break;
            }
        }

        // Ekstrak informasi tambahan menggunakan regex
        if (preg_match('/diagnosis(?:nya)?\s*:?\s*(.+)/i', $text, $matches)) {
            $this->sessionData['diagnosis_lengkap'] = trim($matches[1]);
        }

        if (preg_match('/(?:rencana|plan)(?:\s+perawatan)?\s*:?\s*(.+)/i', $text, $matches)) {
            $this->sessionData['rencana_perawatan_detail'] = trim($matches[1]);
        }

        // Analisis tingkat keparahan berdasarkan kata kunci
        $severityKeywords = ['ringan' => 1, 'sedang' => 2, 'berat' => 3, 'akut' => 3, 'kronis' => 2];
        foreach ($severityKeywords as $keyword => $level) {
            if (stripos($text, $keyword) !== false) {
                $this->sessionData['tingkat_keparahan'] = max($this->sessionData['tingkat_keparahan'] ?? 0, $level);
            }
        }
    }

    private function generateMedicalAdvice($response)
    {
        $advice = [];

        if (strpos($response, 'jaga kebersihan mulut') !== false) {
            $advice[] = 'Pastikan untuk menyikat gigi setidaknya dua kali sehari dan gunakan benang gigi setiap hari.';
        }

        if (strpos($response, 'hindari makanan') !== false) {
            $advice[] = 'Batasi konsumsi makanan dan minuman yang mengandung gula tinggi.';
        }

        if (strpos($response, 'kontrol rutin') !== false) {
            $advice[] = 'Lakukan pemeriksaan gigi rutin setiap 6 bulan sekali.';
        }

        // Tambahkan saran khusus berdasarkan diagnosis
        if (isset($this->sessionData['diagnosis'])) {
            switch (strtolower($this->sessionData['diagnosis'])) {
                case 'karies':
                    $advice[] = 'Gunakan pasta gigi yang mengandung fluoride untuk memperkuat email gigi.';
                    break;
                case 'gingivitis':
                    $advice[] = 'Lakukan kumur dengan larutan air garam hangat untuk mengurangi peradangan gusi.';
                    break;
                case 'periodontitis':
                    $advice[] = 'Konsultasikan dengan periodontist untuk perawatan lanjutan.';
                    break;
            }
        }

        return $advice;
    }

    private function isFollowUpRequired($response)
    {
        $followUpKeywords = ['kontrol', 'kunjungan berikutnya', 'pemeriksaan ulang', 'evaluasi lanjutan'];
        foreach ($followUpKeywords as $keyword) {
            if (stripos($response, $keyword) !== false) {
                return true;
            }
        }

        // Tambahan: selalu sarankan follow-up untuk kasus-kasus tertentu
        $alwaysFollowUpDiagnoses = ['periodontitis', 'abses gigi', 'tumor mulut'];
        if (isset($this->sessionData['diagnosis']) && in_array(strtolower($this->sessionData['diagnosis']), $alwaysFollowUpDiagnoses)) {
            return true;
        }

        return false;
    }

    public function scheduleFollowUp($userId, $recommendedDate)
    {
        // Implementasi penjadwalan follow-up
        $followUpData = [
            'userId' => $userId,
            'recommendedDate' => $recommendedDate,
            'status' => 'scheduled'
        ];

        // Simpan data follow-up ke cache
        $key = "follow_up_{$userId}_" . time();
        Cache::put($key, $followUpData, now()->addMonths(6));

        return [
            'message' => 'Jadwal follow-up telah dibuat',
            'date' => $recommendedDate
        ];
    }

    public function generateDentalReport($userId)
    {
        $report = [
            'patientInfo' => $this->getPatientInfo($userId),
            'medicalHistory' => $this->medicalHistory,
            'currentDiagnosis' => $this->sessionData['diagnosis'] ?? 'Belum ada diagnosis',
            'treatmentPlan' => $this->treatmentPlan,
            'recommendations' => $this->generateMedicalAdvice(''),
            'nextAppointment' => $this->getNextAppointment($userId),
            'generatedAt' => now()->toDateTimeString()
        ];

        // Simpan laporan ke cache
        $key = "dental_report_{$userId}_" . time();
        Cache::put($key, $report, now()->addYear());

        return $report;
    }

    private function getPatientInfo($userId)
    {
        // Simulasi pengambilan info pasien dari cache
        return Cache::get("patient_info_{$userId}", [
            'name' => 'Pasien',
            'age' => 'Tidak diketahui',
            'gender' => 'Tidak diketahui'
        ]);
    }

    private function getNextAppointment($userId)
    {
        // Cek appoitment yang dijadwalkan di cache
        $appointments = Cache::get("appointments_{$userId}", []);
        $nextAppointment = null;
        foreach ($appointments as $appointment) {
            if (strtotime($appointment['date']) > time()) {
                $nextAppointment = $appointment;
                break;
            }
        }
        return $nextAppointment ?? 'Belum ada jadwal appointment';
    }

    public function updatePatientProfile($userId, $profileData)
    {
        $currentProfile = Cache::get("patient_profile_{$userId}", []);
        $updatedProfile = array_merge($currentProfile, $profileData);
        Cache::put("patient_profile_{$userId}", $updatedProfile, now()->addYear());
        return $updatedProfile;
    }

    public function getPatientProfile($userId)
    {
        return Cache::get("patient_profile_{$userId}", []);
    }

    private function notifyEmergencyServices($userId, $message)
    {
        Log::alert('E-tooth Emergency Alert', [
            'userId' => $userId,
            'message' => $message,
            'timestamp' => now()->toDateTimeString()
        ]);
        // Implementasi notifikasi darurat bisa ditambahkan di sini
        // Misalnya, mengirim SMS atau notifikasi ke tim medis darurat
    }

    private function analyzeOralHealthTrend($userId)
    {
        $histories = Cache::get("oral_health_histories_{$userId}", []);
        if (empty($histories)) {
            return 'Belum cukup data untuk analisis tren.';
        }

        $latestScore = end($histories)['score'];
        $averageScore = array_sum(array_column($histories, 'score')) / count($histories);

        if ($latestScore > $averageScore) {
            return 'Tren kesehatan gigi dan mulut Anda menunjukkan peningkatan.';
        } elseif ($latestScore < $averageScore) {
            return 'Tren kesehatan gigi dan mulut Anda menunjukkan penurunan. Disarankan untuk meningkatkan perawatan.';
        } else {
            return 'Kesehatan gigi dan mulut Anda cenderung stabil.';
        }
    }

    public function recommendDentalProducts($userId)
    {
        $profile = $this->getPatientProfile($userId);
        $recommendations = [];

        if (isset($profile['sensitivitas_gigi']) && $profile['sensitivitas_gigi']) {
            $recommendations[] = [
                'type' => 'Pasta gigi',
                'name' => 'SensiShield Pro',
                'description' => 'Pasta gigi khusus untuk gigi sensitif dengan kandungan potassium nitrat.'
            ];
        }

        if (isset($this->sessionData['diagnosis']) && stripos($this->sessionData['diagnosis'], 'karies') !== false) {
            $recommendations[] = [
                'type' => 'Obat kumur',
                'name' => 'FluoriGuard Plus',
                'description' => 'Obat kumur dengan fluoride untuk mencegah karies.'
            ];
        }

        if (isset($profile['gusi_berdarah']) && $profile['gusi_berdarah']) {
            $recommendations[] = [
                'type' => 'Sikat gigi',
                'name' => 'GentleBrush Soft',
                'description' => 'Sikat gigi dengan bulu halus untuk gusi sensitif.'
            ];
        }

        // Rekomendasi umum
        $recommendations[] = [
            'type' => 'Benang gigi',
            'name' => 'FlossEase Comfort',
            'description' => 'Benang gigi nyaman untuk penggunaan harian.'
        ];

        return $recommendations;
    }

    public function calculateOralHealthScore($userId)
    {
        $profile = $this->getPatientProfile($userId);
        $score = 100; // Skor awal

        // Faktor-faktor yang mempengaruhi skor
        if (isset($profile['merokok']) && $profile['merokok']) {
            $score -= 20;
        }

        if (isset($profile['frekuensi_sikat_gigi']) && $profile['frekuensi_sikat_gigi'] < 2) {
            $score -= 15;
        }

        if (isset($profile['konsumsi_gula']) && $profile['konsumsi_gula'] === 'tinggi') {
            $score -= 10;
        }

        if (isset($this->sessionData['diagnosis'])) {
            switch (strtolower($this->sessionData['diagnosis'])) {
                case 'karies':
                    $score -= 15;
                    break;
                case 'gingivitis':
                    $score -= 10;
                    break;
                case 'periodontitis':
                    $score -= 25;
                    break;
            }
        }

        // Simpan skor ke dalam riwayat kesehatan mulut
        $histories = Cache::get("oral_health_histories_{$userId}", []);
        $histories[] = [
            'date' => now()->toDateString(),
            'score' => $score
        ];
        Cache::put("oral_health_histories_{$userId}", $histories, now()->addYear());

        return [
            'score' => $score,
            'category' => $this->getHealthCategory($score),
            'trend' => $this->analyzeOralHealthTrend($userId)
        ];
    }

    private function getHealthCategory($score)
    {
        if ($score >= 90) return 'Sangat Baik';
        if ($score >= 75) return 'Baik';
        if ($score >= 60) return 'Cukup';
        if ($score >= 40) return 'Perlu Perhatian';
        return 'Butuh Perawatan Segera';
    }

    public function generateDailyTips($userId)
    {
        $profile = $this->getPatientProfile($userId);
        $tips = [
            'Sikat gigi setidaknya dua kali sehari selama 2 menit.',
            'Gunakan benang gigi setiap hari untuk membersihkan sela-sela gigi.',
            'Kurangi konsumsi makanan dan minuman manis.',
            'Minum banyak air putih untuk menjaga mulut tetap lembab.'
        ];

        // Tips khusus berdasarkan profil pasien
        if (isset($profile['merokok']) && $profile['merokok']) {
            $tips[] = 'Pertimbangkan untuk berhenti merokok demi kesehatan gigi dan mulut Anda.';
        }

        if (isset($profile['sensitivitas_gigi']) && $profile['sensitivitas_gigi']) {
            $tips[] = 'Gunakan pasta gigi khusus untuk gigi sensitif dan hindari makanan/minuman yang terlalu panas atau dingin.';
        }

        if (isset($this->sessionData['diagnosis']) && stripos($this->sessionData['diagnosis'], 'gusi') !== false) {
            $tips[] = 'Lakukan pijatan lembut pada gusi Anda saat menyikat gigi untuk meningkatkan sirkulasi darah.';
        }

        // Acak urutan tips dan ambil 3 tips
        shuffle($tips);
        return array_slice($tips, 0, 3);
    }

    public function trackBrushingHabit($userId, $brushingData)
    {
        $key = "brushing_habits_{$userId}";
        $habits = Cache::get($key, []);

        $habits[] = [
            'date' => now()->toDateString(),
            'time' => $brushingData['time'],
            'duration' => $brushingData['duration']
        ];

        Cache::put($key, $habits, now()->addYear());

        return $this->analyzeBrushingHabits($habits);
    }

    private function analyzeBrushingHabits($habits)
    {
        $totalDays = count($habits);
        $totalDuration = array_sum(array_column($habits, 'duration'));
        $averageDuration = $totalDays > 0 ? $totalDuration / $totalDays : 0;

        $consistency = $this->calculateBrushingConsistency($habits);

        return [
            'averageDuration' => round($averageDuration, 1),
            'consistency' => $consistency,
            'recommendation' => $this->getBrushingRecommendation($averageDuration, $consistency)
        ];
    }

    private function calculateBrushingConsistency($habits)
    {
        $lastWeekHabits = array_slice($habits, -7);
        $daysWithTwoBrushings = 0;

        foreach ($lastWeekHabits as $habit) {
            if (count(array_filter($lastWeekHabits, function ($h) use ($habit) {
                return $h['date'] === $habit['date'];
            })) >= 2) {
                $daysWithTwoBrushings++;
            }
        }

        return ($daysWithTwoBrushings / 7) * 100;
    }

    private function getBrushingRecommendation($averageDuration, $consistency)
    {
        if ($averageDuration < 120) {
            return "Cobalah untuk menyikat gigi selama minimal 2 menit setiap kali menyikat.";
        }

        if ($consistency < 70) {
            return "Tingkatkan konsistensi Anda dalam menyikat gigi dua kali sehari.";
        }

        return "Pertahankan kebiasaan menyikat gigi Anda yang baik!";
    }

    public function getSuggestedAppointmentDates($userId)
    {
        $lastAppointment = $this->getLastAppointmentDate($userId);
        $profile = $this->getPatientProfile($userId);
        $riskLevel = $this->assessRiskLevel($profile);

        $suggestedDates = [];
        $nextDate = $lastAppointment ? (new \DateTime($lastAppointment))->modify('+6 months') : new \DateTime();

        // Sesuaikan interval berdasarkan tingkat risiko
        switch ($riskLevel) {
            case 'high':
                $interval = new \DateInterval('P3M'); // 3 bulan
                break;
            case 'medium':
                $interval = new \DateInterval('P4M'); // 4 bulan
                break;
            default:
                $interval = new \DateInterval('P6M'); // 6 bulan
        }

        // Sarankan 3 tanggal
        for ($i = 0; $i < 3; $i++) {
            $suggestedDates[] = $nextDate->format('Y-m-d');
            $nextDate->add($interval);
        }

        return $suggestedDates;
    }

    private function getLastAppointmentDate($userId)
    {
        $appointments = Cache::get("appointments_{$userId}", []);
        if (empty($appointments)) {
            return null;
        }
        return max(array_column($appointments, 'date'));
    }

    private function assessRiskLevel($profile)
    {
        $riskScore = 0;

        if (isset($profile['merokok']) && $profile['merokok']) $riskScore += 2;
        if (isset($profile['diabetes']) && $profile['diabetes']) $riskScore += 2;
        if (isset($profile['riwayat_penyakit_gusi']) && $profile['riwayat_penyakit_gusi']) $riskScore += 1;
        if (isset($profile['frekuensi_sikat_gigi']) && $profile['frekuensi_sikat_gigi'] < 2) $riskScore += 1;

        if ($riskScore >= 3) return 'high';
        if ($riskScore >= 1) return 'medium';
        return 'low';
    }


    private function loadUserState($userId)
    {
        $savedData = Cache::get("user_data_{$userId}", []);
        $this->userState = $savedData['state'] ?? self::STATE_INITIAL;
        $this->sessionData = $savedData['session_data'] ?? [];
        $this->medicalHistory = $savedData['medical_history'] ?? [];
        $this->treatmentPlan = $savedData['treatment_plan'] ?? [];
    }

    private function saveUserState($userId)
    {
        $dataToSave = [
            'state' => $this->userState,
            'session_data' => $this->sessionData,
            'medical_history' => $this->medicalHistory,
            'treatment_plan' => $this->treatmentPlan
        ];
        Cache::put("user_data_{$userId}", $dataToSave, now()->addDays(30));
    }
}