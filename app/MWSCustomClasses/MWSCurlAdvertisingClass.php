<?php
namespace App\MWSCustomClasses;

use File;

class MWSCurlAdvertisingClass {
	private $client_id = 'amzn1.application-oa2-client.63c3a699e1a04a5d93b673f1b84d8d9c';
	private $client_secret = '4f948107fcf067fe4d7bc75af864837ee74bb41db13481eff9e248a544964fc7';
	private $urls = ['na'=>'https://advertising-api.amazon.com','eu'=>'https://advertising-api-eu.amazon.com'];

	//request for tokens using code
	public function get_access_token($code){
		$c = curl_init('https://api.amazon.com/auth/o2/token');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded;charset=UTF-8'));
		curl_setopt($c, CURLOPT_POST, true);
		$post_fields = array(
			'grant_type' => 'authorization_code',
			'code' => $code,
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
		);
		curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($post_fields));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);

		return $data;
	}

	// getting seller profile
	// returns seller profiles of both na and eu
	public function get_seller_profiles($access_token){
		$data['na'] = $this->get_seller_profile($access_token, 'na');
		$data['eu'] = $this->get_seller_profile($access_token, 'eu');
		return $data;
	}

	// get seller profile by mkp
	// mkp should be na or eu
	// return profile id of specific mkp specified
	public function get_seller_profile($access_token, $mkp){
		// exchange the access token for user profile EU
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/profiles');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type: application/json'));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$eu = json_decode($r);
		return $eu;
	}

	//refreshing the access and refresh tokens of the seller using refresh token
	public function refresh_tokens($refresh_token){
		$c = curl_init('https://api.amazon.com/auth/o2/token');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded;charset=UTF-8'));
		curl_setopt($c, CURLOPT_POST, true);
		$post_fields = array(
			'grant_type' => 'refresh_token',
			'client_id' => $this->client_id,
			'refresh_token' => $refresh_token,
			'client_secret' => $this->client_secret,
		);
		curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($post_fields));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);

		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// data is the data of new campaign list to be submitted to amazon in multi dimentional array format
	// required fields name, campaignType, targetingType, state, dailyBudget and startDate
	public function add_campaign($access_token, $profile_id, $mkp,  $data){
		if(count($data) > 100) return ['error'=>"Limit is only 100 data per request"];
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/campaigns');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// campaignId is the id of the campaign that need to be archieved or deleted
	public function delete_campaign($access_token, $profile_id, $mkp,  $campaignId){
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/campaigns/'.$campaignId);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'DELETE');
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// data is the data to be submitted to amazon in multi dimentional array format
	// mutable fields:
	// name, state, dailyBudget, startDate, endDate, premiumBidAdjustment
	public function update_campaign($access_token, $profile_id, $mkp,  $data){
		if(count($data) > 100) return ['error'=>"Limit is only 100 data per request"];
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/campaigns');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($c, CURLOPT_POSTFIELDS,json_encode($data));
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// test or sandbox
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country
	// data is the data to be submitted to amazon in multi dimentional array format
	// mutable fields:
	// name, state, dailyBudget, startDate, endDate, premiumBidAdjustment
	public function update_campaign_test($access_token, $profile_id, $data){
		if(count($data) > 100) return ['error'=>"Limit is only 100 data per request"];
		$c = curl_init('https://advertising-api-test.amazon.com/v1/campaigns');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($c, CURLOPT_POSTFIELDS,json_encode($data));
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// condition is the condition or filters for the request
	// condition is an array might consist any of the following:
	// startIndex, count, campaignType, stateFilter, name, campaignIdFilter
	// this should be the index of the array
	public function get_campaigns($access_token, $profile_id, $mkp, $condition = array()){
		$condition = http_build_query($condition);
		$url = $this->urls[strtolower($mkp)].'/v1/campaigns/extended';
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', 'Amazon-Advertising-API-Scope: '.$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// getting specific campaign
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// campaign id of the campaign
	// this should be the index of the array
	public function get_campaign($access_token, $profile_id, $mkp, $campaign_id){
		$url = $this->urls[strtolower($mkp)].'/v1/campaigns/extended/'.$campaign_id;
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// data is the data of new adGroup list to be submitted to amazon in multi dimentional array format
	// campaignId, name, state and defaultBid
	public function add_adGroup($access_token, $profile_id, $mkp,  $data){
		if(count($data) > 100) return ['error'=>"Limit is only 100 data per request"];
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/adGroups');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// adGroupId is the id of the adGroup that need to be archieved or deleted
	public function delete_adGroup($access_token, $profile_id, $mkp,  $adGroupId){
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/adGroup/'.$adGroupId);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'DELETE');
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// data is the data to be submitted to amazon in multi dimentional array format
	// mutable Fields: name, defaultBid, state
	public function update_adGroup($access_token, $profile_id, $mkp,  $data){
		if(count($data) > 100) return ['error'=>"Limit is only 100 data per request"];
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/adGroups');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($c, CURLOPT_POSTFIELDS,json_encode($data));
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// test or sandbox
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country
	// data is the data to be submitted to amazon in multi dimentional array format
	// mutable Fields: name, defaultBid, state
	public function update_adGroup_test($access_token, $profile_id, $data){
		if(count($data) > 100) return ['error'=>"Limit is only 100 data per request"];
		$c = curl_init('https://advertising-api-test.amazon.com/v1/adGroups');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($c, CURLOPT_POSTFIELDS,json_encode($data));
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// condition is the condition or filters for the request
	// condition is an array might consist any of the following:
	// startIndex, count, campaignType, stateFilter, name, campaignIdFilter, adGroupIdFilter
	// this should be the index of the array
	public function get_adGroups($access_token, $profile_id, $mkp, $condition){
		$condition = http_build_query($condition);
		$url = $this->urls[strtolower($mkp)].'/v1/adGroups/extended?'.$condition;
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// getting specific adGroup
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// adgroupid of the adgroup
	// this should be the index of the array
	public function get_adGroup($access_token, $profile_id, $mkp, $adgroupid){
		$url = $this->urls[strtolower($mkp)].'/v1/adGroups/extended/'.$adgroupid;
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// data is the data of new keyword adgroup level list to be submitted to amazon in multi dimentional array format
	// campaignId, adGroupId, keywordText, matchType and state
	public function add_keywords($access_token, $profile_id, $mkp,  $data){
		if(count($data) > 1000) return ['error'=>"Limit is only 1000 data per request"];
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/keywords');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// keywordsId is the id of the keyword adgroup level that need to be archieved or deleted
	public function delete_keywords($access_token, $profile_id, $mkp,  $keywordsId){
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/keywords/'.$keywordsId);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'DELETE');
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// data is the data to be submitted to amazon in multi dimentional array format
	// mutable fields: 
	// state: enabled, paused, archived
	// bid:
	public function update_keywords($access_token, $profile_id, $mkp,  $data){
		if(count($data) > 1000) return ['error'=>"Limit is only 1000 data per request"];
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/keywords');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($c, CURLOPT_POSTFIELDS,json_encode($data));
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// test or sandbox
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country
	// data is the data to be submitted to amazon in multi dimentional array format
	// mutable fields: 
	// state: enabled, paused, archived
	// bid:
	public function update_keywords_test($access_token, $profile_id, $data){
		if(count($data) > 1000) return ['error'=>"Limit is only 1000 data per request"];
		$c = curl_init('https://advertising-api-test.amazon.com/v1/keywords');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($c, CURLOPT_POSTFIELDS,json_encode($data));
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// condition is the condition or filters for the request
	// condition is an array might consist any of the following:
	// startIndex, count, campaignType, state, campaignIdFilter, adGroupIdFilter, matchTypeFilter, keywordText
	// this should be the index of the array
	public function get_keywords($access_token, $profile_id, $mkp, $condition){
		$condition = http_build_query($condition);
		$url = $this->urls[strtolower($mkp)].'/v1/keywords/extended?'.$condition;
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// getting specific keyword
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// keywordId of the keyword
	// this should be the index of the array
	public function get_keyword($access_token, $profile_id, $mkp, $keywordId){
		$url = $this->urls[strtolower($mkp)].'/v1/keywords/extended/'.$keywordId;
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// data is the data of new negativeKeywords adgroup level list to be submitted to amazon in multi dimentional array format
	public function add_adgroup_negativeKeywords($access_token, $profile_id, $mkp,  $data){
		if(count($data) > 1000) return ['error'=>"Limit is only 1000 data per request"];
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/negativeKeywords');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// keywordsId is the id of the negativeKeywords adgroup level that need to be archieved or deleted
	public function delete_adgroup_negativeKeywords($access_token, $profile_id, $mkp,  $keywordsId){
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/negativeKeywords/'.$keywordsId);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'DELETE');
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// data is the data to be submitted to amazon in multi dimentional array format
	// mutable fields: 
	// state: enabled, disabled
	public function update_adgroup_negativeKeywords($access_token, $profile_id, $mkp,  $data){
		if(count($data) > 1000) return ['error'=>"Limit is only 1000 data per request"];
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/negativeKeywords');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($c, CURLOPT_POSTFIELDS,json_encode($data));
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// test or sandbox
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country
	// data is the data to be submitted to amazon in multi dimentional array format
	// mutable fields: 
	// state: enabled, disabled
	public function update_adgroup_negativeKeywords_test($access_token, $profile_id, $data){
		if(count($data) > 1000) return ['error'=>"Limit is only 1000 data per request"];
		$c = curl_init('https://advertising-api-test.amazon.com/v1/negativeKeywords');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($c, CURLOPT_POSTFIELDS,json_encode($data));
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// condition is the condition or filters for the request
	// condition is an array might consist any of the following:
	// startIndex, count, campaignType, matchTypeFilter, keywordText, stateFilter, campaignIdFilter, adGroupIdFilter
	// this should be the index of the array
	public function get_adgroup_negativeKeywords($access_token, $profile_id, $mkp, $condition){
		$condition = http_build_query($condition);
		$url = $this->urls[strtolower($mkp)].'/v1/negativeKeywords/extended?'.$condition;
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// getting specific negative keyword
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// keywordId of the keyword
	// this should be the index of the array
	public function get_adgroup_negativeKeyword($access_token, $profile_id, $mkp, $keywordId){
		$url = $this->urls[strtolower($mkp)].'/v1/negativeKeywords/extended/'.$keywordId;
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// data is the data of new campaignNegativeKeywords list to be submitted to amazon in multi dimentional array format
	public function add_campaign_negativeKeywords($access_token, $profile_id, $mkp,  $data){
		if(count($data) > 1000) return ['error'=>"Limit is only 1000 data per request"];
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/campaignNegativeKeywords');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// keywordsId is the id of the campaignNegativeKeywords that need to be archieved or deleted
	public function delete_campaign_negativeKeywords($access_token, $profile_id, $mkp,  $keywordsId){
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/campaignNegativeKeywords/'.$keywordsId);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'DELETE');
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// data is the data to be submitted to amazon in multi dimentional array format
	// mutable fields: 
	// state: enabled, disabled
	public function update_campaign_negativeKeywords($access_token, $profile_id, $mkp,  $data){
		if(count($data) > 1000) return ['error'=>"Limit is only 1000 data per request"];
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/campaignNegativeKeywords');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($c, CURLOPT_POSTFIELDS,json_encode($data));
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// test or sandbox
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country
	// data is the data to be submitted to amazon in multi dimentional array format
	// mutable fields: 
	// state: enabled, disabled
	public function update_campaign_negativeKeywords_test($access_token, $profile_id, $data){
		if(count($data) > 1000) return ['error'=>"Limit is only 1000 data per request"];
		$c = curl_init('https://advertising-api-test.amazon.com/v1/campaignNegativeKeywords');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($c, CURLOPT_POSTFIELDS,json_encode($data));
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// condition is the condition or filters for the request
	// condition is an array might consist any of the following:
	// startIndex, count, campaignType, matchTypeFilter, keywordText, stateFilter, campaignIdFilter, adGroupIdFilter
	// this should be the index of the array
	public function get_campaign_negativeKeywords($access_token, $profile_id, $mkp, $condition){
		$condition = http_build_query($condition);
		$url = $this->urls[strtolower($mkp)].'/v1/campaignNegativeKeywords/extended?'.$condition;
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// getting specific negative keyword
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// keywordId of the keyword
	// this should be the index of the array
	public function get_campaign_negativeKeyword($access_token, $profile_id, $mkp, $keywordId){
		$url = $this->urls[strtolower($mkp)].'/v1/campaignNegativeKeywords/extended/'.$keywordId;
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// data is the data of new productAds list to be submitted to amazon in multi dimentional array format
	// campaignId, adGroupId, SKU and state
	public function add_productAds($access_token, $profile_id, $mkp,  $data){
		if(count($data) > 1000) return ['error'=>"Limit is only 1000 data per request"];
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/productAds');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// pid is the id of the productAds that need to be archieved or deleted
	public function delete_productAds($access_token, $profile_id, $mkp,  $pid){
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/productAds/'.$pid);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'DELETE');
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// manage product ads interface
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of na
	// mkp must be na or eu
	// data is the data to be submitted to amazon in multi dimentional array format
	// mutable fields: 
	// state: enabled, paused, archived
	public function update_productAds($access_token, $profile_id, $mkp,  $data){
		if(count($data) > 1000) return ['error'=>"Limit is only 1000 data per request"];
		$c = curl_init($this->urls[strtolower($mkp)].'/v1/productAds');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($c, CURLOPT_POSTFIELDS,json_encode($data));
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// manage product ads interface
	// test or sandbox
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country
	// data is the data to be submitted to amazon in multi dimentional array format
	// mutable fields: 
	// state: enabled, paused, archived
	public function update_productAds_test($access_token, $profile_id, $data){
		if(count($data) > 1000) return ['error'=>"Limit is only 1000 data per request"];
		$c = curl_init('https://advertising-api-test.amazon.com/v1/productAds');
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($c, CURLOPT_POSTFIELDS,json_encode($data));
    	$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// manage product ads interface
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// condition is the condition or filters for the request
	// condition is an array might consist any of the following:
	// startIndex, count, campaignType, adGroupId, sku, asin, stateFilter,campaignIdFilter, adGroupIdFilter
	// this should be the index of the array
	public function get_productAds($access_token, $profile_id, $mkp, $condition){
		$condition = http_build_query($condition);
		$url = $this->urls[strtolower($mkp)].'/v1/productAds/extended?'.$condition;
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// manage product ads interface
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// adId of the product ad
	// this should be the index of the array
	public function get_productAd($access_token, $profile_id, $mkp, $adId){
		$url = $this->urls[strtolower($mkp)].'/v1/productAds/extended/'.$adId;
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// Bid Recommendations Interface
	public function get_bid_recommendation_by_adGroupId($access_token, $profile_id, $mkp, $adgroupid){
		$url = $this->urls[strtolower($mkp)].'/v1/adGroups/'.$adgroupid.'/bidRecommendations';
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	public function get_bid_recommendation_by_keywordId($access_token, $profile_id, $mkp, $keywordId){
		$url = $this->urls[strtolower($mkp)].'/v1/keywords/'.$keywordId.'/bidRecommendations';
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	//batch keywords is limitted only to 100 keywordId multidimentional
	public function get_bid_recommendation_by_batch_keywords($access_token, $profile_id, $mkp, $keywordlist){
		if(count($keywordlist) > 100) return ['error'=>'Limit is only 100 data per request'];
		$url = $this->urls[strtolower($mkp)].'/v1/keywords/bidRecommendations';
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($keywordlist));
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}


	// requesting reports by recordType
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// recordType The type of entity for which the report should be generated. This must be one of campaigns, adGroups, keywords or productAds
	// condition is an array which consist of the following index: 
	// campaignType - The type of campaign for which performance data should be generated. Must be sponsoredProducts
	// segment - Optional. Dimension on which to segment the report. If specified, must be query
	// reportDate - The date for which to retrieve the performance report in YYYYMMDD format. The time zone is specified by the Profile used to request the report. If this date is today, then the performance report may contain partial information.Reports are not available for data older than 60 days
	// metrics - A comma-separated list of the metrics to be included in the report. See Report Metrics in the Developer Notes for a complete list of supported metrics.
	// impressions, clicks, cost, attributedConversions1dSameSKU, attributedConversions1dSameSKU, attributedConversions1d, attributedSales1dSameSKU, attributedSales1d, attributedConversions7dSameSKU, attributedConversions7d, attributedSales7dSameSKU, attributedSales7d, attributedConversions30dSameSKU, attributedConversions30d, attributedSales30dSameSKU, attributedSales30d
	public function get_report($access_token, $profile_id, $mkp, $recordType, $condition){
		$url = $this->urls[strtolower($mkp)].'/v1/'.$recordType.'/report';
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($condition));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// getting reports status by reportID
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// reportId 
	public function get_report_by_reportid($access_token, $profile_id, $mkp, $reportId){
		$url = $this->urls[strtolower($mkp)].'/v1/reports/'.$reportId;
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $access_token, 'Content-Type:application/json', "Amazon-Advertising-API-Scope: ".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$r = curl_exec($c);
		curl_close($c);
		$data = json_decode($r);
		return $data;
	}

	// extracting reports by report url
	// access token is the access token of the seller
	// profile id is the profile id of the seller in what country of eu  
	// $mkp should be na or eu
	// report_url is the url of the report 
	public function get_report_data_by_reporturl($access_token, $profile_id, $report_url){
		$url = trim($report_url);
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization:bearer ' . $access_token, "Amazon-Advertising-API-Scope:".$profile_id));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		$r = curl_exec($c);

		//getting the last redirected url
		$last_url = curl_getinfo($c, CURLINFO_EFFECTIVE_URL);
		curl_close($c);

		// extract the last url or the redirect url given by amazon.
    	return $this->get_final_url_report($last_url, $profile_id);
	}

	public function get_final_url_report($url, $profile_id){
		$url = trim($url);
		echo "<br>Redirect URL: ".$url."......";
		$fp = fopen('app/ads_ajax_files/report_'.$profile_id.'.json.gz', 'wb');
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0" );
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_BINARYTRANSFER, 1);
		$r = curl_exec($c);
		curl_close($c);
		$write = fwrite($fp,$r);
		fclose($fp);

		$file_name = 'app/ads_ajax_files/report_'.$profile_id.'.json.gz';

		// Raising this value may increase performance
		$buffer_size = 4096; // read 4kb at a time
		$out_file_name = str_replace('.gz', '', $file_name); 
		try{
			// Open our files (in binary mode)
			$file = gzopen($file_name, 'rb');
			$out_file = fopen($out_file_name, 'wb'); 

			// Keep repeating until the end of the input file
			while (!gzeof($file)) {
			    // Read buffer-size bytes
			    // Both fwrite and gzread and binary-safe
			    fwrite($out_file, gzread($file, $buffer_size));
			}

			// Files are done, close files
			fclose($out_file);
			gzclose($file);

			//converting the json file to array
			$data = json_decode(file_get_contents($out_file_name), true);

			// removing the files so it wont load the server
			File::delete($file_name);
			File::delete($out_file_name);

			return $data;
		}catch(\Exception $e){
			echo "Error occurred : " . '"'.$e->getMessage() . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
			return array();
		}
	}





}
?>