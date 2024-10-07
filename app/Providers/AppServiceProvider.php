<?php

namespace App\Providers;

use App\Services\AIService;
use Illuminate\Support\ServiceProvider;
use App\Services\EntityRecognitionService;
use App\Services\LanguageDetectionService;
use App\Services\SentimentAnalysisService;
use App\Services\DoctorRecommendationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind('ChatifyMessenger', function ($app) {
            return new \App\Chatify\ChatifyMessenger();
        });

        $this->app->singleton(AIService::class, function ($app) {
            return new AIService(
                $app->make(SentimentAnalysisService::class),
                $app->make(DoctorRecommendationService::class),
                $app->make(EntityRecognitionService::class),
                $app->make(LanguageDetectionService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set(config('app.timezone'));
    }
}