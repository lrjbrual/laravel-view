<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;


use Illuminate\Http\Request;
use App;
class CronNotification extends Mailable
{
    public $cron_name = "";
    public $date;
    public $used_view="emails.end_run_cronnotification";
    public $subject = "Cron End Running";
    public $data = array();

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($cron_name = "", $isStart = true, $email_data = array())
    {
        $this->date = date('Y-m-d H:i:s');
        $this->data = $email_data;
        $this->cron_name = $cron_name;
        if($isStart){
            $this->used_view = "emails.start_run_cronnotification";
            $this->subject = "Cron for ".$cron_name." Start Running";
        }else{
            $this->used_view = "emails.end_run_cronnotification";
            $this->subject = "Cron for ".$cron_name." End Running";
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view($this->used_view)
        ->from('crons@trendle.io')
        ->subject($this->subject);
    }
}
