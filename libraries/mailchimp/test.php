<?php

require_once('vendor/autoload.php');



/* $client = new MailchimpMarketing\ApiClient();

$client->setConfig([
    'apiKey' => 'c799b76606792859138325910554f01d-us20',
    'server' => 'us20',
]); */

/* $list_id = "b3baa80859";

try {
    $response = $client->lists->addListMember($list_id, [
        "email_address" => $email,
        "status" => "subscribed",
        "merge_fields" => [
          "STORE_NAME" => $vendor_name,
          "PHONE" => $phone
        ]
    ]);
	echo 'a';
    print_r($response);
} catch (MailchimpMarketing\ApiException $e) {
    echo $e->getMessage();
} */


$client = new MailchimpMarketing\ApiClient();
$client->setConfig([
    'apiKey' => 'c799b76606792859138325910554f01d-us20',
    'server' => 'us20',
]);

$response = $client->lists->createList([
    "name" => "New Audeince from API",
    "permission_reminder" => "You are receiving this email because you opted in via our website.",
    "email_type_option" => true,
    "contact" => [
        "company" => "Test Company",
        "address1" => "GK",
        "city" => "Delhi",
        "country" => "India",
		"state" => "delhi",
        "zip" => "110094",
		"language" => "language",
		"from_email" => "abc@hotmail.com",
		"subject" => "test subject",
		
    ],
    "campaign_defaults" => [
        "from_name" => "abc",
        "from_email" => "abc@hotmail.com",
        "subject" => "test subject",
        "language" => "language",
      
    ],
]);
print_r($response);

?>