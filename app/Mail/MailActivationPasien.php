<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Auth;

class MailActivationPasien extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $link;
    public $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $link)
    {
        $this->data = $data;
        $this->link = $link;
        $this->user = Auth::user();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Aktivasi Akun Pasien')
                    ->view('emails._aktivasi_akun_pasien');
    }
}
