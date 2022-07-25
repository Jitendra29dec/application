			


	<link href="https://booknpay.com/assets/new/dist/css/bootstrap.min.css" rel="stylesheet">
<?php 
$dayNameNew=array();
/*function minutes($time){
$time = explode(':', $time);
return ($time[0]*60) + ($time[1]);
}*/
//echo "<pre>";print_r($newData);exit;
if(!empty($newData)){

?>

<div class="col-md-12" style="overflow-x: auto;overflow-y: hidden;scrollbar-width: thin;">

<table id="table1" class="table table-striped table-bordered table_service" cellspacing="0" width="100%" style="white-space: nowrap;">
                    <thead>
                    <tr>
				    <th class="preview_th" colspan="2">Employe Name</th>
                        <?php foreach($getDay as $key11=>$val){
                        		$dayNameNew[]=$key11;
                        	?>
                        <th class="preview_th" colspan="3" style="text-align:center;"><?php echo $key11;?><br/> <?php echo $val;?></th>
                        <!--<th>Hour</th>-->
                    	<?php } ?>
                    	<th class="preview_th">Total Hour</th>
                    </tr>
                    </thead>
				<tbody id="getData">
				<?php
                   $days=[];
                   $days1=[];
                   $totalHour=[];
				 foreach($newData as $key=>$val){
					foreach ($val as $k => $value) {
						$days[$value->dayname]=$value->end;
						//$days1[$value->dayname]=$value->totalHour;
						$split_time=explode(":",$value->totalHour);
						//echo "<pre>";print_r($split_time);
						if($split_time[0]==00){
							$hour='';
						}else{
							$hour=$split_time[0]."";
						}
						if($split_time[1]==00){
							$mint='';
						}else{
							$mint=$split_time[1]."";
						}
						$minutes1[$key]+= ($split_time[0]*60) + ($split_time[1]);
						$days1[$value->dayname]=$hour.".".$mint;
						//$start = strtotime($split_time[0].":".$split_time[1]);
						//$end = strtotime('13:16:00');

						
						$minutes=$minutes1[$key];
							//echo $mins;
						//echo $minutes;
						$hours = floor($minutes / 60);
						$min = $minutes - ($hours * 60);
						if($hours==0){
							$hour11='';
						}else{
							$hour11=$hours."&nbsp;Hr";
						}
						if($min==0){
							$min11='';
						}else{
							$min11=$min."&nbsp;min";
						}
						
						//echo $hours.":".$min;
						$totalHour[$key]=$hour11."&nbsp;".$min11;
						}

					//echo "<pre>";print_r($days);exit;

				 ?>
				 
				 <tr style="font-weight:600;font-size:14px;">
					<td>Name</td>
					<td>Job</td>
					<td>Time In</td>
					<td>Time Out</td>
					<td>Hrs</td>
					
					<td>Time In</td>
					<td>Time Out</td>
					<td>Hrs</td>
					
					<td>Time In</td>
					<td>Time Out</td>
					<td>Hrs</td>
					
					<td>Time In</td>
					<td>Time Out</td>
					<td>Hrs</td>
					
					<td>Time In</td>
					<td>Time Out</td>
					<td>Hrs</td>
					
					<td>Time In</td>
					<td>Time Out</td>
					<td>Hrs</td>
					
					<td>Time In</td>
					<td>Time Out</td>
					<td>Hrs</td>
					
					<td>Hours</td>
					
					
				</tr>
				
				
				<tr>
					<td>
					<?php echo $key;?>	
					</td>
					<td>Stylist</td>
					 <?php 
					 	
					foreach($dayNameNew as  $dayVal){
						$abc =  ($days[$dayVal]) ? $days[$dayVal] : '-';
						$in_out = explode('-',$abc);
					 	?>

					<td>
						<?php echo $in_out[0];?>
					</td>
					<td>
						<?php echo $in_out[1];?>
					</td>
					<td>
					
					<?php
						echo ($days1[$dayVal]) ? $days1[$dayVal]."&nbsp;" : '';


					?>
					</td>
					<!--<td>
					<?php 
						
					//echo ($days1[$dayVal]) ? $days1[$dayVal]."&nbsp;" : '0';?>
				</td>-->
						
				
				<?php 
					//$totalHour+=$days1[$dayVal];
			} ?> 
				<td><?php 

				echo $totalHour[$key]."&nbsp;";?></td>
					</tr>

				<?php unset($days);
					 unset($days1);
			} ?>				
							

				
				</tbody>
</table>
</div>
<?php } else { ?>
	<p>No schedule available</p>
	<?php }  ?>







				
