<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\URL;

use Illuminate\Http\Request;

class Confirmation extends Mailable
{
    public $email;
    public $fname;
    public $lname;
    public $token;
    public $baseurl;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public function __construct(Request $data, $token)
    {
        $this->fname = ucwords($data->fname);
        $this->lname = ucwords($data->lname);
        $this->email = $data->email;

        $this->baseurl = URL::to('/');
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // $arr=array(
        //   'email'=>$this->email
        // );
        return $this->view('emails.confirmation')
        ->from(env('CONTACT_EMAIL1'), 'Trendle.io')
        ->subject('Activate your Trendle.io Account');
    }
}
