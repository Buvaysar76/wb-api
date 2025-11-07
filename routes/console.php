<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:import-api-data --type=orders')->twiceDaily(8, 20);
Schedule::command('app:import-api-data --type=sales')->twiceDaily(8, 20);
Schedule::command('app:import-api-data --type=stocks')->twiceDaily(8, 20);
Schedule::command('app:import-api-data --type=incomes')->twiceDaily(8, 20);
