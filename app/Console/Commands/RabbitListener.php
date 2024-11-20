<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\RabbitMQ;

class RabbitListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mq:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listening on RabbitMQ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rabbit = new RabbitMQ();
        $rabbit->consume("task");
    }
}
