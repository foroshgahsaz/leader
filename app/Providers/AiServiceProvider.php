<?php

namespace App\Providers;

use App\Contracts\AiAssistant\OpenAiClientInterface;
use App\Services\AiAssistant\FakeOpenAiClient;
use App\Services\AiAssistant\OpenAiClient;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OpenAiClientInterface::class, function ($app) {
            if ($app->environment('testing')) {
                return new FakeOpenAiClient;
            }

            return new OpenAiClient;
        });
    }
}
