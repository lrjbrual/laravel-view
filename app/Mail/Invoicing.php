<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\URL;
use App\BillingInvoice;
use App\Seller;
use Illuminate\Support\Facades\Crypt;
use Countries;
use DvK\Laravel\Vat\Facades\Rates;
use DvK\Laravel\Vat\Facades\Countries as Countrydvk;
use App\BillingInvoiceItem;
use Illuminate\Support\Facades\DB;

class Invoicing extends Mailable
{
    public $fname;
    public $lname;
    public $token;
    public $baseurl;
    public $invoice;
    public $date;
    public $seller_id;
    public $description;
    public $amount;
    public $vat;
    public $currency;
    public $promo;
    public $promo_discount;
    public $name;
    public $company;
    public $address;
    public $city;
    public $state;
    public $postal_code;
    public $vat_num;
    public $preferred_currency;
    public $country;
    public $total_amount;
    public $symbol;
    public $rate;
    public $items;
    public $symbol1;
    public $currency1;
    public $total_amount1;
    public $conversion;
    public $subAmount;


    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($fname, $lname, $token)
    {

        $this->fname = ucwords($fname);
        $this->lname = ucwords($lname);

        $this->baseurl = URL::to('/');
        $this->token = $token;

        $invoice = Crypt::decryptString($token);
        $bi = BillingInvoice::where('invoice_number', '=', $invoice)
                              ->first();
        $this->date = date("m/d/Y", strtotime($bi->created_at));
        $this->seller_id = $bi->seller_id;
        $status = $bi->status;
        // $this->description = $bi->product_description;
        $amount = $bi->amount;
        $vat = $bi->vat;
        $currency = $bi->currency;
        $promo = $bi->promocode;
        if ($promo == NULL) {
            $this->promo = 0;
            $promo_discount = 0;
        } else {
            $this->promo = 1;
            $promo_discount = $bi->promocode_discount;
        }

        $s = Seller::find($this->seller_id);
        $this->name = ucfirst($s->billing->firstname).' '.ucfirst($s->billing->lastname);
        $this->company = ucfirst($s->billing->company);
        $this->address = ucfirst($s->billing->address1);
        $this->city = ucfirst($s->billing->city);
        $this->state = ucfirst($s->billing->state);
        $this->postal_code = ucfirst($s->billing->postal_code);
        $vat_num = $s->billing->vat_number;
        $cc = $bi->country_code;
        if ($status == 'Valid Vat Number') {
            $this->vat_num = 'VAT No. '.$cc.$vat_num;
        } else {
            $this->vat_num = '';
        }

        if ($currency == 'usd') {
            $this->symbol = '$';
        } elseif ($currency == 'gbp') {
            $this->symbol = '£';
        } elseif ($currency == 'eur') {
            $this->symbol = '€';
        }

        $c = Countries::getOne($s->billing->country_id);
        $this->country = $c['name'];

        if (strlen($invoice) == 1) {
            $this->invoice = '0000'.$invoice;
        } elseif (strlen($invoice) == 2) {
            $this->invoice = '000'.$invoice;
        } elseif (strlen($invoice) == 3) {
            $this->invoice = '00'.$invoice;
        } elseif (strlen($invoice) == 4) {
            $this->invoice = '0'.$invoice;
        } else {
            $this->invoice = $invoice;
        }

        $inEurope = Countrydvk::inEurope($cc);

        if ($inEurope) {
          $this->rate = Rates::country($cc);
        } else {
          $this->rate = 0;
        }
        $this->subAmount = 0;
        $bii = BillingInvoiceItem::where('bi_id', '=', $bi->id)
                              ->get();
        $this->items = array();
        foreach ($bii as $value) {
            $d = array();
            $d['description_item'] = $value->product_description;
            $d['amount_item'] = $this->getTwoDecimal($value->item_amount);
            $this->items[] = $d;
            $this->subAmount = $this->subAmount + $value->item_amount;
        }

        $this->subAmount = $this->getTwoDecimal($this->subAmount);
        $total_amount = $amount + $vat;
        $this->currency = $currency;
        $this->amount = $this->getTwoDecimal(($amount + $promo_discount));
        $this->promo_discount = $this->getTwoDecimal($promo_discount);
        $this->vat = $this->getTwoDecimal($vat);
        $this->total_amount = $this->getTwoDecimal($total_amount);

        $con = DB::table('currencies')->where('code', '=', strtoupper($currency))->first();
        $this->conversion = '1 USD = '.$con->exchange_rate.' '.strtoupper($currency);
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function getTwoDecimal($amount)
    {
        $amount = $amount * 100;
        $amount = floor($amount);
        $amount = $amount/100;
        return $amount;
    }


    public function build()
    {
        // $arr=array(
        //   'email'=>$this->email
        // );
        return $this->view('emails.invoicing2')
        ->from('crm@trendle.io', 'Trendle.io')
        ->subject('Trendle.io has sent an invoice.');
    }
}
