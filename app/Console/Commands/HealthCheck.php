<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class HealthCheck extends Command
{
    protected $signature = 'app:health-check';
    protected $description = 'Check application health status';

    public function handle()
    {
        $this->info('🏥 Skynet Health Check');
        $this->newLine();

        // Check database connection
        try {
            DB::connection()->getPdo();
            $this->info('✅ Database: Connected');
        } catch (\Exception $e) {
            $this->error('❌ Database: Failed - ' . $e->getMessage());
            return 1;
        }

        // Check if users exist
        try {
            $userCount = User::count();
            $this->info("✅ Users: {$userCount} users found");
            
            if ($userCount === 0) {
                $this->warn('⚠️  No users found. Run php artisan db:seed');
            } else {
                $adminUser = User::whereHas('role', function($query) {
                    $query->where('slug', 'admin');
                })->first();
                
                if ($adminUser) {
                    $this->info("✅ Admin user exists: {$adminUser->email}");
                } else {
                    $this->warn('⚠️  No admin user found');
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Users: Failed - ' . $e->getMessage());
        }

        // Check storage directories
        $directories = [
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
            'bootstrap/cache'
        ];

        foreach ($directories as $dir) {
            if (is_dir(base_path($dir))) {
                $this->info("✅ Directory: {$dir}");
            } else {
                $this->error("❌ Directory missing: {$dir}");
            }
        }

        $this->newLine();
        $this->info('🚀 Health check completed!');
        return 0;
    }
}