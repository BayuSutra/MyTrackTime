<?php

namespace App\Console\Commands;

use App\Services\MQTTService;
use Illuminate\Console\Command;

class MQTTListenCommand extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Listen to MQTT broker for GPS data from devices';

    protected $mqttService;

    public function __construct(MQTTService $mqttService)
    {
        parent::__construct();
        $this->mqttService = $mqttService;
    }

    public function handle()
    {
        $this->info('🚀 MQTT Listener Started');
        $this->info('📡 Connected to: ' . config('mqtt.connections.default.host'));
        $this->info('👂 Listening for GPS data...');
        $this->line('----------------------------------------');

        try {
            $this->mqttService->listen();
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}