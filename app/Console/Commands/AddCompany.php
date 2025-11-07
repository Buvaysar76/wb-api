<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class AddCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-company {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавить новую компанию';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        $company = Company::create(['name' => $name]);

        $this->info("Компания создана. ID = {$company->id}");
    }
}
