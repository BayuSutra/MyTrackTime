<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mqtt:listen', function () {
    $this->info('========================================');
    $this->info('🚀 MQTT LISTENER STARTED');
    $this->info('========================================');
    $this->info('📡 Broker: ' . config('mqtt.connections.default.host'));
    $this->info('👂 Listening for GPS data...');
    $this->info('========================================');
    
    try {
        $mqttService = app(\App\Services\MQTTService::class);
        $mqttService->listen();
    } catch (\Exception $e) {
        $this->error('❌ Error: ' . $e->getMessage());
        return 1;
    }
})->purpose('Listen to MQTT broker for GPS data from ESP32 devices');