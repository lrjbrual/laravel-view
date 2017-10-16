<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CronNewColumnReport extends Mailable
{
    use Queueable, SerializesModels;

    public $table = "'Table not specified'";
    public $newcols = array();
    public $reportsampledata = array();

    public function __construct($newcols,$reportsampledata,$table)
    {
      $this->newcols=$newcols;
      $this->reportsampledata=$reportsampledata;
      $this->table=$table;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->view('emails.mwsreportnewcolumn')
        ->from('crons@trendle.io')
        ->subject('Cron for '.$this->table.' has new column');
    }
}
