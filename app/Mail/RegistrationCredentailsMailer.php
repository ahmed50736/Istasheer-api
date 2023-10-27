<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationCredentailsMailer extends Mailable
{
    use Queueable, SerializesModels;
    public $userName, $password, $type;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $userName, string $password, string $type)
    {
        $this->userName = $userName;
        $this->password = $password;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.registrationcredentails', ['username' => $this->userName, 'password' => $this->password, 'user_type' => $this->type]);
    }
}
