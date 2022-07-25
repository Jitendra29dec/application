<!DOCTYPE>
<html>
<head>
	<meta charset="UTF-8">
	<title>	</title>
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="margin:0;font-family: 'Poppins', sans-serif;">

<div class="wrapper" style="max-width: 700px;font-family: 'Poppins', sans-serif; width: 100%; margin: auto; background: #f7f7f7; padding: 38px 80px; box-sizing: border-box; ">
<!-- logo -->
<!--<div class="header" style="margin-bottom: 14px; "> 
	<a href="#"><img src="https://booknpay.com/assets/img/hubwallet-logo.png" style="height: 62px; "></a>
    <span></span>
</div>-->
<!-- banner -->
<!--<div style="background: #05bae1;box-shadow: 0 0 4px #d8d8d8;border-radius: 4px 4px 0 0;padding: 00;">
	<img src="https://booknpay.com/assets/img/email_image/reAppointment.jpg" style="height: 163px;margin: 0 auto;display: block;width: 100%;object-fit: cover;object-position: bottom;">
</div>-->
<!-- content -->
<div style="background: #fff;box-shadow: 0 0 4px #d8d8d8;border-radius: 0 0 4px 4px;padding: 30px;min-height: 400px;box-sizing: border-box;margin-left:15px;">
	<?php
			
			$getDataNew=getImageTemplate($vendor_id,$template_type);
			//echo "<pre>";print_r($getDataNew);exit;
  				$new_content= $getDataNew->email_content; 
  			//	echo $new_content;die;

  				$serviceValue=str_replace("-", "<br />", $getData['service_name']); 
  				$start_time=str_replace("/n", "<br />", $getData['start_time']); 
  				$dt=str_replace('/n', "<br />", $getData['appointment_date']); 

  				
		    $search  = array('{Customer First Name}', '{Day Date}','{Time}','{Business Name}','{Service Name} with {Employee Name}', '{Service Time}','{Street Address}','{City}', '{State}','{Zip Code}','{Cancellation Policy}','{Business Phone Number}','{Duration}');
			$replace = array($getData['customer_name'], $dt,$start_time,$getData['store_name'],$serviceValue,$start_time,$getData['address'],$getData['city'],$getData['state'],$getData['zipcode'],$getData['policy'],$getData['business_phone'],$getData['duration']);

			$search1  = array('{Customer First Name}', '{Date}','{Time}','{Business Name}');
			$replace1 = array($getData['customer_name'], $getData['newdate'],$getData['newTime'],$getData['store_name']);
			//echo "<pre>";print_r($replace1);exit;
			$getsmsData=str_replace($search1, $replace1, $getDataNew->sms_content);
			//echo $getsmsData;die;
			test($getsmsData,$phone);
		    echo str_replace($search, $replace, $new_content);
		   ///echo 
	?>
</div>
<!-- content -->
</div>
</body>
</html>
