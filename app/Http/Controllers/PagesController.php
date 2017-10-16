<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Post;
use Mail;
use Session;
use Auth;
use \Config;

class PagesController extends Controller
{
  protected function guard()
  {
    return Auth::guard();
  }

  public function setLang(Request $request,$locale) {
    Session::forget('lang');
    Session::put('lang', $locale);
    return redirect()->back();
  }

  public function Home() 
  {

    $locales = $this->getAvailableAppLangArray();
    return view ('pages.home')
          ->with('locales', $locales);
  }

  public function getContact()
  { 

    $locales = $this->getAvailableAppLangArray();
    return view('pages.contact')
          ->with('locales', $locales);
  }

  public function pricing()
  { 

    $locales = $this->getAvailableAppLangArray();
    return view('pages.pricing')
          ->with('locales', $locales);
  }

  public function postContact(Request $request) 
  {
    //set’s application’s locale
    $locale = session()->get('lang', 'en');
    app()->setLocale($locale);

    $this->validate($request, [
      'Full_Name' => 'min:3',
      'email' => 'required|email',
      'subject' => 'min:3',
      'message' => 'min:10']);
    $data = array(
      'name' => $request->Full_Name,
      'email' => $request->email,
      'subject' => $request->subject,
      'bodyMessage' => $request->message
      );
    Mail::send('emails.contact', $data, function($message) use ($data){
      $message->from($data['email']);
      $message->to(config('constant.emails.contact_us'));
      $message->subject($data['subject']);
    });
    flash('Your email was sent! We\'ll aim to respond within 24hours', 'success');
    return redirect('contact');
  }

  public function Login() 
  {
    
    $locales = $this->getAvailableAppLangArray();
    return view ('pages.login')
          ->with('locales', $locales);
  }

  public function Register()
  {
    
    $locales = $this->getAvailableAppLangArray();
    return view ('pages.register')
          ->with('locales', $locales);
  }

  public function Faqs()
  {
    
    $locales = $this->getAvailableAppLangArray();
    return view ('pages.faqs')
          ->with('locales', $locales);
  }
    
  public function Privacy()
  {
    
    $locales = $this->getAvailableAppLangArray();
    return view ('pages.privacy')
          ->with('locales', $locales);
  }

  private function getAvailableAppLangArray()
  {
    $locales = array();
    foreach (Config::get('app.locales') as $key => $value)
    {
      $locales[$key] = $value;
    }
    return $locales;
  } 

}
