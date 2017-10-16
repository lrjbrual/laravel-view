<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FbaCSEmailNotif extends Mailable
{
    public $sc_email;
    public $crm_email;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sc_email, $crm_email)
    {
        //
        $this->sc_email = $sc_email;
        $this->crm_email = $crm_email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.fbacsemailnotif')
        ->from('crm@trendle.io', 'Trendle.io')
        ->subject('Trendle Analytics New Seller');
    }
}
