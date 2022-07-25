<!DOCTYPE html>
<html lang="en">
<head>
  <title>Notification Message</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>


  
<div class="container">
  
  <p>Appointment Message</p>            
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>#</th>
        <th>Email</th>
        <th>Auto</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      
	  <?php 
		$i=1;
		foreach($email_data_app as $email){ 
		?>
      <tr>
        <td><?=$i?></td>
        <td><?=$email->email_heading?></td>
        <td> 
			<input type="checkbox" name="switch[0]" id="switch_<?php echo $email->e_id;?>" value="0" <?php if ($email->is_delete==0){ echo "checked=checked";} ?> onchange="checBox('<?php echo $email->e_id;?>')">
		</td>
       
	   <td>
		<a href="<?php echo base_url(
        "administrator/settings/edit/$email->e_id"); ?>" class="btn btn-primary">View</a>
		</td>
      </tr>
		<?php $i++;}?>
      
    </tbody>
  </table>
  
  <p>Customer Message</p>   
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>#</th>
        <th>Email</th>
        <th>Auto</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      
	  <?php 
            $i=1;
            foreach($email_data_cust as $email){
		?>
      <tr>
              <td><?=$i?></td>
              <td><?=$email->email_heading?></td>
                            <td>
          <input type="checkbox" name="switch[0]" id="switch" value="0" <?php if ($email->is_delete==0){ echo "checked=checked";} ?> onchange="checBox('<?php echo $email->e_id;?>')">
          
       
            <input type="hidden" name="day[]" value="Sunday">
        </td>
              
              <td><a href="<?php echo "https://booknpay.com/api_new/settings/editMessageWebView?e_id=$email->e_id";?>" class="btn btn-primary">View</a></td>
            </tr>
		<?php $i++;}?>
      
    </tbody>
  </table>
  
  
  <p>Employee Message</p>   
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>#</th>
        <th>Email</th>
        <th>Auto</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      
	  <?php 
            $i=1;
            foreach($email_data_sty as $email){
		?>
      <tr>
              <td><?=$i?></td>
              <td><?=$email->email_heading?></td>
                            <td>
          <input type="checkbox" name="switch[0]" id="switch" value="0" <?php if ($email->is_delete==0){ echo "checked=checked";} ?> onchange="checBox('<?php echo $email->e_id;?>')">
          
       
            <input type="hidden" name="day[]" value="Sunday">
        </td>
              
              <td><a href="<?php echo "https://booknpay.com/api_new/settings/notificationMessageWebView?e_id=$email->e_id"; ?>" class="btn btn-primary">View</a></td>
            </tr>
		<?php $i++;}?>
      
    </tbody>
  </table>
  
  
</div>


</body>
</html>
