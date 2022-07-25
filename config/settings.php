<?php
$config['emails'] = array(
    "from_email" => "jitendra29dec@gmail.com",
    "business_email" => "info@quickorder.com"
);

$config['payment'] = array(
    //"url" => "https://secure.ebs.in/pg/ma/payment/request",
	"url"=>"https://www.sandbox.paypal.com/cgi-bin/webscr",
	"account_id" => "22673",
	"cmd"=>"_xclick",
    "business" => "jitendra29dec@gmail.com",
    "bank_code" => "",
    "card_brand" => "",
	"no_shipping"=>"0",
    "channel" => "0",
	"currency" => "USD",
    "currency_code" => "USD",
    "display_currency" => "USD",
    "display_currency_rates" => "1",
    "emi" => "",
    "mode" => "TEST",
    "page_id" => "",
    "payment_option" => "",
    "payment_mode" => "",
	"lc"=>"AU",
	"bn"=>"PP-BuyNowBF",
   "key" => "c18f5e50b8a0c3d86a6ebacf6007eb93"
);
?>
