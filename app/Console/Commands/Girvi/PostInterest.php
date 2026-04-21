<?php

namespace App\Console\Commands\Girvi;

use App\Services\GirviService;
use Illuminate\Console\Command;

class PostInterest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'girvi:post-interest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically post interest for all active Girvi loans based on their cycle';

    protected $girviService;

    public function __construct(GirviService $girviService)
    {
        parent::__construct();
        $this->girviService = $girviService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automatic Girvi interest posting...');

        $count = $this->girviService->postInterestForAllActive();

        $this->info("Successfully posted interest for {$count} Girvi accounts.");
    }
}
