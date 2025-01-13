<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\OAuthService;
use Illuminate\Console\Command;

class RemoveExpiredTokenCommand extends Command
{
    public function __construct(private readonly OAuthService $oAuthService)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oauth:remove-expired-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired access tokens';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->oAuthService->removeExpiredTokens();
    }
}
