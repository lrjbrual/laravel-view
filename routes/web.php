<?php

// Pages Routes


  Route::group(['middleware' => ('web')], function (){
  Auth::routes();
  Route::get('/setLang/{locale}', 'PagesController@setLang');
  Route::get('/', 'PagesController@Home');
  Route::get('contact', 'PagesController@getContact');
  Route::post('contact', 'PagesController@postContact');
  Route::get('pricing', 'PagesController@pricing');
  Route::get('/logout', function () { Auth:Logout(); return redirect('/login'); });
  Route::get('/home', 'Trendle\HomeController@index')->name('home');
  Route::post('/verify', 'Auth\RegisterController@verify');
  Route::get('verify_confirmation/{token}', 'Auth\RegisterController@verify_confirmation');
  \App\Http\Controllers\Trendle\Marketplace\MarketplaceController::routes();
  \App\Http\Controllers\Trendle\BillingController::routes();
  \App\Http\Controllers\Trendle\CompanyController::routes();
  \App\Http\Controllers\Trendle\PaypalController::routes();
  Route::get('campaign', 'Trendle\Campaign\CampaignController@index');
  Route::post('campaign/campaigntemplatelist', 'Trendle\Campaign\CampaignController@getCampaignTemplateList');
  Route::get('campaign/newcampaign/{t_id}', 'Trendle\Campaign\CampaignController@newCampaign');
  Route::get('campaign/loadcampaign/{c_id}', 'Trendle\Campaign\CampaignController@loadCampaign');
  Route::get('campaign/campaign/{c_id}', 'Trendle\Campaign\CampaignController@campaignEmail');
  Route::get('campaign/emailtags', 'Trendle\Campaign\CampaignController@getEmailTags');
  Route::get('campaign/emailbodytags', 'Trendle\Campaign\CampaignController@getEmailBodyTags');
  Route::post('campaign/setStatus', 'Trendle\Campaign\CampaignController@setStatus');
  Route::post('campaign/savecampaign', 'Trendle\Campaign\CampaignController@saveCampaign');
  Route::post('campaign/newtabcontent/{tabindex}', 'Trendle\Campaign\CampaignController@newTabContent');
  Route::post('campaign/savetemplate', 'Trendle\Campaign\CampaignController@saveTemplate');
  Route::post('campaign/doupload', 'Trendle\Campaign\CampaignController@doUpload');
  Route::post('campaign/manageattachment', 'Trendle\Campaign\CampaignController@manageAttachment');
  Route::post('campaign/removecampaignemail', 'Trendle\Campaign\CampaignController@removeCampaignEmail');
  Route::post('campaign/sendtest', 'Trendle\Campaign\CampaignController@sendTest');
  Route::get('campaign/sendtest', 'Trendle\Campaign\CampaignController@sendTest');
  Route::post('campaign/deleteCampaign', 'Trendle\Campaign\CampaignController@deleteCampaign');

  //seller reviews routes
  Route::post('SellerReviewsFilter', 'Trendle\SellerReviewController@filter_reviews');
  Route::post('UpdateReviewsAction', 'Trendle\SellerReviewController@update_reviews_action');
  Route::post('GetReviewFilter', 'Trendle\SellerReviewController@getReviewFilters');
  Route::post('AddReviewComment', 'Trendle\SellerReviewController@addReviewComment');
  Route::post('AddReviewFilter', 'Trendle\SellerReviewController@addReviewFilter');
  Route::resource('sellerreview', 'Trendle\SellerReviewController');

  // product reviews
  Route::resource('productreview', 'Trendle\ProductReviewController');
  Route::post('getProductReviewData', 'Trendle\ProductReviewController@getData');
  Route::post('moveProducts', 'Trendle\ProductReviewController@moveProducts');
  Route::post('AddProductReviewComment', 'Trendle\ProductReviewController@addReviewComment');

  //crons
  Route::resource('UpdateSellerReviewsDatabase','Crons\UpdateSellerReviewDatabaseController');
  Route::resource('UpdateFulfilledShipmentsDatabase','Crons\UpdateFulfilledShipmentsDatabaseController');
  Route::resource('CRMAutoCampaign','Crons\CRMAutoCampaignController');
  Route::resource('UpdateProductsDatabase','Crons\UpdateProductsDatabaseController');
  Route::resource('MasterCronScript','Crons\MasterCronScriptController');
  Route::resource('TrialPeriodChecker','Crons\SellerTrialPeriodCheckerController');
  Route::resource('StripePaymentForFBARefunds','Crons\StripePaymentForFBARefundsController');
  Route::resource('FbaRefundsPreCalculation','Crons\FbaRefundsPreCalculationController');
  Route::resource('PopulateAdminSellers','Crons\PopulateAdminSellersController');
  Route::resource('UpdateFlatFileAllOrdersByDate','Crons\UpdateFlatFileAllOrdersByDateController');
  Route::resource('ProductReviews','Crons\DexiController');
  Route::resource('UpdateProductImage', 'Crons\UpdateProductImageController');
  Route::resource('UpdateFBAFIR', 'Crons\UpdateFBAFulfillmentInventoryReceiptsController');

  //old advert crons
  Route::resource('UpdateCampaignAdvertising','Crons\UpdateCampaignAdController');
  Route::resource('UpdateAdsEntityReport','Crons\UpdateAdsEntityReportController');
  Route::resource('UpdateCampaignSPMega','Crons\UpdateCampaignSPMegaController');
  //advert new api crons
  Route::resource('UpdateAdvertCampaigns','Crons\UpdateAdvertCampaignsController');
  Route::get('ExtractAdvertCampaigns','Crons\UpdateAdvertCampaignsController@extract_reports');
  //related crons to advert
  Route::resource('UpdateProductsPrice','Crons\UpdateProductsPriceDatabaseController');
  Route::resource('UpdateProductsEstimateFees','Crons\UpdateProductsEstimateFeesController');


  //Merchant Refunds crons
  Route::resource('UpdateInventoryDataDatabase','Crons\UpdateInventoryDatabaseController');
  Route::resource('UpdateReimbursementDatabase','Crons\UpdateReimbursementDatabaseController');
  Route::resource('UpdateInventoryAdjustment','Crons\UpdateInventoryAdjustmentsDatabaseController');
  Route::resource('UpdateRuturnsReport','Crons\UpdateRuturnsReportDatabaseController');
  Route::resource('UpdateSettlementReport','Crons\UpdateSettlementReportDatabaseController');
  Route::resource('UpdateFinancialEvents','Crons\UpdateFinancialEventsController');


  //account routes
  Route::group(array('prefix' => 'account'), function() {
    Route::get('delete-account', 'Trendle\AccountController@deleteAccount');
    Route::post('delete-confirmed', 'Trendle\AccountController@deleteConfirmed');
  });
  //subscription routes
    Route::resource('subscription', 'Trendle\SubscriptionController');
    Route::group(array('prefix' => 'subscription'), function() {
    //temporary change subscription to crm loading
    // Route::post('subscribe', 'Trendle\SubscriptionController@subscribe');
    Route::post('subscribe', 'Trendle\SubscriptionController@purchase');
    Route::post('verify-coupon', 'Trendle\SubscriptionController@getPromo');
    Route::post('plan-coverage', 'Trendle\SubscriptionController@planCoverage');
    Route::post('hasBillingCard', 'Trendle\SubscriptionController@hasBillingCard');
  });
  Route::post('selectBaseSubscription', 'Trendle\SubscriptionController@selectBaseSubscription');
  Route::get('convertBS', 'Trendle\SubscriptionController@convertBS');
  Route::get('convertBoostBS', 'Trendle\SubscriptionController@convertBoostBS');
  Route::get('convertFbaRate', 'Trendle\SubscriptionController@convertFbaRate');
  Route::get('convertBoostBSBaseSubs', 'Trendle\SubscriptionController@convertBoostBSBaseSubs');

    // promo codes
  Route::resource('promocode', 'Trendle\PromoCodeController');

  Route::get('refund', 'Trendle\FBARefund\FBARefundController@index');
  Route::get('refund/checkout', 'Trendle\FBARefund\FBARefundController@checkout');
  Route::post('refund/payRefund', 'Trendle\FBARefund\FBARefundController@payRefund');
  Route::post('refund/deactivate', 'Trendle\FBARefund\FBARefundController@deactivate');
  Route::get('runrefundcrons', 'Trendle\FBARefund\FBARefundController@runRefundsCron');
  Route::post('refund/getFBADetails', 'Trendle\FBARefund\FBARefundController@getFBADetailsByCountry');
  Route::get('billinginvoice/{token}', 'Trendle\PDF\PDFController@billing_invoice');
  Route::post('refund/activate', 'Trendle\FBARefund\FBARefundController@activate');
  Route::post('refund/updateFbaMode', 'Trendle\FBARefund\FBARefundController@updateFbaMode');
  Route::post('/getfbasellers1', 'Trendle\FBARefund\FBARefundController@getfbasellers');
  Route::post('/getSellerOIC1', 'Trendle\FBARefund\FBARefundController@getSellerOIC');
  Route::post('/getSellerFNSKU1', 'Trendle\FBARefund\FBARefundController@getSellerFNSKU');
  Route::post('/fbarefundsellerfilter1', 'Trendle\FBARefund\FBARefundController@getfbasellersFiltered'); //filter for first table fba sellers
  Route::post('/getSellerOICFilter1', 'Trendle\FBARefund\FBARefundController@getSellerOICFiltered');     //filter for 2nd table oic table
  Route::post('/getSellerFNSKUFilter1', 'Trendle\FBARefund\FBARefundController@getSellerFNSKUFiltered'); //filter for 3rd table fnsku
  Route::post('/update_adminsellers1', 'Trendle\FBARefund\FBARefundController@update_adminsellers');
  Route::post('/update_adminOIC1', 'Trendle\FBARefund\FBARefundController@update_adminOIC');
  Route::post('/update_adminFNSKU1', 'Trendle\FBARefund\FBARefundController@update_adminFNSKU');
  Route::post('/update_oicstatus1' , 'Trendle\FBARefund\FBARefundController@updateStatusOIC');
  Route::post('/update_fnskustatus1' , 'Trendle\FBARefund\FBARefundController@updateStatusFNSKU');

  //Billing validate stripe
  Route::post('setValidity','Trendle\BillingController@setValidity');
  Route::post('notValid','Trendle\BillingController@notValid');

  // dashboard
  Route::post('todo/create', 'Trendle\HomeController@createTodo');
  Route::post('todo/update', 'Trendle\HomeController@updateTodo');
  Route::post('todo/delete', 'Trendle\HomeController@deleteTodo');
  Route::post('todo/status', 'Trendle\HomeController@updateStatus');
  Route::get('get_on_board', 'Trendle\HomeController@get_on_board');
  Route::get('update_onboaring_billing', 'Trendle\HomeController@update_onboaring_billing');


  //P&L
  Route::resource('pnl', 'Trendle\PnLController');
  Route::post('pnlRevTable', 'Trendle\PnLController@getRevenueTableData');
  Route::post('pnlRevGraph', 'Trendle\PnLController@getRevenueGraphData');

  Route::group(array('prefix' => 'pnl'), function() {
    Route::get('/', 'Trendle\PnLController@index');
    // Route::get('pnl_getpnlcosttable', 'Trendle\PnLController@getPnLCostTable');
    Route::post('pnl_getpnlcosttable', 'Trendle\PnLController@getPnLCostTable');
  });



  // Ads Performance
  // Route::get('test', 'Trendle\DexiController@index');

  Route::resource('adsperformance', 'Trendle\AdsPerformanceController');
  Route::post('getAdData', 'Trendle\AdsPerformanceController@getAdData');
  Route::post('getAdFilters', 'Trendle\AdsPerformanceController@getAdFilters');
  Route::post('addAdFilter', 'Trendle\AdsPerformanceController@addAdFilter');
  Route::post('updateAdFilter', 'Trendle\AdsPerformanceController@updateAdFilter');
  Route::post('getAdFilterData', 'Trendle\AdsPerformanceController@getAdFilterData');
  Route::post('deleteAdFilterData', 'Trendle\AdsPerformanceController@deleteAdFilter');
  Route::post('getAdGraph', 'Trendle\AdsPerformanceController@getAdGraph');
  Route::post('updateAdComment', 'Trendle\AdsPerformanceController@updateAdComment');
  Route::post('performance_sendCampaignId', 'Trendle\AdsPerformanceController@sendCampaignId');
  Route::post('performance_adgroup', 'Trendle\AdsPerformanceController@performance_adgroup');
  Route::post('performance_keyword', 'Trendle\AdsPerformanceController@performance_keyword');

  Route::post('updateAdsBid', 'Trendle\AdsPerformanceController@updateAdsBid');
  Route::post('deleteAdsBid', 'Trendle\AdsPerformanceController@deleteAdsBid');
  Route::get('getAdsBid', 'Trendle\AdsPerformanceController@getAdsBid');
  Route::post('updateMatchType', 'Trendle\AdsPerformanceController@updateMatchType');
  Route::get('countChanges', 'Trendle\AdsPerformanceController@countChanges');
  Route::post('submitBidToAmazon', 'Trendle\AdsPerformanceController@submit_bid_to_amazon');
  Route::post('getChangeBid', 'Trendle\AdsPerformanceController@getChangeBid');

  // Ads Product cost
  Route::resource('productscosts', 'Trendle\adsProductsCostsController');
  Route::get('getAdProdCostData', 'Trendle\adsProductsCostsController@getAdProdCostData');
  Route::post('updateUnitCost', 'Trendle\adsProductsCostsController@updateUnitCost');
  Route::post('updateMinimumMargin', 'Trendle\adsProductsCostsController@updateMinimumMargin');
  Route::post('updateConversionTimePeriod', 'Trendle\adsProductsCostsController@updateConversionTimePeriod');
  Route::post('productscosts/importExcelAdsProduct', 'Trendle\adsProductsCostsController@importCsv');
  Route::get('exportExcelAdsProduct', 'Trendle\adsProductsCostsController@export_table');

  // Ads recommendation
  Route::resource('adsrecommendation', 'Trendle\AdsRecommendationController');
  Route::post('saveAdRule', 'Trendle\AdsRecommendationController@saveAdRule');
  Route::get('showAdRule', 'Trendle\AdsRecommendationController@showAdRule');
  Route::post('saveChanges', 'Trendle\AdsRecommendationController@saveChanges');
  Route::post('deleteRule', 'Trendle\AdsRecommendationController@deleteRule');
  Route::get('getDropDownData', 'Trendle\AdsRecommendationController@getCampaignAdgroupData');

  Route::get('test','Trendle\AdsRecommendationController@test');
  // Campaign Manager
  Route::resource('campaignmanager', 'Trendle\AdsCampaignManagerController');
  Route::post('getCampaignData ', 'Trendle\AdsCampaignManagerController@getCampaignData');
  Route::post('getCampaignAdGroup ', 'Trendle\AdsCampaignManagerController@getCampaignAdGroup');
  Route::post('postCampaignData ', 'Trendle\AdsCampaignManagerController@postCampaignData');
  Route::post('get_country_sku', 'Trendle\AdsCampaignManagerController@get_country_sku');
  Route::post('sendCapaignId', 'Trendle\AdsCampaignManagerController@sendCapaignId');
  Route::post('sendAdgroupId', 'Trendle\AdsCampaignManagerController@sendAdgroupId');
  Route::post('getSkuByadGroup', 'Trendle\AdsCampaignManagerController@getSkuByadGroup');
  Route::post('updateCampaignValue', 'Trendle\AdsCampaignManagerController@updateCampaignValue');
  Route::post('updateAdgroupValue', 'Trendle\AdsCampaignManagerController@updateAdgroupValue');

  // Ads Campaign Manager
  Route::resource('adscampaignmanager', 'Trendle\AdsCampaignManagerController');
  Route::get('test_add_campaign', 'Trendle\AdsCampaignManagerController@test_add_campaign');
  Route::get('get_campaign_list_is_link', 'Trendle\AdsCampaignManagerController@get_campaign_list_is_link');

  //Admin functions routing
  Route::prefix('admin')->group(function(){
    Route::get('/login', 'Auth\AdminLoginController@showLoginForm')->name('admin.login');
    Route::post('/login', 'Auth\AdminLoginController@login')->name('admin.login.submit');
    Route::get('/', 'AdminController@index')->name('admin.dashboard');
    //cronlogs
    Route::get('/cronlog', 'AdminController@cronlogs')->name('admin.cronlogs.cronlogs');
    Route::post('/getcronlogs', 'AdminController@getLogs');
    //cronsched
    Route::get('/cronscheduling', 'AdminController@cronsched')->name('admin.cronsched.cronsched');
    Route::post('/getsellercron', 'AdminController@getSellerCron');
    Route::post('/getNotSelectedCrons', 'AdminController@getNotSelectedCrons');
    Route::post('/addCronToSeller', 'AdminController@addCronToSeller');
    Route::post('/updateSellerCron', 'AdminController@updateSellerCron');

    //fba refund
    Route::get('/fbarefund', 'AdminController@fbarefund')->name('admin.fbarefunds.fbarefund');
    Route::post('/getfbasellers', 'AdminController@getfbasellers');
    Route::post('/getSellerOIC', 'AdminController@getSellerOIC');
    Route::post('/getSellerFNSKU', 'AdminController@getSellerFNSKU');
    Route::post('/getFulfillmentCenters', 'AdminController@getFulfillmentCenters');
    Route::post('/updateFulfillmentCenter', 'AdminController@updateFulfillmentCenter');

    Route::post('/fbarefundsellerfilter', 'AdminController@getfbasellersFiltered'); //filter for first table fba sellers
    Route::post('/getSellerOICFilter', 'AdminController@getSellerOICFiltered');     //filter for 2nd table oic table
    Route::post('/getSellerFNSKUFilter', 'AdminController@getSellerFNSKUFiltered'); //filter for 3rd table fnsku
    Route::post('/update_adminsellers', 'AdminController@update_adminsellers');
    Route::post('/update_adminOIC', 'AdminController@update_adminOIC');
    Route::post('/update_adminFNSKU', 'AdminController@update_adminFNSKU');
    Route::post('/update_oicstatus' , 'AdminController@updateStatusOIC');
    Route::post('/update_fnskustatus' , 'AdminController@updateStatusFNSKU');
  });

  Route::get('ApiColumnChecker','Crons\ApiColumnChecker@index');
});

Route::get('test','Crons\DexiController@index');
