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
	<a href="#"><img src="https://booknpay.com/assets/img/booknpay.png" style="height: 62px; "></a>
    <span></span>
</div>
<!-- banner -->
<div style="background: #05bae1;box-shadow: 0 0 4px #d8d8d8;border-radius: 4px 4px 0 0;padding: 00;">
	<img src="https://booknpay.com/assets/img/order.jpg" style="height: 303px;margin: 0 auto;display: block;width: 100%;object-fit: cover;object-position: bottom;">
</div>
<!-- content -->
<div style="background: #fff;box-shadow: 0 0 4px #d8d8d8;border-radius: 0 0 4px 4px;padding: 30px;text-align: center;min-height: 400px;">
	<h2 style="font-size: 22px;margin-bottom: 10px;font-weight: 400;">ORDER #<?php echo $editData->order_number;?></h2> 
	<h2 style="font-size: 20px;color: #0dd60d;margin-bottom: 0;font-weight: 500;text-align: left;padding-left: 10px;">Customer Information</h2> 
<table style="width: 100%;border-collapse: collapse;box-sizing: border-box;border-radius: 4px;overflow: hidden;margin-top: 14px;">
		<tbody>
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Name
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
			<?php echo $editData->customer_name;?>
			</td>
		</tr>
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Email
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
		 <?php echo $editData->email;?>
			</td>
		</tr>
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Mobile No.
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
			<?php echo $editData->mobile_phone;?>
			</td>
		</tr>
	
	</tbody>
</table>
<?php if(!empty($appointmentData)){?>
<h2 style="font-size: 20px;color: #0dd60d;margin-bottom: 0;font-weight: 500;text-align: left;padding-left: 10px;">Appointment Information</h2> 
<div style="    border-bottom: 1px solid #ddd;
    padding-bottom: 14px;">
	<table style="width: 100%;border-collapse: collapse;box-sizing: border-box;border-radius: 4px;overflow: hidden;margin-top: 14px;table-layout: fixed;">
		<tbody>
			<tr> 
				<td style="padding: 4px 10px;font-size: 16px;vertical-align: top;border-bottom: 1px solid #eee;">
			<strong>Stylist</strong>
			
		</td>
		<td style="padding: 4px 10px;font-size: 16px;vertical-align: top;border-bottom: 1px solid #eee;">
		<strong>Appointment Date</strong>
						</td>


			<td style="padding: 4px 10px;font-size: 16px;vertical-align: top;border-bottom: 1px solid #eee;">
		 <strong>Service</strong>
		
			</td>
	</tr>
	<tr>
		<?php foreach($appointmentData as $ap_val){?>
		<td style="vertical-align: top; padding: 4px 10px; ">
			<img src="<?php echo $ap_val->stylist_image;?>" style="height: 38px;">
			<p style="margin: 0;"><?php echo $ap_val->stylist_name;?></p></td>

		<td style="vertical-align: top; padding: 4px 10px; "><?php echo $ap_val->app_date; ?> <br> <?php echo $ap_val->apt_time; ?></td>

		<td style="
    vertical-align: top; padding: 4px 10px; "> <p style="margin: 0;"><?php echo $ap_val->service_name; ?></p>
			<p style="margin: 0;"><?php echo $ap_val->duration; ?> Min</p>
			<p style="margin: 0;">$<?php echo $ap_val->price; ?></p>
		</td>
	</tr>
<?php }} ?>
	
			
	</tbody>
</table>
</div>


<h2 style="font-size: 20px;color: #0dd60d;margin-bottom: 0;font-weight: 500;text-align: left;padding-left: 10px;">Order Information
</h2> 
<table style="width: 100%;border-collapse: collapse;box-sizing: border-box;border-radius: 4px;overflow: hidden;margin-top: 14px;">
		<tbody>
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Order Number:
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
			#<?php echo $editData->order_number;?>
			</td>
		</tr>
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Order Date:
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
		<?php echo $editData->order_date;?>
			</td>
		</tr>
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Order Type:
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
		Card
			</td>
		</tr>
		<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Order Status:
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
		<?php echo $editData->payment_status;?>
			</td>
		</tr>
		<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Total:
		</td>
			<td style="padding: 4px 10px;font-size: 16px;font-weight: 600;">
			$ <?php echo $editData->payment_amount;?>
			</td>
		</tr>
	
	</tbody>
</table>



<h2 style="font-size: 20px;color: #0dd60d;margin-bottom: 0;font-weight: 500;text-align: left;padding-left: 10px;">Transaction Information
</h2> 
<table style="width: 100%;border-collapse: collapse;box-sizing: border-box;border-radius: 4px;overflow: hidden;margin-top: 14px;">
		<tbody>
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Payment Methods
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
			Payment process by Card 
			</td>
		</tr>
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Payment Status	
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
		<?php echo $editData->payment_status;?>
			</td>
		</tr>
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Transaction ID
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
			<?php echo $editData->transaction_id;?>
			</td>
		</tr>
			</tbody>
</table>


<h2 style="font-size: 20px;color: #0dd60d;margin-bottom: 0;font-weight: 500;text-align: left;padding-left: 10px;">Payment Details
</h2> 
<table style="width: 100%;border-collapse: collapse;box-sizing: border-box;border-radius: 4px;overflow: hidden;margin-top: 14px;">
		<tbody>
			
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Total Amount:
		</td>
			<td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			$ <?php echo $editData->payment_amount;?>
			</td>
		</tr>
			</tbody>
</table>

</div>
<!-- content -->
</div>
</body>
</html>
