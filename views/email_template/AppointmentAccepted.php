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
<div class="header" style="margin-bottom: 14px; "> 
	<a href="#"><img src="booknpay.png" style="height: 62px; "></a>
    <span></span>
</div>
<!-- banner -->
<div style="background: #05bae1;box-shadow: 0 0 4px #d8d8d8;border-radius: 4px 4px 0 0;padding: 00;">
	<img src="reAppointment.jpg" style="height: 163px;margin: 0 auto;display: block;width: 100%;object-fit: cover;object-position: bottom;">
</div>
<!-- content -->
<div style="background: #fff;box-shadow: 0 0 4px #d8d8d8;border-radius: 0 0 4px 4px;padding: 30px;text-align: center;min-height: 400px;box-sizing: border-box;">
	<h2 style="font-size: 40px;font-weight: 400;margin-bottom: 10px;">Dear <b><?php echo $stylist_name;?></b>,</h2> 
	<p style="font-size: 21px;color: #4c4c4c;margin-top: 0;margin-bottom: 40px;">We are happy to inform you that <b><?php echo $customer_name ?></b> has been booked aappointment with you.</p>
	<table style="width: 100%; table-layout: fixed; border-collapse: collapse; box-sizing: border-box;    border-radius: 4px;
    overflow: hidden; ">
		<tbody>
			<tr> <td style="padding: 20px;background: #FFC107; ">
			<h3 style="margin-bottom: 10px;font-size: 24px;color: #fff;font-weight: 500;">When</h3>
			<p style="margin-top: 0; margin-bottom: 8px; font-size: 18px; line-height: 26px; color: #fff; "><?php echo $service->service_date;?></p>
			<p style="margin-top: 0; margin-bottom: 8px; font-size: 18px; color: #ffffff; line-height: 26px; "><?php echo $service->appointment_time;?> - <?php echo $service->appointment_end_time;?></p>
		</td>
			<td style="padding: 20px; background: #2196F3; ">
				<h3 style="margin-bottom: 10px;font-size: 24px;color: #fff;font-weight: 500;">Where</h3> 
				<p style="margin-top: 0; margin-bottom: 8px; font-size: 18px; color: #ffffff; line-height: 26px; ">Book N Pay</p>
			</td>
		</tr>
		<tr>
			<td style="padding: 20px; background: #8BC34A; ">
				<h3 style="margin-bottom: 10px;font-size: 24px;color: #fff;font-weight: 500;">What</h3>
				<p style="margin-top: 0; margin-bottom: 8px; font-size: 18px; color: #ffffff; line-height: 26px; "><?php echo $service->service_name;?></p>
				<!-- <p style="margin-top: 0; margin-bottom: 8px; font-size: 18px; color: #ffffff; line-height: 26px; ">Service 2</p> -->
			</td>
			<td style="padding: 20px;background: #FF5722;">
				<h3 style="margin-bottom: 10px;font-size: 24px;color: #fff;font-weight: 500;">With</h3>
				<p style="margin-top: 0; margin-bottom: 8px; font-size: 18px; color: #ffffff; line-height: 26px; "><?php echo $stylist_name;?></p>
			</td>
		</tr>
	</tbody></table>
<!-- <a href="#" style="padding: 12px 22px;display: inline-block;background: #05bae1;color: #fff;text-decoration: none;font-size: 20px;border-radius: 4px;margin-top: 20px;min-width: 180px;box-sizing: border-box;font-weight: 500;">Reschedule Now</a>
<a href="#" style="padding: 12px 22px;display: inline-block;background: #797979;color: #fff;text-decoration: none;font-size: 20px;border-radius: 4px;margin-top: 20px;margin-left: 7px;min-width: 180px;box-sizing: border-box;font-weight: 500;">Cancel Now</a> -->
</div>
<!-- content -->
</div>
</body>
</html>
