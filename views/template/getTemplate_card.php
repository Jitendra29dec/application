<?php foreach( $templateData_card as $val){

    $amount=$val->amount;
 
    $barCode=$this->checkout->bar128(stripcslashes($val->card_number),stripcslashes($amount));

  ?>
<div class="container" style="position: relative;
  text-align: center;
  color: black;">
  
  <img src="<?php echo base_url();?>assets/img/template/<?php echo $val->temp_image;?>" class="temImage1" style="width:100%;">

  <div class="top-right" style="position: absolute;
    top: 171px;
    right: 54px;
    border: 5px solid gray;
    width: 535px;
    font-size: 25px;">
    <p style="margin-left:150px;"><?php echo $barCode;?></p>
  	<p>Gift Card Number : <?php echo $val->card_number;?></p>
  	<p>Buyer Name : <?php echo $val->buyer_name;?></p>
  	<?php if($val->amount !='' && $val->amount !=0){?>	
  	<p>Amount :<?php echo $val->amount;?> </p>
  <?php } ?>
  	<p>Message : <?php echo $val->message;?></p>
  </div>
</div>

<?php } ?>
<style>
div.b128{
 border-left: 1px black solid;
 height: 30px;
} 
</style>