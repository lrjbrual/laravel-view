<?php

namespace app\Http\Requests;

use app\Http\Requests\Request;

class ContactFormRequest extends Request {

  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'name' => 'required',
      'email' => 'required|email',
      'subject' => 'required',
      'message' => 'required',
    ];
  }

}
