<?php

namespace App\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportApiPageJob implements ShouldQueue
{
    use Queueable;

    protected string $url;
    protected string $model;
    protected array $params;

    /**
     * Create a new job instance.
     */
    public function __construct(string $url, string $model, array $params)
    {
        $this->url = $url;
        $this->model = $model;
        $this->params = $params;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = Http::get($this->url, $this->params);

        if ($response->failed()) {
            Log::warning("Ошибка API. Статус: {$response->status()}, Параметры: " . json_encode($this->params));
            return;
        }

        $data = $response->json('data') ?? [];
        $dataChunks = array_chunk($data, 250);
        $currentTime = now()->toDateTimeString();

        foreach ($dataChunks as $chunk) {
            try {
                DB::transaction(fn() => $this->model::insert(
                    array_map(fn($item) => array_merge($item, [
                        'created_at' => $currentTime,
                        'updated_at' => $currentTime,
                    ]), $chunk)
                ), 5);
            } catch (Exception $e) {
                Log::error("Ошибка вставки данных: {$e->getMessage()}");
            }
        }

        $lastPage = $response->json('meta.last_page') ?? 1;
        $currentPage = $this->params['page'] ?? 1;

        if ($currentPage < $lastPage) {
            $this->params['page'] = $currentPage + 1;
            self::dispatch($this->url, $this->model, $this->params)
                ->delay(now()->addSeconds(1));
        }
    }
}
