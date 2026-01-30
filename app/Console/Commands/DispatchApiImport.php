<?php

namespace App\Console\Commands;

use App\Jobs\ImportApiPageJob;
use App\Models\Account;
use App\Models\AccountToken;
use App\Models\Income;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DispatchApiImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-api-data
                            {--type= : Тип импорта (stocks, orders, sales, incomes)}
                            {--account=1 : ID аккаунта}
                            {--dateFrom= : Дата начала}
                            {--dateTo= : Дата конца}';

    protected $description = 'Import data from API';

    private const API_BASE_URL = 'http://109.73.206.144:6969/api';

    private const IMPORT_TYPES = [
        'stocks' => [
            'endpoint' => '/stocks',
            'model' => Stock::class,
            'uses_dates' => false,
        ],
        'orders' => [
            'endpoint' => '/orders',
            'model' => Order::class,
            'uses_dates' => true,
        ],
        'sales' => [
            'endpoint' => '/sales',
            'model' => Sale::class,
            'uses_dates' => true,
        ],
        'incomes' => [
            'endpoint' => '/incomes',
            'model' => Income::class,
            'uses_dates' => true,
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $type = $this->option('type');
        $accountId = (int)$this->option('account');

        if (!$type || !isset(self::IMPORT_TYPES[$type])) {
            $this->error('Неизвестный тип импорта. Допустимые значения: stocks, orders, sales, incomes');
            return;
        }

        $account = Account::find($accountId);
        if (!$account) {
            $this->error("Аккаунт {$accountId} не найден");
            return;
        }

        $token = AccountToken::where('account_id', $accountId)->value('value');
        if (!$token) {
            $this->error("Для аккаунта {$accountId} не найден токен");
            return;
        }

        $config = self::IMPORT_TYPES[$type];
        $modelClass = $config['model'];

        $dateFrom = $this->option('dateFrom');
        $dateTo   = $this->option('dateTo');

        if ($config['uses_dates'] && (!$dateFrom || !$dateTo)) {
            $latest = $modelClass::where('account_id', $accountId)->max('date');

            $dateFrom = $latest ? Carbon::parse($latest)->format('Y-m-d') : now()->subDays(3)->format('Y-m-d');
            $dateTo = now()->format('Y-m-d');
        }

        $params = [];

        if ($config['uses_dates']) {
            $params['dateFrom'] = $dateFrom;
            $params['dateTo']   = $dateTo;
        } else {
            // stocks
            $params['dateFrom'] = now()->format('Y-m-d');
        }

        $this->fetchApiData(
            self::API_BASE_URL . $config['endpoint'],
            $modelClass,
            $token,
            $accountId,
            $params
        );

        $this->info("Импорт '{$type}' для account_id={$accountId} успешно отправлен в очередь.");
    }

    private function fetchApiData(string $url, string $modelClass, string $token, int $accountId, array $extraParams = []): void
    {
        $params = array_merge([
            'key' => $token,
            'page' => 1,
            'limit' => 500,
        ], $extraParams);

        ImportApiPageJob::dispatch($url, $modelClass, $params, $accountId);
    }
}
