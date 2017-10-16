<?php

namespace App\Http\Controllers\Trendle\PDF;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use App\Currency;
use App\Billing;
use App\BillingInvoice;
use App\Seller;
use App;
use Illuminate\Support\Facades\Crypt;
use Countries;
use DvK\Laravel\Vat\Facades\Rates;
use App\BillingInvoiceItem;
use DvK\Laravel\Vat\Facades\Validator;
use DvK\Laravel\Vat\Facades\Countries as CountriesDvk;
use Illuminate\Support\Facades\DB;

class PDFController extends Controller
{
    public function __construct()
    {
      $this->middleware('auth');
    }

    public function billing_invoice($token){

    	$invoice = Crypt::decryptString($token);
    	$bi = BillingInvoice::where('invoice_number', '=', $invoice)
                              ->first();
        $date = date("m/d/Y", strtotime($bi->created_at));
        $seller_id = $bi->seller_id;
        $status = $bi->status;
        // $description = $bi->product_description;
        $amount = $bi->amount;
        $vat = $bi->vat;
        $currency = $bi->currency;
        $promo = $bi->promocode;
        if ($promo == NULL) {
            $promo = 0;
            $promo_discount = 0;
        } else {
            $promo = 1;
            $promo_discount = $bi->promocode_discount;
        }

    	$s = Seller::find($seller_id);
    	$name = ucfirst($s->billing->firstname).' '.ucfirst($s->billing->lastname);
    	$company = ucfirst($s->billing->company);
    	$address = ucfirst($s->billing->address1);
    	$city = ucfirst($s->billing->city);
    	$state = ucfirst($s->billing->state);
    	$postal_code = ucfirst($s->billing->postal_code);
    	$vat_num = $s->billing->vat_number;
    	$cc = $bi->country_code;
    	if ($status == 'Valid Vat Number') {
    		$vat_num = 'VAT No. '.$cc.$vat_num;
    	} else {
    		$vat_num = '';
    	}

    	if ($currency == 'usd') {
    		$symbol = '$';
    	} elseif ($currency == 'gbp') {
    		$symbol = '£';
    	} elseif ($currency == 'eur') {
    		$symbol = '€';
    	}

    	$c = Countries::getOne($s->billing->country_id);
      	$country = $c['name'];

      	if (strlen($invoice) == 1) {
            $invoice = '0000'.$invoice;
        } elseif (strlen($invoice) == 2) {
            $invoice = '000'.$invoice;
        } elseif (strlen($invoice) == 3) {
            $invoice = '00'.$invoice;
        } elseif (strlen($invoice) == 4) {
            $invoice = '0'.$invoice;
        } else {
            $invoice = $invoice;
        }

        $inEurope = CountriesDvk::inEurope($cc);
        $rate = 0;
        if ($inEurope) {
            $vat_number = $cc.$s->billing->vat_number;
            $v = Validator::validate($vat_number);
            if ($v == false) {
              $rate = Rates::country($cc);
            }
        }

        $bii = BillingInvoiceItem::where('bi_id', '=', $bi->id)
                              ->get();
        $items = array();
        foreach ($bii as $value) {
            $d = array();
            $d['description_item'] = $value->product_description;
            $d['amount_item'] = $this->getTwoDecimal($value->item_amount);
            $items[] = $d;
        }

      	$total_amount = $amount + $vat;
      	$amount = $this->getTwoDecimal(($amount + $promo_discount));
      	$promo_discount = $this->getTwoDecimal($promo_discount);
      	$vat = $this->getTwoDecimal($vat);
      	$total_amount = $this->getTwoDecimal($total_amount);

        $con = DB::table('currencies')->where('code', '=', strtoupper($currency))->first();
        $conversion = '1 USD = '.$con->exchange_rate.' '.strtoupper($currency);

		$pdf = App::make('dompdf.wrapper');
		$data = array('invoice'		=>	$invoice,
					'date'			=>	$date,
					'seller_id'		=>	$seller_id,
					'name'			=>	$name,
					'company'		=>	$company,
					'address'		=>	$address,
					'city'			=>	$city,
					'state'			=>	$state,
					'postal_code'	=>	$postal_code,
					'country'		=>	$country,
					'vat_num'		=>	$vat_num,
					// 'description'	=>	$description,
					'amount'		=>	$amount,
					'promo'			=>	$promo,
                    'promo_discount'=>  $promo_discount,
					'vat'			=>	$vat,
					'total_amount'	=>	$total_amount,
					'symbol'	    =>	$symbol,
                    'rate'          =>  $rate,
					'currency'		=>	$currency,
                    'items'         =>  $items,
                    'conversion'    =>  $conversion);
		$pdf->loadView('pdf.invoice2', [
		    'data' => $data,
		])->setPaper('a4', 'portrait');
		// return $pdf->download();
		// - for test 
        return $pdf->stream();

		return view('pdf.invoice2');
    }

    public function getTwoDecimal($amount)
    {
        $amount = $amount * 100;
        $amount = floor($amount);
        $amount = $amount/100;
        return $amount;
    }
}
