<?php

namespace App\Console\Commands;

use App\Models\ApiService;
use Illuminate\Console\Command;

class AddApiService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-api-service {code} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавить API сервис';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $service = ApiService::create([
            'code' => $this->argument('code'),
            'name' => $this->argument('name'),
        ]);

        $this->info("API сервис создан. ID = {$service->id}");
    }
}
