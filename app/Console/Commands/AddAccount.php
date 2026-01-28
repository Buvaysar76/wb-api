<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Console\Command;

class AddAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-account {company_id} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавить аккаунт к компании';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $companyId = $this->argument('company_id');
        $name = $this->argument('name');

        $company = Company::find($companyId);

        if (!$company) {
            $this->error("Компания ID {$companyId} не найдена.");
            return;
        }

        $account = Account::firstOrCreate([
            'company_id' => $companyId,
            'name' => $name,
        ]);

        if ($account->wasRecentlyCreated) {
            $this->info("Аккаунт создан. ID = {$account->id}");
        } else {
            $this->warn("Аккаунт уже существует у компании {$company->name}. ID = {$account->id}");
        }
    }
}
