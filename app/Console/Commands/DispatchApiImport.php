<?php

namespace App\Console\Commands;

use App\Jobs\ImportApiPageJob;
use App\Models\Income;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
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
                            {--dateFrom= : Дата начала}
                            {--dateTo= : Дата конца}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from API';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $type = $this->option('type');
        $dateFrom = $this->option('dateFrom') ?? now()->subDays(7)->format('Y-m-d');
        $dateTo = $this->option('dateTo') ?? now()->format('Y-m-d');

        if (!$type) {
            $this->error('Не указан тип импорта. Допустимые значения: stocks, orders, sales, incomes');
            return;
        }

        switch (strtolower($type)) {
            case 'stocks':
                $this->dispatchStocks();
                break;
            case 'orders':
                $this->dispatchOrders($dateFrom, $dateTo);
                break;
            case 'sales':
                $this->dispatchSales($dateFrom, $dateTo);
                break;
            case 'incomes':
                $this->dispatchIncomes($dateFrom, $dateTo);
                break;
            default:
                $this->error('Неизвестный тип импорта. Допустимые значения: stocks, orders, sales, incomes');
                return;
        }

        $this->info("Импорт '$type' успешно отправлен в очередь.");
    }

    private function dispatchStocks(): void
    {
        $this->fetchApiData(
            'http://109.73.206.144:6969/api/stocks',
            Stock::class,
            ['dateFrom' => now()->subDay()->format('Y-m-d')]
        );
    }

    private function dispatchOrders(string $dateFrom, string $dateTo): void
    {
        $this->fetchApiData(
            'http://109.73.206.144:6969/api/orders',
            Order::class,
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo]
        );
    }

    private function dispatchSales(string $dateFrom, string $dateTo): void
    {
        $this->fetchApiData(
            'http://109.73.206.144:6969/api/sales',
            Sale::class,
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo]
        );
    }

    private function dispatchIncomes(string $dateFrom, string $dateTo): void
    {
        $this->fetchApiData(
            'http://109.73.206.144:6969/api/incomes',
            Income::class,
            ['dateFrom' => $dateFrom, 'dateTo' => $dateTo]
        );
    }

    private function fetchApiData(string $url, string $modelClass, array $extraParams = []): void
    {
        $params = array_merge([
            'key' => config('services.api.key'),
            'page' => 1,
            'limit' => 500,
        ], $extraParams);

        ImportApiPageJob::dispatch($url, $modelClass, $params);
    }
}
