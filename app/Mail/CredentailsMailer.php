<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CredentailsMailer extends Mailable
{
    use Queueable, SerializesModels;

    public $username, $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Credentilas Changed Notification')
            ->view('emails.credentilasmailer')
            ->with([
                'username' => $this->username,
                'password' => $this->password,
            ]);
    }
}
