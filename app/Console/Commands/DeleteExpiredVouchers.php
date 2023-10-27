<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\Voucher;

class DeleteExpiredVouchers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vouchers:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all expired Vouchers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get current datetime
        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');

        // Delete expired vouchers
        Voucher::where('endTime', '<=', $currentDateTime)->delete();

        $this->info('Expired vouchers deleted successfully.');
    }
}
