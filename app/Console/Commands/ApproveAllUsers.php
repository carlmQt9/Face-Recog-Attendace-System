<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ApproveAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:approve-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Approve all existing users (useful after adding approval system)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Approving all existing users...');

        $users = User::where(function($query) {
            $query->whereNull('is_approved')
                  ->orWhere('is_approved', false);
        })->get();

        if ($users->isEmpty()) {
            $this->info('✓ All users are already approved!');
            return 0;
        }

        $count = 0;
        foreach ($users as $user) {
            $user->update([
                'is_approved' => true,
                'approved_at' => now(),
            ]);
            $count++;
            $this->line("✓ Approved: {$user->name} ({$user->email}) - {$user->role}");
        }

        $this->newLine();
        $this->info("✨ Successfully approved {$count} user(s)!");
        
        return 0;
    }
}
