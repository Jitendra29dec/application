<div class="page-content-wrap">

    <div class="row">
        <div class="col-md-12">

            <div class="panel panel-default">
                <div class="panel-body">
                    <h2>ORDER <strong>#<?php echo $editData->order_number; ?></strong></h2>
                    
                    <!-- INVOICE -->
                    <div class="invoice">

                        <div class="row">
						<div class="col-md-3">
                                <div class="invoice-address">
                                    <h5><b>Customer Information</b></h5>
                                    
									<p>Name: <?php echo ucwords($customerInfo->firstname.' '.$customerInfo->lastname); ?> </p>
									<p>Email: <?php echo $customerInfo->email; ?> </p>
									<?php if($customerInfo->mobile_phone!=''){ ?><p>Mobile Phone: <?php echo $customerInfo->mobile_phone; ?> </p><?php }?>
									<?php if($customerInfo->home_phone!=''){ ?><p>Home Phone: <?php echo $customerInfo->home_phone; ?> </p><?php }?>
									<?php if($customerInfo->work_phone!=''){ ?><p>Work Phone: <?php echo $customerInfo->work_phone; ?> </p><?php }?>
									
                                   
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="invoice-address">
									<?php 
									$service_total = 0;
									if(!empty($appointmentData)){ ?>
                                    <h5><b>Appointment Information</b></h5>
									<table class="table table-striped">
									<thead>
										<tr>
											<th>Appointment ID</th>
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
                                            <td class="text-right"><?php echo $ap->appointment_id; ?></td>
                                            <td class="text-right"><?php echo date('M d Y h:i a',strtotime($ap->appointment_date)); ?></td>
                                            <td class="text-right"><?php echo ucwords($ap->stylist_name); ?></td>
                                            <td class="text-right"><?php echo ucwords($ap->service_name); ?></td>
                                            <td class="text-right"><?php echo $ap->price; ?></td>
                                            <td class="text-right"><?php echo $ap->duration; ?></td>
                                        </tr>
										<?php 
										$service_total += $ap->price;
										}?>
                                        
                                        </tbody>
                                    </table>
									<?php }?>
                                </div>

                            </div>
                           
                            
                            <div class="<?php if(!empty($appointmentData)){ECHO "col-md-3";}else{echo "col-md-8";}?>">

                                <div class="invoice-address">
                                    <h5><b>Order Information</b></h5>
                                    <table class="table table-striped">
                                        <tbody>
                                        <tr>
                                            <td width="200">Order Number:</td>
                                            <td class="text-right">#<?php echo $editData->order_number; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Order Date:</td>
                                            <td class="text-right"><?php
                                                $originalDate = $editData->created_date;
                                                $newDate = date("d F Y", strtotime($originalDate));
                                                echo $newDate; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Order Type:</td>
                                            <td class="text-right"><?php
                                                if($editData->order_type == "1"){
                                                    $orderType = "Website";
                                                }
                                                else if($editData->order_type == "2"){
                                                    $orderType = "Admin";
                                                }
                                                echo $orderType; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Order Status:</td>
                                            <td class="text-right"><?php
                                                if($editData->status_id == "1"){
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
                                                echo $orderStatus;
                                            ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total:</strong></td>
                                            <td class="text-right"><strong>$ <?php
                                                    if(isset($paymentInfo)){
                                                        echo round($paymentInfo->amount,2);
                                                    }
                                                    else{
                                                        $order_amount = $editData->order_amount;
                                                        $service_tax_amount = $editData->service_tax_amount;
                                                        $swacch_bharat_tax_amount = $editData->swacch_bharat_tax_amount;
                                                        $krishi_kalyan_tax_amount = $editData->krishi_kalyan_tax_amount;
                                                        $discount_amount = $editData->discount_amount;
                                                        $totalPayment = ($order_amount + $service_tax_amount + $swacch_bharat_tax_amount + $krishi_kalyan_tax_amount + $service_total) - $discount_amount;
                                                        echo $totalPayment;
                                                    }
                                                    ?> </strong></td>
                                        </tr>
                                        </tbody>
                                    </table>

                                </div>

                            </div>
                        </div>
						<?php error_reporting(1); if(count(@$productInfo)>0){ ?>
                        <div class="table-invoice">
						
                            <table class="table">
                                <tbody>
								<tr>
                                        <td colspan="4" style="text-align: center"><h4>Product Information</h4></td>
                                    </tr>
                                <tr>
                                    <th>Product Name</th>
                                    <th class="text-center">Unit Price</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Total</th>
                                </tr>
                                <form class="form-horizontal"
                                      action="<?php echo admin_url(""); ?>/orders/savesimnumber" method="post"><?php
									  
                                    $j = 0;
                                    $k = 0;
                                    $topupAmountTotal = 0;
                                    foreach($productInfo as $keyProduct => $valueProduct) {
                                    ?><tr>
                                        <td>
                                            <strong><?php echo $valueProduct->name; ?></strong>
                                        </td>
                                        
                                        <td class="text-center">$ <?php echo $valueProduct->price; ?>
                                            </td>
                                        <td class="text-center"><?php echo $valueProduct->quant; ?></td>
                                        <td class="text-center">$ <?php
                                            $totalProductPrice = ($valueProduct->price * $valueProduct->quant);
                                            echo round($totalProductPrice,2);
                                            ?> </td>
                                    </tr><?php
                                }
                                ?>
                                </form>
                                </tbody>
                            </table>
                        </div>
						
						<?php }?>
                            <div class="col-md-4">
                                <table class="table table-striped">
                                    <tbody>
                                    <tr>
                                        <td colspan="3" style="text-align: center"><h4>Payment History</h4></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Cash Payment</strong></td>
                                        <td><strong>Credit Payment</strong></td>
                                        <td><strong>Iou</strong></td>
                                    </tr>
                                    <tr>
                                        <td>$<?php echo number_format($editData->cash_amount,2);?></td>
                                        <td>$<?php echo number_format($editData->credit_card_amount,2);?></td>
                                        <td>$<?php echo number_format($editData->iou_amount,2);?></td>
                                    </tr>
                            </div>
                        <div class="row">
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
                            <div class="col-md-5">
                                <div class="invoice-address">
                                    <table class="table table-striped">
                                        <tbody>
                                        <tr>
                                            <td colspan="3"><h4>Order History</h4></td>
                                        </tr>
                                        <tr>
                                            <td width="200">Order Number</td>
                                            <td width="200" align="center">Status</td>
                                            <td width="200" align="center">Date</td>
                                        </tr><?php
                                        if(!empty($orderHistoryInfo)) {
                                            foreach($orderHistoryInfo as $keyOrderHistory => $valueOrderHistory){
                                                ?><tr>
                                                <td>#<?php echo $editData->order_number; ?></td>
                                                <td class="text-center"><?php
                                                    if ($valueOrderHistory->status_id == "1") {
                                                        $orderStatus = "Incomplete";
                                                    } else if ($valueOrderHistory->status_id == "2") {
                                                        $orderStatus = "Pending";
                                                    } else if ($valueOrderHistory->status_id == "3") {
                                                        $orderStatus = "Processed";
                                                    } else if ($valueOrderHistory->status_id == "4") {
                                                        $orderStatus = "Partially Shipped";
                                                    } else if ($valueOrderHistory->status_id == "5") {
                                                        $orderStatus = "Shipping";
                                                    } else if ($valueOrderHistory->status_id == "6") {
                                                        $orderStatus = "Shipped";
                                                    } else if ($valueOrderHistory->status_id == "7") {
                                                        $orderStatus = "Partially Returned";
                                                    } else if ($valueOrderHistory->status_id == "8") {
                                                        $orderStatus = "Returned";
                                                    } else if ($valueOrderHistory->status_id == "9") {
                                                        $orderStatus = "Canceled";
                                                    } else if ($valueOrderHistory->status_id == "10") {
                                                        $orderStatus = "Completed";
                                                    }
                                                    echo $orderStatus; ?>
                                                </td>
                                                <td><?php $originalDate = $valueOrderHistory->order_update_date;
                                                    $newDate = date("d F Y", strtotime($originalDate));
                                                    echo $newDate; ?></td>
                                                </tr><?php
                                            }
                                        }
                                        ?>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                            <div class="col-md-3">
                                <table class="table table-striped">
                                    <tbody>
                                    <tr>
                                        <td colspan="2"><h4>Payment Details</h4></td>
                                    </tr>
									<tr>
                                        <td width="200"><strong>Sub Total:</strong></td>
                                        <td class="text-right">$ <?php echo $editData->order_amount; ?> </td>
                                    </tr><?php
                                    if(!empty($editData->service_tax_amount)){
                                    ?><tr>
                                        <td><strong>SERVICE TAX (<?php echo $taxInfo[0]->value; ?> %):</strong></td>
                                        <td class="text-right">$ <?php echo $editData->service_tax_amount; ?> </td>
                                    </tr>
									<?php }?>
                                    <tr>
                                        <td><strong>Discount (<?php
                                                if(!empty($editData->coupon_id)){
                                                    echo $couponInfo->coupon_number;
                                                }
                                                else{
                                                    echo '0%';
                                                } ?>):</strong></td>
                                        <td class="text-right">$ <?php echo $editData->discount_amount; ?> </td>
                                    </tr>
                                 <?php    
                                 if(!empty($editData->tip_amount)){ ?>
                                     <tr>
                                        <td><strong>Tip Amount</strong></td>
                                        <td class="text-right">$ <?php echo number_format($editData->tip_amount,2); ?> </td>
                                    </tr>
                                <?php } ?>
                                <?php    
                                 if(!empty($editData->member_ship_amount)){ ?>
                                     <tr>
                                        <td><strong>Membership Amount</strong></td>
                                        <td class="text-right">$ <?php echo number_format($editData->member_ship_amount,2); ?> </td>
                                    </tr>
                                <?php } ?>
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
                                        <td><strong>Iou Amount</strong></td>
                                        <td class="text-right">$<?php 
                                        /*if($totalProductPrice==0 || $service_price==0){
                                             echo round($paymentInfo->amount,2);
                                        }else{*/
                                          echo number_format($editData->iou_amount,2);    
                                       
                                        /*}
*/
                                        ?> </td>
                                    </tr>
                                    <tr class="total">
                                        <td>Total Amount:</td>
                                        <td class="text-right">$ <?php
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

                                <?php if($send_invoice==1){ ?>
                                    <a href="#" class="btn btn-primary">Click for payment</a>

                                <?php } ?>
                            </div>

                        </div>
                    </div>
                    <!-- END INVOICE -->

                </div>
            </div>

        </div>
    </div>
</div>