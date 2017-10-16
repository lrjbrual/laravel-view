<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReviewsEmail extends Mailable
{
    public $first;
    public $type;
    public $array;
    public $subject;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($first,$array,$type)
    {
        //
        $this->first = ucwords($first);
        $this->array = $array;
        $this->type = $type;

        $this->subject = '';
        if($type == 'product')
        {
            $this->subject = 'New Product Reviews';
        }
        else
        {
            $this->subject = 'New Seller Reviews';
        }

    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.reviews_email')
        ->from(env('CONTACT_EMAIL1'), 'Trendle.io')
        ->subject($this->subject);
    }
}
