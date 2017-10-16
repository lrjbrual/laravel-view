@if (Session::has('error'))
  <div class="alert alert-danger m-t-5">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      </a>{{ Session::get('error') }}</div>
  </div>
@endif

@if($billing)
  {{ Form::model($billing, array('route' => array('billing.update', $billing->id), 'method' => 'PUT')) }}
@else
  {{ Form::open(array('action' => 'Trendle\BillingController@store')) }}
@endif
  <div class="col-md-6">
    <div class="form-group col-md-10 col-sm-12">
      <h3 class="header-text-billing color-orange m-t-25">Billing Address</h3>
        {!! Form::text('firstname', old('firstname'),  ['class' => 'form-control margin-form-control', 'placeholder' => 'First Name', 'required']) !!}
        {!! Form::text('lastname', old('lastname'),  ['class' => 'form-control margin-form-control', 'placeholder' => 'Last Name', 'required']) !!}
        {!! Form::text('company', old('company'),  ['class' => 'form-control margin-form-control', 'placeholder' => 'Company Name', 'required']) !!}
        {!! Form::text('address1', old('address1'),  ['class' => 'form-control margin-form-control', 'placeholder' => 'Address 1', 'required']) !!}
        {!! Form::text('address2', old('address2'),  ['class' => 'form-control margin-form-control', 'placeholder' => 'Address 2']) !!}
        {!! Form::text('city', old('city'),  ['class' => 'form-control margin-form-control' , 'placeholder' => 'City', 'required']) !!}
        {!! Form::text('postal_code', old('postal_code'),  ['class' => 'form-control margin-form-control', 'placeholder' => 'Postal code', 'required']) !!}
        {!! Form::text('state', old('state'),  ['class' => 'form-control margin-form-control select_country', 'placeholder' => 'State']) !!}
        {!! Form::select('country_id', $countries, old('country_id'),  ['class' => 'form-control margin-form-control select-country', 'placeholder' => 'Select Your Country', 'required']) !!}
        <div class="input-group m-b-10">
          <span class="input-group-addon" style="padding:0px;">
          {!! Form::select('vat_country_code', $CountryCode, old('vat_country_code'),  ['style' => 'height: 31px;border: 1px solid #D9D9D9', 'class' => '']) !!}
          </span>
          {!! Form::text('vat_number', old('vat_number'),  ['class' => 'form-control margin-form-control vatTb', 'placeholder' => 'Vat Number']) !!}
        </div>
        <input class="btn btn-primary col-md-12 col-sm-12 col-xs-12" type="submit" value="Save">
    </div>
  </div>
  <input type="hidden" name="from" value="{{ (isset($from)) ? $from : '' }}">

  <input type="hidden" id="firstname" value="{{ count($billing) == 0 ? '' : $billing->firstname }}">
  <input type="hidden" id="lastname" value="{{ count($billing) == 0 ? '' : $billing->lastname }}">
  <input type="hidden" id="company" value="{{ count($billing) == 0 ? '' : $billing->company }}">
  <input type="hidden" id="address1" value="{{ count($billing) == 0 ? '' : $billing->address1 }}">
  <input type="hidden" id="city" value="{{ count($billing) == 0 ? '' : $billing->city }}">
  <input type="hidden" id="postal_code" value="{{ count($billing) == 0 ? '' : $billing->postal_code }}">
  <input type="hidden" id="country_id" value="{{ count($billing) == 0 ? '' : $billing->country_id }}">
{{ Form::close() }}
