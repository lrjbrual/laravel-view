<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class InsufficientNotification extends Mailable
{
    public $email;
    public $fname;
    public $lname;
    public $baseurl;
    public $fees_paid;
    public $currency_symbol;
    public $daysCount;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($fname, $lname, $fees_paid, $currency_symbol, $daysCount)
    {
        //
        $this->fname = ucwords($fname);
        $this->lname = ucwords($lname);
        $this->fees_paid = round($fees_paid,2);
        $this->currency_symbol = $currency_symbol;
        $this->daysCount = $daysCount;

        $this->baseurl = URL::to('/');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.insufficient_notification')
        ->from(env('CONTACT_EMAIL1'), 'Trendle.io')
        ->subject('Payment method Problem');
    }
}
