<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AccountToken;
use App\Models\ApiService;
use App\Models\TokenType;
use Illuminate\Console\Command;

class AddAccountToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-account-token
                            {account_id}
                            {api_service_id}
                            {token_type_id}
                            {value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавить токен аккаунту';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountId = $this->argument('account_id');
        $serviceId = $this->argument('api_service_id');
        $typeId = $this->argument('token_type_id');
        $value = $this->argument('value');

        if (!Account::find($accountId)) {
            $this->error("Аккаунт не найден.");
            return;
        }

        if (!ApiService::find($serviceId)) {
            $this->error("API сервис не найден.");
            return;
        }

        if (!TokenType::find($typeId)) {
            $this->error("Тип токена не найден.");
            return;
        }

        if (
            AccountToken::where('account_id', $accountId)
                ->where('api_service_id', $serviceId)
                ->where('token_type_id', $typeId)
                ->exists()
        ) {
            $this->error("У этого аккаунта уже есть токен такого типа для этого API.");
            return;
        }

        $token = AccountToken::create([
            'account_id' => $accountId,
            'api_service_id' => $serviceId,
            'token_type_id' => $typeId,
            'value' => $value,
        ]);

        $this->info("Токен добавлен. ID = {$token->id}");
    }
}
