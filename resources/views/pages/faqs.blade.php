@extends('layouts.master')
@section('title', '| FAQs')

@section('content')
  <div class="container margin-top-60 ">
    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12 background-pink-faqs">
        <h4 class="text-center">
          <span class="glyphicon glyphicon-question-sign icon-faqs"></span> <u>FAQs</u>
        </h4>
		 <div class="panel-group" id="accordion">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">How to add API?</a>
                </h4>
            </div>
            <div id="collapseOne" class="panel-collapse collapse">
                <div class="panel-body">
                    Account registration at <strong>PrepBootstrap</strong> is only required if you will be selling or buying themes. 
                    This ensures a valid communication channel for all parties involved in any transactions. 
                </div>
            </div>
        </div>
		<div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">How to add user?</a>
                </h4>
            </div>
            <div id="collapseTwo" class=" collapse">
                <div class="panel-body">
                    Account registration at <strong>PrepBootstrap</strong> is only required if you will be selling or buying themes. 
                    This ensures a valid communication channel for all parties involved in any transactions. 
                </div>
            </div>
        </div>
		<div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseThree">What is CRM?</a>
                </h4>
            </div>
            <div id="collapseThree" class=" collapse">
                <div class="panel-body">
                    Account registration at <strong>PrepBootstrap</strong> is only required if you will be selling or buying themes. 
                    This ensures a valid communication channel for all parties involved in any transactions. 
                </div>
            </div>
        </div>
		<div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseFour">and so on..</a>
                </h4>
            </div>
            <div id="collapseFour" class=" collapse">
                <div class="panel-body">
                    Account registration at <strong>PrepBootstrap</strong> is only required if you will be selling or buying themes. 
                    This ensures a valid communication channel for all parties involved in any transactions. 
                </div>
            </div>
        </div>
        </div>
		
      </div>
    </div>
  </div>
@endsection
