<style>
	.page-content-wrap{
		
		margin-top:15px;
	}
	.inside-body{
		background-color:#fff;
		padding:25px;
	}
	.heading{
		
		padding-left:28px;
		padding-right:28px;
	}
	.customer_info tr td{
		padding:5 5 5 0;
		font-size:14px;
	}
	
	.customer_info tr th{
		padding:5 5 5 0;
		font-size:14px;
	}
	.table_heading{
		
		background-color:#f9f9f9;
		padding:10 10 10 6;
		margin-top:20px;
		
	}
	
</style>

<div class="page-content-wrap">

    <div class="row">
        <div class="col-md-12">
		
		<div class="heading">
<h3>ORDER # : <?php echo $editData->order_number; ?></h3>

<div class="push-down-10 pull-right">
                        <a href="<?php echo base_url(); ?>orders/invoice/<?php echo $editData->order_id; ?>" ><button class="btn btn-primary" style="margin-top:-80px;"><span class="fa fa-print"></span> Print</button></a>&nbsp;<a href="javascript:void(0);" onclick="sendInvoice('<?php echo $order_id;?>');" class="btn btn-primary" style="margin-top:-80px;">Send Invoice</a>&nbsp;
                           <a href="javascript:void(0);"  class="btn btn-primary" data-toggle="modal" data-target="#myModal_get_all_customer123" style="margin-top:-80px;">Distribute Tip</a>
                            <?php 
                            $permissionData = getPermission($this->session->userdata('role_id'));
                            if($editData->is_refund==0 && in_array(6, $permissionData)){?>
                            <a href="<?php echo base_url(); ?>orders/getRefundData/<?php echo $editData->order_id; ?>"  class="btn btn-primary" style="margin-top:-80px;">Get Refund</a>
                        <?php } ?>
                        <!-- <div class="dropdown" style="margin-top:-80px;margin-left:-200px;">
                           <span class="btn btn-default" style="margin-top:-97px;">Action</span>
                              <div class="dropdown-content">
                                   <a href="<?php echo base_url(); ?>orders/invoice/<?php echo $editData->order_id; ?>"><button class="btn btn-primary"><span class="fa fa-print"></span> Print</button></a><br>
                          <a href="javascript:void(0);" onclick="sendInvoice('<?php echo $order_id;?>');" class="btn btn-primary" style="margin-top:-97px;">Send Invoice</a>
                           <a href="javascript:void(0);"  class="btn btn-primary" data-toggle="modal" data-target="#myModal_get_all_customer123" style="margin-top:-97px;">Distribute Tip</a>
                               </div>
                         </div> -->

                       <!--  <a href="<?php echo base_url(); ?>orders/invoice/<?php echo $editData->order_id; ?>"><button class="btn btn-default"><span class="fa fa-print"></span> Print</button></a>
                          <a href="javascript:void(0);" onclick="sendInvoice('<?php echo $order_id;?>');" class="btn btn-primary" style="float:right;">Send Invoice</a> -->
                    </div>
					
					</div>
            <div class="panel panel-default">
                <div class="panel-body">
				<div class="inside-body">
                      <span id="sendMessageInvoice"></span>
                    <!--   <?php //echo "<pe>";print_r($editData);exit;?> -->
                    
                    
                    <!-- INVOICE -->
                    <div class="invoice">

                        <div class="row">
						<div class="col-md-3">
                                <div class="invoice-address">
                                    <h5><b>Customer Information</b></h5>
                                    <table class="customer_info">
									<?php foreach($customerInfo as $cust_val){?>
										<tr>
											<td>Name : </td>
											<td><?php echo ucwords($cust_val->firstname.' '.$cust_val->lastname); ?> </td>
										</tr>
										
										<tr>
											<td>Email : </td>
											<td><?php echo $cust_val->email; ?></td>
										</tr>
										
										
									
									<?php if($cust_val->mobile_phone!=''){ ?>
										<tr>
											<td>Mobile : </td>
											<td><?php  echo $cust_val->mobile_phone; ?></td>
										</tr>
										
									<?php }?>
										
									<?php if($cust_val->home_phone!=''){ ?>
										<tr>
											<td>Home Phone : </td>
											<td><?php echo $cust_val->home_phone; ?></td>
										</tr>
									<?php }?>
									
									<?php if($cust_val->work_phone!=''){ ?>
										<tr>
											<td>Work Phone : </td>
											<td><?php echo $cust_val->work_phone; ?></td>
										</tr>
									<?php }?>
									
									<?php if($cust_val->note!=''){ ?>
										<tr>
											<td>Note : </td>
											<td><?php echo $cust_val->note; ?></td>
										</tr>
									<?php }?>
									
									
									
									
									<?php }?>
									
									<tr>
										<td colspan="2"> <?php if($customerSignature->signature!=NULL || $customerSignature->signature!=''){?>
										<img src="<?php echo base_url($this->layout->getAssetPath().'img/signature/'.$customerSignature->signature)?>" width="100" height="50">
								   <?PHP }?></td>
									</tr>
									</table>
								  
                                   
                                </div>
                            </div>
							
							<div class="col-md-3">

                                <div class="invoice-address">
                                    <h5><b>Order Information</b></h5>
                                    <table class="customer_info">
                                        <tbody>
                                        <tr>
                                            <td width="">Order Number:</td>
                                            <td class=""><?php echo $editData->order_number; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Order Date:</td>
                                            <td class=""><?php
                                                $originalDate = $editData->created_date;
                                                $newDate = date("d F Y", strtotime($originalDate));
                                                echo $newDate; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Order Type:</td>
                                            <td class=""><?php
                                                if($editData->order_type == "1"){
                                                    $orderType = "Web";
                                                }
                                                else if($editData->order_type == "2"){
                                                    $orderType = "Admin";
                                                }
                                                echo $orderType; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Order Status:</td>
                                            <td class=""><?php
                                                /* if($editData->status_id == "1"){
                                                    $orderStatus = "Incomplete";
                                                }
                                                else if($editData->status_id == "2"){
                                                    $orderStatus = "Pending";
                                                }
                                                else if($editData->status_id == "3"){
                                                    $orderStatus = "Processed";
                                                }
                                                else if($editData->status_id == "4"){
                                                    $orderStatus = "Partially Shipped";
                                                }
                                                else if($editData->status_id == "5"){
                                                    $orderStatus = "Shipping";
                                                }
                                                else if($editData->status_id == "6"){
                                                    $orderStatus = "Shipped";
                                                }
                                                else if($editData->status_id == "7"){
                                                    $orderStatus = "Partially Returned";
                                                }
                                                else if($editData->status_id == "8"){
                                                    $orderStatus = "Returned";
                                                }
                                                else if($editData->status_id == "9"){
                                                    $orderStatus = "Canceled";
                                                }
                                                else if($editData->status_id == "10"){
                                                    $orderStatus = "Completed";
                                                }
                                                echo $orderStatus; */
												
												echo "Completed";
                                            ?></td>
                                        </tr>
                                        
                                        </tbody>
                                    </table>

                                </div>

                            </div>
							
							<div class="col-md-3">
							<h5><b>Payment Detail</b></h5>
                                <table class="customer_info">
                                    <tbody>
                                    
									<tr>
                                        <td width="">Sub Total : </td>
                                        <td class="text-right">$<?php echo $editData->order_amount; ?> </td>
                                    </tr><?php
                                    if(!empty($editData->service_tax_amount)){
                                    ?><tr>
                                        <td>SERVICE TAX (<?php echo $taxInfo[0]->value; ?> %):</td>
                                        <td class="">$ <?php echo $editData->service_tax_amount; ?> </td>
                                    </tr>
									<?php }?>
                                   <!--  <tr>
                                        <td><strong>Discount (<?php
                                                if(!empty($editData->coupon_id)){
                                                    echo $couponInfo->coupon_number;
                                                }
                                                else{
                                                    echo '0%';
                                                } ?>):</strong></td>
                                        <td class="text-right">$ <?php echo $editData->discount_amount; ?> </td>
                                    </tr> -->
                                 <?php    
                                 if(!empty($editData->tip_amount)){ ?>
                                     <tr>
                                        <td>Tip Amount : </td>
                                        <td class="text-right">$ <?php echo number_format($editData->tip_amount,2); ?> </td>
                                    </tr>
                                <?php } ?>
                                <?php /*    
                                 if(!empty($editData->member_ship_amount)){ ?>
                                     <tr>
                                        <td>Membership Amount</td>
                                        <td class="">$ <?php echo number_format($editData->member_ship_amount,2); ?> </td>
                                    </tr>
                                <?php } */ ?>
                                     <!-- <tr>
                                        <td><strong>Discount (<?php
                                                if(!empty($editData->coupon_id)){
                                                    echo $couponInfo->coupon_number;
                                                }
                                                else{
                                                    echo '0%';
                                                } ?>):</strong></td>
                                        <td class="text-right">$ <?php echo number_format($editData->discount_amount,2); ?> </td>
                                    </tr> -->
                                    <tr>
                                        <td>IOU Amount</td>
                                        <td class="text-right">$<?php 
                                        /*if($totalProductPrice==0 || $service_price==0){
                                             echo round($paymentInfo->amount,2);
                                        }else{*/
                                          echo number_format($editData->iou_amount,2);    
                                       
                                        /*}
*/
                                        ?> </td>
                                    </tr>
                                     <?php if($editData->gift_cert_amount !='' && $editData->gift_cert_amount!=0){ ?>
                                    <tr>
                                        <td>Gift Certificate Amount</td>
                                        <td class="text-right">$<?php 
                                        /*if($totalProductPrice==0 || $service_price==0){
                                             echo round($paymentInfo->amount,2);
                                        }else{*/
                                          echo number_format($editData->gift_cert_amount,2);    
                                       
                                        /*}
*/
                                        ?> </td>
                                    </tr>
                                <?php } ?>
                                <?php if($editData->gift_cart_amount !='' && $editData->gift_cart_amount!=0){?>
                                    <tr>
                                        <td>Gift Cart Amount : </td>
                                        <td class="text-right">$<?php 
                                        /*if($totalProductPrice==0 || $service_price==0){
                                             echo round($paymentInfo->amount,2);
                                        }else{*/
                                          echo number_format($editData->gift_cart_amount,2);    
                                       
                                        /*}
*/
                                        ?> </td>
                                    </tr>
                                <?php } ?>
                                <?php if($editData->rewards_money !='' && $editData->rewards_money !=0){ ?>
                                    <tr>
                                        <td>Points Money : </td>
                                        <td class="text-right">$<?php 
                                        /*if($totalProductPrice==0 || $service_price==0){
                                             echo round($paymentInfo->amount,2);
                                        }else{*/
                                          echo number_format($editData->rewards_money,2);    
                                       
                                        /*}
*/
                                        ?> </td>
                                    </tr>
                                <?php } ?>
                                 <?php if($editData->tax_amount !='' && $editData->tax_amount !=0){ ?>
                                    <tr>
                                        <td>Tax Amount : </td>
                                        <td class="text-right">$<?php 
                                        /*if($totalProductPrice==0 || $service_price==0){
                                             echo round($paymentInfo->amount,2);
                                        }else{*/
                                          echo number_format($editData->tax_amount,2);    
                                       
                                        /*}
*/
                                        ?> </td>
                                    </tr>
                                <?php } ?>
                                 <?php if($editData->discount_amount !='' && $editData->discount_amount !=0){ ?>
                                    <tr>
                                        <td>Discount : </td>
                                        <td class="text-right"> $<?php 
                                        /*if($totalProductPrice==0 || $service_price==0){
                                             echo round($paymentInfo->amount,2);
                                        }else{*/
                                          echo number_format($editData->discount_amount,2);    
                                       
                                        /*}
*/
                                        ?> </td>
                                    </tr>
                                <?php } ?>
                                    <tr class="total">
                                        <td>Total Amount : </td>
                                        <td class="text-right">$<?php
                                        //echo "<pre>";print_r($paymentInfo);
                                            if(isset($paymentInfo)){
                                               // echo $paymentInfo->amount."---".$editData->iou_amount;
                                                $payment_total=$paymentInfo->amount-$editData->iou_amount;
                                                echo number_format($payment_total,2);
                                            }
                                            else{
                                                $order_amount = $editData->order_amount;
                                                $service_tax_amount = $editData->service_tax_amount;
                                                
                                                $discount_amount = $editData->discount_amount;
                                               // $totalPayment = $editData->final_amount;
                                                //echo round($totalPayment,2);
                                            }
                                        ?> </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
							
							
							
							<div class="col-md-3">
                                <div class="invoice-address">
                                    <h5><b>Payment Mothods</b></h5>
                                    <table class="customer_info">
									
										<tr>
											<td>Cash  : </td>
											<td>$<?php echo number_format($editData->cash_amount,2);?> </td>
										</tr>
										
										<tr>
											<td>Card : </td>
											<td>$<?php echo number_format($editData->credit_card_amount,2);?></td>
										</tr>
										
										<tr>
											<td>IOU : </td>
											<td>$<?php echo number_format($editData->iou_amount,2);?></td>
										</tr>
									
									</table>
									
									
								
								  
                                   
                                </div>
                            </div>
							
                            <div class="col-md-12">
                                <div class="invoice-address">
									<?php 
									$service_total = 0;
									if(!empty($appointmentData)){ ?>
                                    <h5 class="table_heading"><b>Appointment Detail</b></h5>
									<table class="customer_info" width="100%">
									<thead>
										<tr>
											
											<th>Appointment Date</th>
											<th>Stylist</th>
											<th>Service</th>
											<th>Price</th>
											<th>Duration</th>
										</tr>
									</thead>
                                        <tbody>
										<?php 
										
										foreach($appointmentData as $ap){ ?>
                                        <tr>
                                            
                                            <td class=""><?php echo date('M d Y h:i a',strtotime($ap->appointment_date)); ?></td>
                                            <td class=""><?php echo ucwords($ap->stylist_name); ?></td>
                                            <td class=""><?php echo ucwords($ap->service_name); ?></td>
                                            <td class="">$<?php echo $ap->price; ?></td>
                                            <td class=""><?php echo $ap->duration; ?></td>
                                        </tr>
										<?php 
										$service_total += $ap->price;
										}?>
                                        
                                        </tbody>
                                    </table>
									<?php }?>
                                </div>

                            </div>
                           
                            
                            
                        </div>
						<?php error_reporting(1); if(count(@$productInfo)>0){ ?>
                        <div class="table-invoice">
						<h5 class="table_heading"><b>Product Detail</b></h5>
                            <table class="customer_info"  width="100%">
                                <tbody>
								
                                <tr>
                                    <th>Product Name</th>
                                    <th class="">Unit Price</th>
                                    <th class="">Quantity</th>
                                    <th class="">Total</th>
                                </tr>
                                <?php
									  
                                    $j = 0;
                                    $k = 0;
                                    $topupAmountTotal = 0;
                                    foreach($productInfo as $keyProduct => $valueProduct) {
                                    ?><tr>
                                        <td>
                                            <?php echo $valueProduct->name; ?>
                                        </td>
                                        
                                        <td class="">$<?php echo $valueProduct->price; ?>
                                            </td>
                                        <td class=""><?php echo $valueProduct->quant; ?></td>
                                        <td class="">$<?php
                                            $totalProductPrice = ($valueProduct->price * $valueProduct->quant);
                                            echo number_format($totalProductPrice,2);
                                            ?> </td>
                                    </tr><?php
                                }
                                ?>
                               
                                </tbody>
                            </table>
                        </div>
						
						<?php }?>
                            
                        <div class="row">
						<?PHP /*
                            <div class="col-md-4"><?php
                                if(isset($paymentInfo)) {
                                    ?><table class="table table-striped">
                                    <tbody>
                                    <tr>
                                        <td colspan="3" style="text-align: center"><h4>Transaction Information</h4></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payment Methods</strong></td>
                                        <td><strong>Payment Status</strong></td>
                                        <td><strong>Transaction ID</strong></td>
                                    </tr>
                                    <tr>
                                        <td><?php
                                            if (isset($paymentInfo)) {
                                                if ($paymentInfo->payment_type == "1") {
                                                    ?>Payment received by Cash Payment<?php
                                                } else if ($paymentInfo->payment_type == "2") {
                                                    ?>Payment received by Debit Card<?php
                                                } else if ($paymentInfo->payment_type == "3") {
                                                    ?>Payment received by Credit Card<?php
                                                } else if ($paymentInfo->payment_type == "4") {
                                                    ?>Payment received by Net Banking<?php
                                                } else if ($paymentInfo->payment_type == "5") {
                                                    ?>Payment received by EBS<?php
                                                }
                                            }
                                            ?></td>
                                        <td><?php if ($paymentInfo->status_id == "1") {
                                                ?>Pending<?php
                                            } else if ($paymentInfo->status_id == "2") {
                                                ?>Success<?php
                                            } else if ($paymentInfo->status_id == "3") {
                                                ?>Reject<?php
                                            } ?></td>
                                        <td><?php if (!empty($paymentInfo->transaction_id)) {
                                                echo $paymentInfo->transaction_id;
                                            } else {
                                                echo 'N/A';
                                            } ?></td>
                                    </tr>
                                    </tbody>
                                    </table><?php
                                }
                                

                               
                                ?>
                            </div>
							 */?>
							 <?php if(!empty($stylist_id)) { ?>
                            <div class="col-md-12">
                                <div class="invoice-address">
								<h5 class="table_heading"><b>Tip Distribution</b></h5>
                                     
                                    <table class="customer_info" width="100%">
                                        <tbody>
                                        
                                        <tr> 
                                            <th width="">Employee Name</th>
                                            <th width="" align="">Amount</th>
                                            <th width="" align="">Tip Date</th>
                                        </tr><?php
                                       
                                            $i=1;
                                            foreach($stylist_id as $keyOrderHistory => $valueOrderHistory){
                                                ?><tr>
                                                
                                                <td><?php echo ucwords($valueOrderHistory->stylist_name); ?></td>
                                                <td style="">$<?php echo number_format($valueOrderHistory->tip_amount,2); ?></td>
                                                    <td><?php echo $valueOrderHistory->tip_date; ?></td>
                                                </tr><?php
                                                $i++;
                                            }
                                            
                                       
                                        ?>
                                        </tbody>
                                    </table>
                                     
                                </div>
                            </div>
                            <?php   } ?> 

                        </div>
                    </div>
                    <!-- END INVOICE -->

				</div>
                </div>
				
            </div>

        </div>
    </div>
</div>
<!-- modal Box -->



<div class="modal" tabindex="-1" role="dialog"  id="myModal_get_all_customer123" >
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
      
        <h5 class="modal-title"><strong>Your Tip Amount Is </strong>:<span id="blinCss">$<?php echo  number_format($editData->tip_amount ,2);?></span></h5>
        <span id="succ_update" ></span>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <span id="error"></span>
      </div><br>
      <div class="row" style="margin-left:20px;">
    
<span style="font-size:14px;"><strong>Tip Distribute Option</strong></span><br>
        <input type="radio" name="check" onclick="distTipVal('equal')"> Equal
        <input type="radio" name="check" onclick="distTipVal('custom')"> Custom
        <input type="radio" name="check" onclick="distTipVal('percentage')"> Percentage
</div><br>
      <form method="POST" id="amountdist">

       <table id="" class="table table-bordered " cellspacing="0" width="100%">
            <tr>
                <th style="width:15%">S.No1</th>
                <th style="width:35%">Stylist Name</th>
                 <th style="width:15%;display:none;" class="perTh">Percentage</th>
                <th>Tip Amount</th>
            </tr>
            <tbody>
                
                <?php 
                $i=1;
                if(!empty($stylist_id)) {
                $appointmentData =$stylist_id;
                $val23='tipExsist';
                }else{
                $appointmentData =$appointmentData;
                $val23='';
                }
               foreach($appointmentData as $ap){ 
                     if($val23 !=''){
                        $tipValnew=$ap->tip_amount;
                        $editId=$ap->id;
                     }else{
                        $editId='';
                        $tipValnew='';
                     }
                ?>
                <tr>
                    <td><?php echo $i;?></td>
                    <td><?php echo ucwords($ap->stylist_name); ?></td>
                    <td class="perTh" style="display:none;">
                        <input name="editTip[]" type="hidden" value="<?php echo $editId; ?>">
                        <select class="form-control"  onchange="percentageChange(this.value,'<?php echo $i;?>')">
                        <option value="0">Select</option>
                        <option value="25">25 %</option>
                        <option value="50">50 %</option>
                        <option value="75">75 %</option>
                        <option value="100">100 %</option>
                    </select></td>
                    <td><input type="text" name="tipVal[]" class="tipAmount" id="<?php echo $i;?>"  value="<?php echo number_format($tipValnew,2);?>">

                  <input type="hidden" name="appointment_id[]" value="<?php echo $ap->appointment_id;?>" ><input type="hidden" name="stylist_id[]" value="<?php echo $ap->stylist_id;?>" ></td>
                </tr>
            <?php $i++; } ?>
            </tbody>
        </table>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <input type="button" class="btn btn-primary" name="" value="Save Tip" onclick="save_tip();">
      </div>
  </form>
    </div>
  </div>
</div>
<!-- modal -->
<script type="text/javascript">
    function sendInvoice(order_id){
       $.ajax({
                url: baseUrl+"/checkout/mailRecipte/"+order_id,
                type: "POST",
               
                success: function(dataResult){
                   // alert();
                        $('#sendMessageInvoice').html('<span style="color:green;font-size:16px;">Inovice has been sent on customer email.</span>');
                        setTimeout(function() { 
                    $('#sendMessageInvoice').html(''); 
                }, 33000);
                }
            }); 
    }
</script>
<style>
.dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-content {
  display: none;
  position: absolute;
  background-color: #f9f9f9;
  min-width: 160px;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  padding: 12px 16px;
  z-index: 1;
}

.dropdown:hover .dropdown-content {
  display: block;
}
</style>
<script type="text/javascript">
    function distTipVal(val){
        var totalStylist='<?php echo count($appointmentData);?>';
        var tipAmount='<?php echo $editData->tip_amount;?>';

 
/**/
        if(val==0){
            $('.tipAmount').val(' ');
        }
        else if(val=='equal'){
            var tipAmountnew=parseInt(tipAmount)/totalStylist;
            //alert(tipAmountnew);
            $('.tipAmount').val(parseFloat(tipAmountnew).toFixed(2));

        }else if(val=='custom'){
            //alert();
            $('.tipAmount').val(' ');
        }else if(val=='percentage'){
            $('.perTh').show();
        }
        //alert(val+'=='+totalStylist);
    }

    function save_tip(){
        var tipVal=$('.tipAmount').val();
        var tipAmount='<?php echo $editData->tip_amount;?>';
        var totalTipAmount=0;
        $('.tipAmount').each(function(){
            
             totalTipAmount+=+$(this).val();
                        /*if($(this).val()!=""){
                             $('#error').html('Tip Amount value should not be empty')
                     return false;
           }*/
          /* else if(parseFloat(totalTipAmount) > parseFloat(tipAmount)){
    $('#error').html('Stylist Fill Tip Amount Should Not be Greater Than Actual Tip Amount.');
    return false;
}*/
 });
 if(tipVal ==''){
    $('#error').html('<span style="color:red;">Please fill tip amount</span>');
    return false;
 }
else if(parseFloat(totalTipAmount) > parseFloat(tipAmount)){
    $('#error').html('<span style="color:red;">Stylist fill tip amount should not be greater than actual tip amount</span>.');
    return false;
}
else if(parseFloat(totalTipAmount) != parseFloat(tipAmount)){
    $('#error').html('<span style="color:red;">Your tip amount is remaining.</span>.');
    return false;
}
 else {
         $.ajax({
          type: "POST",
          url: baseUrl+"/checkout/stylistTipAmount",
          data: $('#amountdist').serialize(),
          cache:'FALSE',
          beforeSend: function () {
              },
              success: function (html) {
               /// window.location.href=html;

               if(html=='succ'){
                  $('#error').html('<span style="color:green;">Tip distribute successfully</span>');
               }else{
                $('#error').html('<span style="color:red;">Something wrong</span>');
               }
               setTimeout(function() { 
                    $('#error').html(''); 
                    //$('#myModal_get_all_customer123').modal('hide');
                    location.reload();
                }, 3000);
    }

});
        }
}

function percentageChange(val,tdid){
    var tipAmount='<?php echo $editData->tip_amount;?>';
    var amnt=val*tipAmount/100;
     
    $('#'+tdid).val(parseFloat(amnt).toFixed(2));
   // alert(val+" "+tdid);
    //var tipRemain=$('#tipRemain').val();

}
</script>