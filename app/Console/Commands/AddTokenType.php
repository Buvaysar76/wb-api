<?php

namespace App\Console\Commands;

use App\Models\TokenType;
use Illuminate\Console\Command;

class AddTokenType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-token-type {code} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавить новый тип токена';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $type = TokenType::create([
            'code' => $this->argument('code'),
            'name' => $this->argument('name'),
        ]);

        $this->info("Тип токена создан. ID = {$type->id}");
    }
}
