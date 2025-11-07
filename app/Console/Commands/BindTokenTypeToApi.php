<?php

namespace App\Console\Commands;

use App\Models\ApiService;
use App\Models\TokenType;
use Illuminate\Console\Command;

class BindTokenTypeToApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:bind-token-type {api_service_id} {token_type_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Назначить тип токена API сервису';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $serviceId = $this->argument('api_service_id');
        $typeId = $this->argument('token_type_id');

        $service = ApiService::find($serviceId);
        $type = TokenType::find($typeId);

        if (!$service || !$type) {
            $this->error('API сервис или тип токена не найден.');
            return;
        }

        $service->tokenTypes()->syncWithoutDetaching([$typeId]);

        $this->info("Тип токена {$type->name} привязан к API сервису {$service->name}");
    }
}
