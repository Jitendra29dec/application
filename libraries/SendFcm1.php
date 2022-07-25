<?php    
    defined('BASEPATH') OR exit('No direct script access allowed');
    class SendFcm1{
        
        // API access key from Google API's Console
	function sendNotification($title,$body,$registrationIds,$subtitle="")
	{
	//$API_ACCESS_KEY="AAAAwDKqE9g:APA91bH5KP3XJ90-xU1Qd25Ady-pv3A3fRf0D6BPZKsfYh-9ZJAEDMQ3Q85r-ViGufn3ShjL1grta__TJXuSSH0VAtmwob_XmB4-I9T_vhFdeWfsTjU8O8QAgtaNkvxJVqU7Bxv852xH";
	$API_ACCESS_KEY="AAAAwDKqE9g:APA91bH5KP3XJ90-xU1Qd25Ady-pv3A3fRf0D6BPZKsfYh-9ZJAEDMQ3Q85r-ViGufn3ShjL1grta__TJXuSSH0VAtmwob_XmB4-I9T_vhFdeWfsTjU8O8QAgtaNkvxJVqU7Bxv852xH";
	// prep the bundle
	$msg = array
	(
		'message' 	=> $body,
		'title'		=> $title,
		'subtitle'	=> $subtitle,
		'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
		'vibrate'	=> 1,
		'sound'		=> true,
		'largeIcon'	=> 'large_icon',
		'smallIcon'	=> 'small_icon'
	);
	
	$fields = array
	(
		'registration_ids' 	=> $registrationIds,
		'notification' => array('title' => $title, 'body' => $body,'sound'=>'notification_sound'),
		'data'			=> $msg
	);
	 
	$headers = array
	(
		'Authorization: key=' . $API_ACCESS_KEY,
		'Content-Type: application/json'
	);
	 
	$ch = curl_init();
	curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
	curl_setopt( $ch,CURLOPT_POST, true );
	curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
	//curl_setopt( $ch,CURLOPT_TIMEOUT_MS, 10);
	curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
	$result = curl_exec($ch );
	curl_close( $ch );
	return $result;
	//echo $result."<br>";
	}
    }
?>