<div class="page-content-wrap">
    <div class="row">
        <div class="col-md-12" id="print_invoice">
            <div class="invoice-box" style="background-color: white;">
                <table cellpadding="0" cellspacing="0">
                    <tr class="top">
                        <td colspan="3">
                            <table>
                                <tr>
                                    <td class="title">
                                        <img src="<?php echo site_url(); ?>assets/img/logo.jpg" height="100px;">
                                    </td>
                                    <td style="text-align: left">
                                        <h6>Company Information</h6>
                                        <p style="text-align: left;">
                                            Zee Cellz, 5 Penn Plaza, 25th Floor,<br>
                                            New York 10001<br>
                                            Phone : +1 212-461-4211<br>
                                            Email : info@zeecellz.com</p>
                                    </td>
                                    <td style="text-align: right">
                                        <p>Invoice #: <?php echo $editData->order_number; ?><br>
                                        Created: <?php $originalDate = $editData->created_date;
                                        $newDate = date("d F Y", strtotime($originalDate));
                                        echo $newDate; ?></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr class="information">
                        <td colspan="2">
                            <table>
                                <tr>
                                    <td style="text-align: left;padding-left: 135px;">
                                        <div>
                                            <h6>Billing Information</h6>
                                            <p style="text-align: left;">
                                                <?php echo $billingAddressInfo->fname . ' ' . $billingAddressInfo->lname; ?>,<br>
                                                <?php if(!empty($billingAddressInfo->address)){ ?>
                                                <?php echo $billingAddressInfo->address; ?>, <?php echo $billingAddressInfo->address2; ?>,<br>
                                                <?php } ?>
                                                <?php if(!empty($billingAddressInfo->country_name) && !empty($billingAddressInfo->state_name) && !empty($billingAddressInfo->city_name)){ ?>
                                                <?php echo $billingAddressInfo->country_name; ?>, <?php echo $billingAddressInfo->state_name; ?>, <?php echo $billingAddressInfo->city_name; ?>, <?php echo $billingAddressInfo->zipcode; ?>,<br>
                                                <?php } ?>
                                                <?php if(!empty($billingAddressInfo->home_phone)){ ?>
                                                Phone: <?php echo $billingAddressInfo->home_phone; ?><br>
                                                <?php } ?>
                                                Mobile: <?php echo $billingAddressInfo->mobile_phone; ?><br>
                                                Email: <?php echo $customerInfo->email; ?><p>
                                            </p>
                                        </div>
                                    </td>
                                    <td style="text-align: left;">
                                        <div>
                                            <h6>Shipping Information</h6>
                                            <p style="text-align: left;">
                                                <?php echo $shippingAddressInfo->fname . ' ' . $shippingAddressInfo->lname; ?>,<br>
                                                <?php if(!empty($shippingAddressInfo->address)){ ?>
                                                    <?php echo $shippingAddressInfo->address; ?>, <?php echo $shippingAddressInfo->address2; ?>,<br>
                                                <?php } ?>
                                                <?php if(!empty($shippingAddressInfo->country_name) && !empty($shippingAddressInfo->state_name) && !empty($shippingAddressInfo->city_name)){ ?>
                                                    <?php echo $shippingAddressInfo->country_name; ?>, <?php echo $shippingAddressInfo->state_name; ?>, <?php echo $shippingAddressInfo->city_name; ?>, <?php echo $shippingAddressInfo->zipcode; ?>,<br>
                                                <?php } ?>
                                                <?php if(!empty($shippingAddressInfo->home_phone)){ ?>
                                                    Phone: <?php echo $shippingAddressInfo->home_phone; ?><br>
                                                <?php } ?>
                                                Mobile: <?php echo $shippingAddressInfo->mobile_phone; ?><br>
                                                Email: <?php echo $customerInfo->email; ?><p>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr class="heading">
                        <td colspan="2">
                            <div class="table-invoice">
                                <table class="table table-striped" style="margin-bottom: 0px;" cellspacing="0">
                                    <tbody>
                                    <tr>
                                        <th>Product Name</th>
                                        <th style="text-align: center">Unit Price</th>
                                        <th style="text-align: center">Quantity</th>
                                        <th style="text-align: center">Total</th>
                                    </tr><?php
                                    foreach($productInfo as $keyProduct => $valueProduct) {
                                        ?><tr>
                                        <td>
                                            <strong><?php echo $valueProduct->name; ?></strong>
                                        </td>
                                        <td style="text-align: center;"><?php echo $valueProduct->price; ?>
                                            INR</td>
                                        <td style="text-align: center;"><?php echo $valueProduct->quantity; ?></td>
                                        <td style="text-align: center;"><?php
                                            $totalProductPrice = ($valueProduct->price * $valueProduct->quantity);
                                            echo round($totalProductPrice,2);
                                            ?> INR</td>
                                        </tr><?php
                                    }
                                    ?><tr>
                                        <td colspan="2">&nbsp;</td>
                                        <td class="text-right"><strong>Sub Total:</strong></td>
                                        <td style="text-align: center;"><?php echo $editData->order_amount; ?> INR</td>
                                    </tr>
                                    <?php
                                    if(!empty($editData->service_tax_amount)){
                                        ?><tr>
                                        <td colspan="2">&nbsp;</td>
                                        <td><strong>SERVICE TAX (<?php echo $taxInfo[0]->value; ?> %):</strong></td>
                                        <td style="text-align: center;"><?php echo $editData->service_tax_amount; ?> INR</td>
                                        </tr><?php
                                    }
                                    if(!empty($editData->swacch_bharat_tax_amount)) {
                                        ?><tr>
                                        <td colspan="2">&nbsp;</td>
                                        <td><strong>SWACHH BHARAT TAX (<?php echo $taxInfo[1]->value; ?> %):</strong></td>
                                        <td style="text-align: center;"><?php echo $editData->swacch_bharat_tax_amount; ?> INR</td>
                                        </tr><?php
                                    }
                                    if(!empty($editData->krishi_kalyan_tax_amount)) {
                                        ?><tr>
                                        <td colspan="2">&nbsp;</td>
                                        <td><strong>KRISHI KALYAN TAX (<?php echo $taxInfo[2]->value; ?> %):</strong></td>
                                        <td style="text-align: center;"><?php echo $editData->krishi_kalyan_tax_amount; ?> INR</td>
                                        </tr><?php
                                    }
                                    ?>
                                    <tr>
                                        <td colspan="2">&nbsp;</td>
                                        <td><strong>Discount (<?php
                                                if(!empty($editData->coupon_id)){
                                                    echo $couponInfo->coupon_number;
                                                }
                                                else{
                                                    echo '0 %';
                                                } ?>):</strong></td>
                                        <td style="text-align: center;"><?php echo $editData->discount_amount; ?> INR</td>
                                    </tr>
                                    <tr class="total">
                                        <td colspan="2">&nbsp;</td>
                                        <td>Total:</td>
                                        <td style="text-align: center;"><?php
                                            if(isset($paymentInfo)){
                                                echo round($paymentInfo->amount,2);
                                            }
                                            else{
                                                $order_amount = $editData->order_amount;
                                                $service_tax_amount = $editData->service_tax_amount;
                                                $swacch_bharat_tax_amount = $editData->swacch_bharat_tax_amount;
                                                $krishi_kalyan_tax_amount = $editData->krishi_kalyan_tax_amount;
                                                $discount_amount = $editData->discount_amount;
                                                $totalPayment = ($order_amount + $service_tax_amount + $swacch_bharat_tax_amount + $krishi_kalyan_tax_amount) - $discount_amount;
                                                echo round($totalPayment,2);
                                            }
                                            ?> INR</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <tr class="information">
                        <td colspan="2">
                            <table>
                                <tr>
                                    <td style="text-align: left;">
                                        <div>
                                            <h6>DECLARATION</h6>
                                            <p style="text-align: left;">We declare that this invoice shows actual price of the goods and that all particulars are true and correct.<p>
                                            </p>
                                        </div>
                                        <div>
                                            <h6>CUSTOMER ACKNOWLEDGEMENT--</h6>
                                            <p style="text-align: left;">I <?php echo $shippingAddressInfo->fname . ' ' . $shippingAddressInfo->lname; ?> hereby confirm that the above said product/s are being purchased for my internal / personal consumption and not for re-sale.<p>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </div>
</div>
<style>
    .invoice-box{
        max-width:800px;
        margin:auto;
        padding:30px;
        border:1px solid #eee;
        box-shadow:0 0 10px rgba(0, 0, 0, .15);
        font-size:12px;
        line-height:24px;
        font-family:'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color:#555;
    }
    .table-striped > tbody > tr:nth-child(odd) > th {
        background: #F8FAFC;
    }
    .table>tbody>tr>th{
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border-top: 1px solid #ddd;
    }
    .table > tbody > tr > th{
        border-top: 1px solid #E5E5E5;
    }
    .table > tbody > tr > td{
        border-top: 1px solid #E5E5E5;
    }

    .invoice-box table {
        font-size: 12px;
    }
    .invoice-box table td {
        padding: 5px;
        vertical-align: top;
    }

    .invoice-box table{
        width:100%;
        line-height:inherit;
        text-align:left;
    }

    .invoice-box table td{
        padding:5px;
        vertical-align:top;
    }

    .invoice-box table tr td:nth-child(2){
        text-align:right;
    }

    .invoice-box table tr.top table td{
        padding-bottom:20px;
    }

    .invoice-box table tr.top table td.title{
        font-size:45px;
        line-height:45px;
        color:#333;
    }

    .invoice-box table tr.information table td{
        padding-bottom:40px;
    }

    .invoice-box table tr.heading td{
        background:#f5f5f5;
        /*border-bottom:1px solid #ddd;*/
        /*font-weight:bold;*/
    }

    .invoice-box table tr.details td{
        padding-bottom:20px;
    }

    .invoice-box table tr.item td{
        border-bottom:1px solid #eee;
    }

    .invoice-box table tr.item.last td{
        border-bottom:none;
    }

    .invoice-box table tr.total td:nth-child(2){
        border-top:2px solid #eee;
        font-weight:bold;
    }
    h6 {
        margin-bottom: 5px;
        font-size: 13px;
        font-weight: 600;
    }
    .invoice-box{
        font-size: 12px;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color: #555;
    }
    p {
        margin: 0 0 10px;
        font-size: 12px;
    }

    @media only screen and (max-width: 600px) {
        .invoice-box table tr.top table td{
            width:100%;
            display:block;
            text-align:center;
        }

        .invoice-box table tr.information table td{
            width:100%;
            display:block;
            text-align:center;
        }
    }

    @media print
    {
        .page-sidebar,.x-navigation-horizontal,.breadcrumb{
            display: none;
            width: 0;
            margin: 0;
        }
        .invoice-box{
            width: 100%;
        }

        .page-container .page-content{
            margin-left: 0;
        }

    }

</style>