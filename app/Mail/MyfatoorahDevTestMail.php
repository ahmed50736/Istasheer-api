<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MyfatoorahDevTestMail extends Mailable
{
    use Queueable, SerializesModels;

    private $payloadData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($payloadData)
    {
        $this->payloadData = $payloadData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('My fatoorah Test Mailer')->view('emails.dev-test-myfatoorah')
                    ->with('content',implode($this->payloadData));
    }
}
