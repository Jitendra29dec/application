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
	 <img src="https://booknpay.com/assets/img/hubwallet-logo.png" alt="Logo" border="0" style="display: block; width: 50%; height:135px">
    <span></span>
</div>
<!-- banner -->

<!-- content -->

<div style="background: #fff;box-shadow: 0 0 4px #d8d8d8;border-radius: 0 0 4px 4px;padding: 30px;text-align: center;min-height: 400px;">
	<div><p>22801 Ventura Blvd, Suite 300 Woodland <br> Hills,CA 91364 <br> Phone:(888)707-2836</p></div>
	<?php
	$newstring = substr($order_data->order_number, -7);
	?>
	<div style="float:left;"><p><?php date('m/d/Y h:i A')?><br>Trans#: <?php echo $newstring;?><br>Cashier: <?php echo $cashier;?></p></div>
	

	
	<div style="float:right;"><p>Item Count#: <?php echo $totalCount;?><br>Tikect#:424<br>Station#:24</p></div>


	<!-- <h2 style="font-size: 22px;font-weight: 400;">ORDER #<?php echo $order_data->order_number;?></h2>  -->


	<h2 style="font-size: 20px;color: #0dd60d;margin-bottom: 0;font-weight: 500;text-align: left;padding-left: 10px;">Customer Information</h2> 
<table style="width: 100%;border-collapse: collapse;box-sizing: border-box;border-radius: 4px;overflow: hidden;margin-top: 14px;">
		<tbody>
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Name
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
			<?php echo $customerInfo[0]->customer_name;?>
			</td>
		</tr>
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Email
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
		<?php echo $customerInfo[0]->email;?>
			</td>
		</tr>
	
	</tbody>
</table>
<?php if(!empty($appointmentData)){ ?>
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
	<?php 
			foreach ($appointmentData as $key => $ap_val) {
			
		?>
	<tr>
		<td style="vertical-align: top; padding: 4px 10px; ">
			<img src="<?php echo $ap_val->stylist_image;?>" style="height: 38px;">
			<p style="margin: 0;"><?php echo $ap_val->stylist_name;?></p></td>

		<td style="vertical-align: top; padding: 4px 10px; "><?php echo $ap_val->appointment_date;?> <br> <?php echo $ap_val->appointment_time;?></td>

		<td style="
    vertical-align: top; padding: 4px 10px; "> <p style="margin: 0;"><?php echo $ap_val->service_name;?></p>
			<p style="margin: 0;">$<?php echo $ap_val->price;?></p>
			<p style="margin: 0;"><?php echo $ap_val->duration."&nbsp;"."mint";?></p>
		</td>
	</tr>
<?php } ?>
	</tbody>
</table>
</div>
<?php } ?>
<?php if(!empty($productInfo)){?>
<h2 style="font-size: 20px;color: #0dd60d;margin-bottom: 0;font-weight: 500;text-align: left;padding-left: 10px;">Product Information</h2> 
<div style="    border-bottom: 1px solid #ddd;
    padding-bottom: 14px;">
	<table style="width: 100%;border-collapse: collapse;box-sizing: border-box;border-radius: 4px;overflow: hidden;margin-top: 14px;table-layout: fixed;">
		<tbody>
			
		<td style="padding: 4px 10px;font-size: 16px;vertical-align: top;border-bottom: 1px solid #eee;">
		<strong>Product Name</strong>
						</td>

							<td style="padding: 4px 10px;font-size: 16px;vertical-align: top;border-bottom: 1px solid #eee;">
		 <strong>Quantity</strong>
		
			</td>
			<td style="padding: 4px 10px;font-size: 16px;vertical-align: top;border-bottom: 1px solid #eee;">
		 <strong>Product Price</strong>
		
			</td>
	</tr>
	<?php foreach($productInfo as $pr_val){?>
	<tr>
		

		

		<td style="
    vertical-align: top; padding: 4px 10px; "> <p style="margin: 0;"><?php echo $pr_val->name;?></p>
			
			
		</td>
		<td style="
    vertical-align: top; padding: 4px 10px; "> <p style="margin: 0;"></p>
			
			<p style="margin: 0;">X <?php echo $pr_val->quant;?></p>
		</td>
		<td style="
    vertical-align: top; padding: 4px 10px; "> <p style="margin: 0;"></p>
			
			<p style="margin: 0;">$<?php echo $pr_val->price;?></p>
		</td>
	</tr>
	<?php } ?>
	
			
	</tbody>
</table>
</div>
<?php } ?>
<?php if(!empty($gift_certificate)){?>
<h2 style="font-size: 20px;color: #0dd60d;margin-bottom: 0;font-weight: 500;text-align: left;padding-left: 10px;">Gift Certificate</h2> 
<div style="    border-bottom: 1px solid #ddd;
    padding-bottom: 14px;">
	<table style="width: 100%;border-collapse: collapse;box-sizing: border-box;border-radius: 4px;overflow: hidden;margin-top: 14px;table-layout: fixed;">
		<tbody>
			<tr> 
				<td style="padding: 4px 10px;font-size: 16px;vertical-align: top;border-bottom: 1px solid #eee;">
			<strong>Gift Certificate Number</strong>
			
		</td>
		<td style="padding: 4px 10px;font-size: 16px;vertical-align: top;border-bottom: 1px solid #eee;">
		 <strong>Amount Price</strong>
		
			</td>
	</tr>
	<?php foreach($gift_certificate as $cert_val){?>
	<tr>
		<td style="vertical-align: top; padding: 4px 10px; ">
		<p style="margin: 0;"><?php echo $cert_val->gift_no;?></p>
			</td>

		
<td style="vertical-align: top; padding: 4px 10px; ">
		<p style="margin: 0;">$<?php echo $cert_val->amount;?></p>
			</td>
				
	<?php } ?>
	
			
	</tbody>
</table>
</div>
<?php } ?>
<?php if(!empty($gift_card)){?>
<h2 style="font-size: 20px;color: #0dd60d;margin-bottom: 0;font-weight: 500;text-align: left;padding-left: 10px;">Gift Card</h2> 
<div style="    border-bottom: 1px solid #ddd;
    padding-bottom: 14px;">
	<table style="width: 100%;border-collapse: collapse;box-sizing: border-box;border-radius: 4px;overflow: hidden;margin-top: 14px;table-layout: fixed;">
		<tbody>
			<tr> 
				<td style="padding: 4px 10px;font-size: 16px;vertical-align: top;border-bottom: 1px solid #eee;">
			<strong>Gift Card Number</strong>
			
		</td>
		<td style="padding: 4px 10px;font-size: 16px;vertical-align: top;border-bottom: 1px solid #eee;">
		 <strong>Amount Price</strong>
		
			</td>
		

			
			<td style="padding: 4px 10px;font-size: 16px;vertical-align: top;border-bottom: 1px solid #eee;">
		 <strong>Issue Date</strong>
		
			</td>
	</tr>
	<?php foreach($gift_card as $card_val){?>
	<tr>
		<td style="vertical-align: top; padding: 4px 10px; ">
		<p style="margin: 0;"><?php echo $card_val->gift_card_no;?></p>
			</td>

		<td style="vertical-align: top; padding: 4px 10px; ">
		<p style="margin: 0;">$<?php echo $card_val->amount;?></p>
			</td>
			

		<td style="
    vertical-align: top; padding: 4px 10px; "> 
			<?php
			$issueDate=date("m/d/Y", strtotime($card_val->issue_date));
			//$issueDate=date_format($card_val->issue_date,"m/d/Y");
			?>
			<p style="margin: 0;"><?php echo $issueDate;?></p>
		</td>
	</tr>
	<?php } ?>
	
			
	</tbody>
</table>
</div>
<?php } ?>
<h2 style="font-size: 20px;color: #0dd60d;margin-bottom: 0;font-weight: 500;text-align: left;padding-left: 10px;">Order Information
</h2> 
<table style="width: 100%;border-collapse: collapse;box-sizing: border-box;border-radius: 4px;overflow: hidden;margin-top: 14px;">
		<tbody>
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Order Number:
		</td>
			<td style="padding: 4px 10px;font-size: 16px;margin-left:-10px;">
			#<?php echo $order_data->order_number;?>
			</td>
		</tr>
			<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Order Date:
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
	<?php echo date('m/d/Y');?>
			</td>
		</tr>
		
		<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Order Status:
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
		<?php echo $order_data->order_status;?>
			</td>
		</tr>
		<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Sub total:
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
				<?php
				$subTotal=$order_data->order_amount - $order_data->tax_amount - $order_data->discount_amount;
				?>
			$ <?php echo number_format($subTotal,2);?>
			</td>
		</tr>
		<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Total Tax:
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
			$ <?php echo number_format($order_data->tax_amount,2);?>
			</td>
		</tr>
		<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Total Discount:
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
			-$ <?php echo number_format($order_data->discount_amount,2);?>
			</td>
		</tr>
		<tr> <td style="padding: 4px 10px;font-weight: 600;font-size: 16px;">
			Total:
		</td>
			<td style="padding: 4px 10px;font-size: 16px;">
			$ <?php echo number_format($order_data->order_amount,2);?>
			</td>
		</tr>
	
	</tbody>
</table>
<?php if(!empty($payment_data)){?>
<table>
	<?php
		foreach($payment_data as $valPay){
			if($valPay->payment_type=='Cash'){
				$cashAmount +=$valPay->amount;
			}else{
				$cardAmount+=$valPay->amount;
			}

		}


	 ?>
	 <?php if(!empty($cashAmount) && $cashAmount !=0){?>
	 <tr>
	 	<td>
	 	Cash
	 </td>
	 <td><?php echo $cashAmount;?></td>
	</tr>
	<?php } ?>
	<?php if(!empty($cardAmount) && $cardAmount !=0){?>
	<tr>

	 	<td>
	 	Card
	 </td>
	 <td><?php echo $cardAmount;?></td>
	</tr>
	<?php } ?>
	<tr>
		<td>
	 	Amount Tendered
	 </td>
	 <td><?php echo $cashAmount;?></td>
	</tr>
	<tr>
		<td>
	 	Change due
	 </td>
	 <td><?php echo $order_data->return_amount;?></td>
	</tr>
</table>
<?php } ?>
</div>
<!-- content -->
</div>
</body>
</html>
