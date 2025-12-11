<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Webkul\User\Models\Admin;

class UpdateAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:update-password 
                            {email : The email of the admin user}
                            {password : The new password}
                            {--name= : Optional name to update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update admin user password for development purposes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->option('name');

        try {
            $admin = Admin::where('email', $email)->first();

            if (!$admin) {
                $this->error("Admin user with email '{$email}' not found.");
                return 1;
            }

            $admin->password = bcrypt($password);
            
            if ($name) {
                $admin->name = $name;
            }

            $admin->save();

            $this->info("Password updated successfully for: {$email}");
            if ($name) {
                $this->info("Name updated to: {$name}");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Error updating password: " . $e->getMessage());
            return 1;
        }
    }
}

