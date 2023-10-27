<?php

namespace App\Jobs;

use App\helpers\FutureSmsIntegration;
use App\Mail\CredentailsMailer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class CredentailsSender implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $username, $password;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::select('email', 'phone_no')->where('username', $this->username)->first();
        if ($user->email) {
            Mail::to($user->email)->send(new CredentailsMailer($this->username, $this->password));
        } else {
            FutureSmsIntegration::credentailsChangeMessageSender($user->phone_no, $this->username, $this->password);
        }
    }
}
