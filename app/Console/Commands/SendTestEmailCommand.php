<?php

namespace App\Console\Commands;

use App\Mail\UserCredentialsMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email : The email address to send the test to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify SMTP configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Sending test email to: {$email}");
        
        try {
            Mail::to($email)->send(
                new UserCredentialsMail(
                    userName: 'Test User',
                    userEmail: $email,
                    userPassword: 'TestPass123',
                    loginUrl: url('/login')
                )
            );
            
            $this->info("✓ Test email sent successfully!");
            $this->info("Check your inbox at: {$email}");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("✗ Failed to send test email!");
            $this->error("Error: " . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
