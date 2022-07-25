<html>

<body style="background-color:#e2e1e0;font-family: Open Sans, sans-serif;font-size:100%;font-weight:400;line-height:1.4;color:#000;">
  <table style="max-width:670px;margin:50px auto 10px;background-color:#fff;padding:50px;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;-webkit-box-shadow:0 1px 3px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.24);-moz-box-shadow:0 1px 3px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.24);box-shadow:0 1px 3px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.24); border-top: solid 10px green;">
    <thead>
      <tr>
        <th style="text-align:left;"><img style="max-width: 150px;" src="https://booknpay.com/assets/img/hubwallet-logo.png" alt=""></th>
        <th style="text-align:right;font-weight:400;"><?php echo date('M d, Y');?></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="height:35px;"></td>
      </tr>
      <tr>
        <td colspan="2" style="border: solid 1px #ddd; padding:10px 20px;">
          <p style="font-size:14px;margin:0 0 6px 0;"><span style="font-weight:bold;display:inline-block;min-width:150px">Order status</span><b style="color:green;font-weight:normal;margin:0">Purchase Order</b></p>
          <p style="font-size:14px;margin:0 0 6px 0;"><span style="font-weight:bold;display:inline-block;min-width:146px">PO Number</span> <?php echo $po_number;?></p>
          <p style="font-size:14px;margin:0 0 0 0;"><span style="font-weight:bold;display:inline-block;min-width:146px">Account No.</span> <?php echo $account_no;?></p>
		   <p style="font-size:14px;margin:0 0 0 0;"><span style="font-weight:bold;display:inline-block;min-width:146px">Order Date</span> <?php echo date('M d, Y',strtotime($order_date));?></p>
        </td>
      </tr>
      <tr>
        <td style="height:35px;"></td>
      </tr>
      <tr>
        <td style="width:50%;padding:20px;vertical-align:top">
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px">Vendor Name</span> <?php echo $supplier_name;?></p>
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;">Email</span> <?php echo $supplier_email;?></p>
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;">Phone</span> <?php echo $supplier_phone;?></p>
         
        </td>
        <td style="width:50%;padding:20px;vertical-align:top">
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;">Address</span> <?php echo $supplier_address;?></p>
         
        </td>
      </tr>
      <tr>
        <td colspan="2" style="font-size:20px;padding:30px 15px 0 15px;">Items</td>
      </tr>
	  
      <tr>
	  
        <td colspan="2" style="padding:15px;">
          
		  <?php
		  $msg.="<table border='1px solid #ccc;border-collapse:collapse'><tr><th style=''>Product</th><th style=''>Brand</th><th style=''>Vendor Code</th style='border-collapse:collapse;'><th style='border-collapse:collapse;'>Qty In Pack</th><th style=''>Order Qty</th><th style='border-collapse:collapse;'>Price</th><th style='border-collapse:collapse;'>Amount</th></tr>";
		  
		  
				
				
				$msg.= "<tr><td style='border-collapse:collapse;'>ABC</td><td>XYS</td><td>435354</td><td>12</td><td>11</td><td>$100.00</td><td>$100.00</td></tr>";
			
			
		  $msg.="</table>";
		  
		  echo $msg;
		  
		  ?>
        </td>
      </tr>
    </tbody>
    <tfooter>
      <tr>
        <td colspan="2" style="font-size:14px;padding:50px 15px 0 15px;">
          <strong style="display:block;margin:0 0 10px 0;"></strong> Hubwallet <br> 22801 Ventura Blvd., Suite 300
Woodland Hills, CA 91364<br><br>
        <!--  <b>Phone:</b> (888) 707-2836<br>
          <b>Email:</b> info@booknpay.com-->
        </td>
      </tr>
    </tfooter>
  </table>
</body>

</html>