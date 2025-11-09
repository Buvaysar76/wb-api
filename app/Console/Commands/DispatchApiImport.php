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

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $type = $this->option('type');
        $accountId = (int)$this->option('account');

        if (!$type) {
            $this->error('Не указан тип импорта. Допустимые значения: stocks, orders, sales, incomes');
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

        $dateFrom = $this->option('dateFrom');
        $dateTo = $this->option('dateTo');

        $hasUserDates = $dateFrom && $dateTo;

        if (!$hasUserDates) {
            $model = match ($type) {
                'stocks' => Stock::class,
                'orders' => Order::class,
                'sales'  => Sale::class,
                'incomes'=> Income::class,
            };

            $latest = $model::where('account_id', $accountId)->max('date');

            $dateFrom = $latest ? Carbon::parse($latest)->format('Y-m-d') : now()->subDays(3)->format('Y-m-d');
            $dateTo = now()->format('Y-m-d');
        }

        match ($type) {
            'stocks' => $this->dispatchStocks($token, $accountId),
            'orders' => $this->dispatchOrders($token, $accountId, $dateFrom, $dateTo),
            'sales' => $this->dispatchSales($token, $accountId, $dateFrom, $dateTo),
            'incomes' => $this->dispatchIncomes($token, $accountId, $dateFrom, $dateTo),
            default => $this->error('Неизвестный тип импорта. Допустимые значения: stocks, orders, sales, incomes'),
        };

        $this->info("Импорт '$type' для account_id={$accountId} успешно отправлен в очередь.");
    }

    private function dispatchStocks(string $token, int $accountId): void
    {
        $this->fetchApiData(
            'http://109.73.206.144:6969/api/stocks',
            Stock::class,
            $token,
            $accountId,
            ['dateFrom' => now()->format('Y-m-d')]
        );
    }

    private function dispatchOrders(string $token, int $accountId, string $dateFrom, string $dateTo): void
    {
        $this->fetchApiData(
            'http://109.73.206.144:6969/api/orders',
            Order::class,
            $token,
            $accountId,
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo]
        );
    }

    private function dispatchSales(string $token, int $accountId, string $dateFrom, string $dateTo): void
    {
        $this->fetchApiData(
            'http://109.73.206.144:6969/api/sales',
            Sale::class,
            $token,
            $accountId,
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo]
        );
    }

    private function dispatchIncomes(string $token, int $accountId, string $dateFrom, string $dateTo): void
    {
        $this->fetchApiData(
            'http://109.73.206.144:6969/api/incomes',
            Income::class,
            $token,
            $accountId,
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo]
        );
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
