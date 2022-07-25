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
  				$str1=substr($employee_name, 0, strrpos($employee_name, ' '));
  				$emaployee_name_new=ucwords($str1);
		    $search  = array('{Employee First Name}','{Business Name}');
			$replace = array($emaployee_name_new, $business_name);

			
		    echo str_replace($search, $replace, $new_content);
		   ///echo 
	?>
</div>
<!-- content -->
</div>
</body>
</html>
