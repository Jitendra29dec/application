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
  
           
 <div class="box box-primary">
			
                <div class="box-header with-border">
                  
				  <span style="margin-left:100px;" id="msg"></span>
				  
				  
                </div><!-- /.box-header -->
                <!-- form start -->
                <form role="form"  enctype="multipart/form-data"
                  action="<?php echo "https://booknpay.com/api_new/settings/editMessageWebView/$id"?>"
                  method="post" id="editSettings">
				  
                  <div class="box-body">
				  <div class="col-md-12">
				  
				   <div class="form-group">
                      <label for="exampleInputEmail1">Customer List</label>
                      <select class="form-control" name="email_customer" onchange="getCustMail(this.value);">
                                            <option value="0">-Select Customer-</option>
                                           <?php foreach($customer_list as $list){?>
												<option value="<?=$list->customer_id?>"><?=$list->customer_name?></option>
										   <?php }?>
                                        </select> 
                    </div>
					
                    <div class="form-group">
                      <label for="exampleInputEmail1">Email Subject</label>
                      <input type="text" class="form-control"
                                               value="<?=$content->email_subject?>"
                                               name="email_subject" id="email_subject"
                                               placeholder="Email Subject">
                    </div>
					
                    <div class="form-group">
                      <label for="exampleInputPassword1">Email Text <input type="checkbox" checked> </label>
                      <textarea class="form-control" rows="11" name="description"
                                                  id="editor1"><?=$content->email_content?></textarea>
                    </div>
					
					<div class="form-group">
                      <label for="exampleInputPassword1">SMS Text 
						<!-- Default switch -->
						<input type="checkbox" checked> 
					  </label>
                      <textarea class="form-control" rows="11" name="sms_description"
                                                  id="sms_description"><?=$content->sms_content?></textarea>
                    </div>
					
					
					
                   
                    </div>
				
                  </div><!-- /.box-body -->

                  <div class="box-footer">
                   <button type="button" class="btn btn-primary" id="sndButton" onclick="sendMail();" disabled="true">SEND MAIL</button>
				    <button type="submit" class="btn btn-primary" id="sbButton">SAVE</button>
					<span id="sendMsg"></span>
                  </div>
                </form>
              </div>
  
</div>

<style>
.box-footer {
    position: fixed;
    height: 50px;
    bottom: 0;
    width: 100%;
	background: #fff;
    border-top: 1px solid #ccc;
    padding-top: 8px;
}

</style>
<script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>

<script type="text/javascript">

 CKEDITOR.replace( 'editor1' );
    function sendMail() {
        var data=$('#editSettings').serialize();
        $.ajax({
                url: baseUrl+'/settings/sendEmailSetting/',
                type: "POST",
                data: data,
                cache: false,
               // dataType: 'json',
                success: function(dataResult){
                    $('#sendMsg').html('<span style="font-size:14px;color:green;">'+dataResult+'</span>');
                }
            });
        // body...
    }
    function getCustMail(val){
       if(val !=0){
             $("#sbButton").prop('disabled', true);
              $("#sndButton").prop('disabled', false);
       }else{
            $("#sbButton").prop('disabled', false);
              $("#sndButton").prop('disabled', true);
       }
    }
</script>

</body>
</html>