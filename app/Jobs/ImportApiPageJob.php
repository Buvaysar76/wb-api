<?php

namespace App\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use Throwable;

class ImportApiPageJob implements ShouldQueue
{
    use Queueable;

    protected string $url;
    protected string $model;
    protected array $params;
    protected int $accountId;
    protected int $maxRetries = 7;

    public function __construct(string $url, string $model, array $params, int $accountId)
    {
        $this->url = $url;
        $this->model = $model;
        $this->params = $params;
        $this->accountId = $accountId;
    }

    /**
     * @throws Throwable
     * @throws JsonException
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $attempt = 0;

        request_loop:
        $attempt++;

        Log::info("Импорт: {$this->model}, страница {$this->params['page']}, попытка {$attempt}");

        $response = Http::timeout(30)->get($this->url, $this->params);

        if ($response->status() === 429) {
            $delay = min(2 ** $attempt, 30);

            Log::warning("API вернуло 429 Too Many Requests. Повтор через {$delay} сек.");

            if ($attempt < $this->maxRetries) {
                sleep($delay);
                goto request_loop;
            }

            Log::error("Превышено число попыток для {$this->url}");
            return;
        }

        if ($response->serverError() || $response->failed()) {
            $status = $response->status();
            Log::error("Ошибка API {$status}. Параметры: " . json_encode($this->params, JSON_THROW_ON_ERROR));

            if ($attempt < $this->maxRetries) {
                $delay = min(2 ** $attempt, 20);
                Log::info("Ошибка сервера, retry через {$delay} сек.");
                sleep($delay);
                goto request_loop;
            }

            return;
        }

        Log::info("Ответ API получен. Обработка данных...");

        $data = $response->json('data') ?? [];
        $dataChunks = array_chunk($data, 250);
        $currentTime = now()->toDateTimeString();

        foreach ($dataChunks as $chunk) {
            try {
                $prepared = array_map(fn($item) => array_merge($item, [
                    'account_id' => $this->accountId,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime,
                ]), $chunk);

                $uniqueMap = [
                    \App\Models\Order::class  => ['account_id', 'odid'],
                    \App\Models\Sale::class   => ['account_id', 'sale_id'],
                    \App\Models\Income::class => ['account_id', 'income_id'],
                    \App\Models\Stock::class  => ['account_id', 'date', 'warehouse_name', 'barcode', 'nm_id'],
                ];

                $uniqueBy = $uniqueMap[$this->model];

                $updateColumns = array_diff(array_keys($prepared[0]), ['id', 'created_at']);

                DB::transaction(function () use ($prepared, $uniqueBy, $updateColumns) {
                    $this->model::upsert($prepared, $uniqueBy, $updateColumns);
                });
            } catch (Exception $e) {
                Log::error("Ошибка вставки данных: {$e->getMessage()}");
            }
        }

        $lastPage = $response->json('meta.last_page') ?? 1;
        $currentPage = $this->params['page'] ?? 1;

        Log::info("Страница {$currentPage} обработана из {$lastPage}");

        if ($currentPage < $lastPage) {
            $this->params['page'] = $currentPage + 1;

            Log::info("Отправляем следующую страницу {$this->params['page']}");

            self::dispatch($this->url, $this->model, $this->params, $this->accountId)
                ->delay(now()->addSeconds(1));
        } else {
            Log::info("✅ Импорт завершён: {$this->model} для account_id={$this->accountId}");
        }
    }
}
