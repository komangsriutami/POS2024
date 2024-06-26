<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Auth;

/*
    =======================================================================================
    For     : Email invite Apoteker
    Author  : Agus Yudi
    Date    : 14/09/2021
    =======================================================================================
*/

class MailInviteApoteker extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $user;
    public $link;

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
        return $this->subject('Undangan Sebagai Apoteker Baru')
            ->view('emails._invite_akun_Apoteker');
    }
}
