<div class="col-md-12">
<?php
			foreach($getTemplateData as $getVal){ ?>

				<div class="col-md-4" style="border:1px;padding-top: 10px;">
					
						<img src="<?php echo base_url();?>assets/img/template/<?php echo $getVal->image;?>" class="temImage1" style="width:120px;border:5px solid lightgray;" id="temImage_<?php echo $getVal->id;?>" onclick="imageSelect('<?php echo $getVal->id;?>','<?php echo $type;?>');">
					
				</div>
			<?php } ?>
	</div>
<style type="text/css">
	.temImage1 { cursor: pointer; }
	.active_image{
		border:5px solid green !important;

	}
</style>