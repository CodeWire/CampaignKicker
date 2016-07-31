<?php global $wpdb;
$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."timetagger_infusionsettings");
$results =$results[0];



$crons = $wpdb->get_results("SELECT time,schedule,tag_id FROM ".$wpdb->prefix."timetagger_tagsto_apply");
$cron_array = array();

foreach($crons as $cron){
 if($cron->time != 0){ $cron_array[] = $cron; } else { $default_tag  = $cron->tag_id;}
} 
sort($cron_array);
 ?>
<div class="warp" id="timetagger_setup" style="clear:both;">
  <h2>Time Tagger Settings</h2>
  <br />
  <div id="clscron_settings">
    <form method="post" action="" id="timetagger_setupform">
      <h3>
        <?php _e('Infusion Soft Settings', 'timetrigger')?>
      </h3>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row" valign="top" nowrap="nowrap"> <?php _e('InfusionSoft Status', 'timetrigger'); ?>
            </th>
            <td> Yes
              <input type="radio" name="is_status" <?php echo ($results->status == 1) ? 'checked="checked"':'';?> value="1" />
              No
              <input type="radio" name="is_status" value="0" <?php echo ($results->status == 0) ? 'checked="checked"':'';?>  />
            </td>
          </tr>
          <tr valign="top">
            <th scope="row" nowrap="nowrap"><label for="paypal_email">
              <?php _e('Application Name', 'timetrigger');?>
              </label></th>
            <td><input type="text" name="is_applicationname"  value="<?php echo stripslashes($results->app_name);?>" />
              <br>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><label for="paypal_email">
              <?php _e('API Key', 'timetrigger');?>
              </label></th>
            <td><input type="text" name="is_api_key"  value="<?php echo stripslashes($results->app_key);?>" />
              <br>
			  <input type="hidden" name="id" value="<?php echo stripslashes($results->id);?>"  />
            </td>
          </tr>
        </tbody>
      </table>
	  <br />
	    <h3>
        <?php _e('User Register Tag', 'timetrigger')?>
       </h3>
	    <table class="form-table">
			<tbody>
			  <tr valign="top">
				<th scope="row" valign="top" nowrap="nowrap"> <?php _e('Default User Tag', 'timetrigger'); ?>
				</th>
				<td>
				<?php
			 
				if(IS_APPLICATION_NAME1 != '' && IS_API_KEY1 != '')
				{
				require_once("is/isdk.php");
				$myApp = new iSDK;
				if ($myApp->cfgCon("connectionName")) 
					{
						$returnFields = array('Id','GroupName','GroupCategoryId');
						$query = array('Id' => '%');
						$page = 0;
						$tags = $myApp->dsQuery("ContactGroup",1000,$page,$query,$returnFields);
						$all_records[] = $tags;
						if(isset($all_records[0]) && is_array($all_records[0]))
						{
						
							foreach($all_records[0] as $key => $value)
							{
							 
								$allTagsId[] = $value['Id'];
							}
						}
						if(!empty($all_records))
						{
							foreach($all_records as $k => $v)
							{
				
								if(!empty($v)  && is_array($v))
								{
				
									foreach($v as $p => $d)
									{
										if(isset($d['GroupName']))
										{
											$d['GroupName'] = ucfirst($d['GroupName']);
											$newTags[] = $d;
											
										}
									}
								}
							}
						}
					}
					echo '<select name="default_tag">';
					foreach($newTags as $tag){
						if($default_tag == $tag['Id']){
						echo '<option selected="selected" value='.$tag['Id'].'>'.$tag['GroupName'].'</option>';
						}else{
						echo '<option value='.$tag['Id'].'>'.$tag['GroupName'].'</option>';
						}
					}
					echo '</select>';
				}
				else
				{
				 echo '<div class="updated" id="message"><p>Please Enter Infusionsoft Details.</p></div>';
				}
				?>				
				</td>
			  </tr>
			</tbody>
		</table>
	   
	   <br />
	  <h3>
        <?php _e('Automated Triggers', 'timetrigger')?>
      </h3>
	  	<?php if(IS_APPLICATION_NAME1 != '' && IS_API_KEY1 != ''){ ?>
		 <table class="form-table clscronjob">				
			<?php if(count($cron_array) > 0){
				for($j=0; $j<count($cron_array); $j++) {	 ?>
				 <tr valign="top">
					<td scope="row" valign="top" nowrap="nowrap"> 
					<select class="time" name="cron[time][]">
					<?php for($i=1; $i<=59; $i++) { $selected = '';
					if($cron_array[$j]->time == $i){$selected ='selected="selected"';} ?>
						<option <?php echo $selected; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
					<?php } ?>
					</select>
					</td>
					<td scope="row" valign="top" nowrap="nowrap"> 
					<?php if($cron_array[$j]->schedule == $i){echo 'selected="selected"';}  ?>
					<select name="cron[schedule][]" class="changetime" >
						<option <?php if($cron_array[$j]->schedule=='minutes'){echo 'selected';}?> value="minutes">Minutes</option>
						<option <?php if($cron_array[$j]->schedule== 'hours'){echo 'selected';}  ?> value="hours">Hours</option>
						<option <?php if($cron_array[$j]->schedule == 'days'){echo 'selected';}  ?> value="days">Days</option>
						<option <?php if($cron_array[$j]->schedule == 'months'){echo 'selected';}  ?> value="months">Months</option>
					</select>
					</td>
					<td> 
						<?php
						echo '<select name="cron[tag][]">';
						foreach($newTags as $tag){
						$selected = '';
						if($cron_array[$j]->tag_id == $tag['Id']){$selected ='selected="selected"';}
							echo '<option '.$selected.' value='.$tag['Id'].'>'.$tag['GroupName'].'</option>';
						}
						echo '</select>';
						?>
					</td>
					<td> <a href="javascript:void(0);" <?php if($j==0){echo 'class="clsadd"';} else {echo 'class="clsremove"'; }?>&nbsp;</a></td>
				</tr>
			<?php }} else { ?>
				 <tr valign="top">
					<td scope="row" valign="top" nowrap="nowrap"> 
					<select class="time" name="cron[time][]">
					<?php for($i=1; $i<=59; $i++) { $selected = '';
					// if($cron_array[$j]->days == $i){$selected ='selected="selected"';} ?>
						<option <?php echo $selected; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
					<?php } ?>
					</select>
					</td>
					<td scope="row" valign="top" nowrap="nowrap"> 
					<select name="cron[schedule][]" class="changetime" >
						<option selected="selected" value="minutes">Minutes</option>
						<option value="hours">Hours</option>
						<option value="days">Days</option>
						<option value="months">Months</option>
					</select>
					</td>
					<td> 
						<?php
						echo '<select name="cron[tag][]">';
						foreach($newTags as $tag){
						$selected = '';
						//if($cron_array[$j]->tag_id == $tag['Id']){$selected ='selected="selected"';}
							echo '<option '.$selected.' value='.$tag['Id'].'>'.$tag['GroupName'].'</option>';
						}
						echo '</select>';
						?>
					</td>
					<td><a href="javascript:void(0);" class="clsadd">&nbsp;</a></td>
				</tr>
			<?php } ?>
				
		 </table>
	  	<?php } else{ echo '<div class="updated" id="message"><p>Please Enter Infusionsoft Details.</p></div>'; }?>
	  
      <p class="submit">
        <input  onclick="timetagger_savesetup();" name="button" value="<?php _e('Save Changes','timetagger_savesetup');?>" type="button" />
    
      </p>
    </form>
  </div>
</div>

<script type="text/javascript">
jQuery.noConflict(); 

 jQuery('.clsadd').click(function(){
 	var append = jQuery(this).parent().parent().html();
	append = append.replace('clsadd', 'clsremove');
	jQuery(this).parent().parent().parent().append('<tr>'+append+'</tr>');
	
 });
 
  jQuery(document.body).on('click', '.clsremove' ,function(){
 var append = jQuery(this).parent().parent().remove();
	
 });
  
 /*jQuery(".changetime").bind('change', function(event, ui)
	{*/
	 jQuery(document.body).on('change', '.changetime' ,function(){
 	var value = this.value; 
	var cnt=0; 
	if(value=='minutes'){cnt=59;}else if(value=='hours'){cnt=23;}else if(value=='days'){cnt=29;}else if(value=='months'){cnt=12;}
	var option = '';
	for(var k=1; k<=cnt; k++){
		option += '<option value="'+k+'">'+k+'</option>';
	}
	jQuery(this).parent().parent().before().children().children('.time').html(option);
 });
 function timetagger_savesetup()
		{
			  var data = jQuery("#timetagger_setupform").serialize();
			jQuery.ajax({
				type: 'post',
				url: ajaxurl,
				dataType: 'json',
				data: data + '&action=timetagger_save',
				success: function(response){
					
					alert('Updated Successfully');	
				}
			}); 

			 
	 
		}
</script>
