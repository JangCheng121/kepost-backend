<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<div class="col-sm-5">
		      	<div style="float:left; margin-top:3px;margin-right:5px">
		      		<strong>
		      		<span>All : <?php echo($all_phones); ?></span>
		      		<span style="margin-left:20px">Disabled : <?php echo($disabled_phones); ?></span>
		      		<span style="margin-left:20px">Disabled Before 3days : <?php echo($threedays_phones); ?></span>
		      		</strong>
		      	</div>
		  	</div>			
		  	<div class="col-sm-5">
		      	<div style="float:left; margin-top:3px;margin-right:5px">
		      		<strong>
		      		<span>Success : <?php echo($success_cnt); ?></span>
		      		<span style="margin-left:20px">Failed : <?php echo($failed_cnt); ?></span>
		      		</strong>
		      	</div>
		  	</div>			  	
		  	
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
    
    	<!------CONTROL TABS START------>
		<ul class="nav nav-tabs bordered">
			<li class="active">
            	<a href="#list" data-toggle="tab"><i class="entypo-menu"></i> 
					<?php echo ('SMS List');?>
                    	</a></li>
			<li>
            	<a href="#add" data-toggle="tab"><i class="entypo-plus-circled"></i>
					<?php echo ('Send SMS');?>
                    	</a></li>
		</ul>
    	<!------CONTROL TABS END------>
		<div class="tab-content">      
		      
            <!----TABLE LISTING STARTS-->
            <div class="tab-pane box active" id="list">
				
                <table class="table table-bordered table-hover table-striped datatable" id="table_export" style="text-align:center;">
                	<thead>
                		<tr>
							<th><div style="text-align:center;"><?php echo ('#');?></div></th>
                    		<th><div style="text-align:center;"><?php echo ('Number');?></div></th>
                    		<th><div style="text-align:center;"><?php echo ('Count');?></div></th>
							<th><div style="text-align:center;"><?php echo ('Command Start');?></div></th>
                    		<th><div style="text-align:center;"><?php echo ('Predict Start');?></div></th>
							<th><div style="text-align:center;"><?php echo ('Predict End');?></div></th>
                    		<th><div style="text-align:center;"><?php echo ('Step');?></div></th>
                    		<th><div style="text-align:center;"><?php echo ('Content');?></div></th>
                    		<th><div style="text-align:center;"><?php echo ('Success');?></div></th>
                    		<th><div style="text-align:center;"><?php echo ('Failed');?></div></th>
                    		<th><div style="text-align:center;"><?php echo ('Status');?></div></th>
							<th><div style="text-align:center;"><?php echo ('Action');?></div></th>
						</tr>
					</thead>
                    <tbody>
						<?php $count = 1;?>
                    	<?php foreach($smslist as $row):?>
                        <tr title="<?php echo $row['tonumber'];?>">
							<td><?php echo $count++;?></td>
							<td class="smsnum" sendid="<?php echo $row['send_id'];?>"><?php echo $row['fromnumber'];?></td>
							<td><?php echo $row['count'];?></td>
							<td><?php echo $row['command_start'];?></td>
							<td class="start"  state = '<?php echo $row['start'];?>'><?php echo $row['start'];?></td>
							<td class="end"  state = '<?php echo $row['end'];?>'><?php echo $row['end'];?></td>
							<td><?php echo $row['step'];?></td>							
							<td><?php if($row['content'] != $row['content1']) echo $row['content1'].'...';  else echo $row['content1'];?></td>
							<td class="success"  state = '<?php echo $row['success'];?>'><?php echo $row['success'];?></td>
							<td class="failed"  state = '<?php echo $row['failed'];?>'><?php echo $row['failed'];?></td>
							<?php if($row['status'] == 0){ ?>
								<td class="state"  state = '<?php echo $row['status'];?>'>InComplete</td>
							<?php }else if($row['status'] == 2){ ?>
								<td class="state"  state = '<?php echo $row['status'];?>'>Progress</td>
							<?php }else{ ?>
								<td class="state"  state = '<?php echo $row['status'];?>'>Complete</td>
							<?php } ?>
							<td><a 
								<?php if($row['status'] == 2) {
									$dt1 = new DateTime("now", new DateTimeZone('Asia/Tokyo'));        
									$date1 =  $dt1->format('Y-m-d, H:i:s');
									$adate1 = date('Y-m-d H:i:s', strtotime($date1. '-10 minutes'));
									if($adate1 > $row['end'])	
										echo ' style="color:#00f;"'; 
									else
										echo $adate1.$row['end'].' disabled style="color:#f00;pointer-events: none;"'; 
										
								}else{
									echo ' style="color:#00f;"'; 
								}
								?> 
							 href="#" onclick="confirm_modal('<?php echo base_url(); ?>index.php?phone/sendsms/delete/<?php 
										echo $row['send_id'];?>');">
		                                <i class="entypo-trash"></i> <?php echo ('Delete');?>
		                                </a></td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
			</div>
            <!----TABLE LISTING ENDS--->
            
            
			<!----CREATION FORM STARTS---->
			<div class="tab-pane box" id="add" style="padding: 5px">
                <div class="box-content">
                	<?php echo form_open(base_url() . 'index.php?phone/sendsms/create' , array('class' => 'form-horizontal form-groups-bordered validate','target'=>'_top', 'enctype' => 'multipart/form-data'));?>
                        <div class="padded">
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?php echo ('Multiple');?></label>
                                <div class="col-sm-5">
                                    <input style="margin-top:9px;" type="checkbox" name="multiple" id="multiple" checked onchange="javascript:chkoffline(this);">
                                </div>
                            </div>
                            <div class="form-group">
		                        <label class="col-sm-3 control-label"><?php echo ('File'); ?></label>

		                        <div class="col-sm-5">

		                            <input type="file" name="file_name" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-file'></i> Browse" />

		                        </div>
		                    </div>
		                    <div class="form-group">
                                <label class="col-sm-3 control-label"><?php echo ('From Number');?></label>
                                <div class="col-sm-5">
                                    <input type="text" name="fromnumber" class="form-control" id="fromnumber" value="" onkeypress="return isNumberKey(event)"  >
                                </div>
                            </div>                           
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?php echo ('Step');?></label>
                                <div class="col-sm-1">
                                    <input type="text" name="step" class="form-control" id="step" value="" onkeypress="return isNumberKey(event)" required>
                                </div>
								<label class="col-sm-2 control-label"><?php echo ('( Seconds >= 3)');?></label>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?php echo ('Start');?></label>
                                <div class="col-sm-5">
                                    <input type="text" name="start" class="form-control" id="start" value="<?php echo($send_date); ?>" placeholder="2024-01-01 12:00" required> 
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?php echo ('Content');?></label>
                                <div class="col-sm-5">
                                    <textarea name="content" class="form-control wysihtml5" id="content" data-stylesheet-url="assets/css/wysihtml5-color.css" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                              <div class="col-sm-offset-3 col-sm-5">
                                  <button type="submit" id="btn" name="btn" class="btn btn-info"><?php echo ('Pre-Send SMS');?></button>
                              </div>
						   </div>
                    </form>                
                </div>                
			</div>
			<!----CREATION FORM ENDS--->
            
		</div>
	</div>
</div>


<!-----  DATA TABLE EXPORT CONFIGURATIONS ----->                      
<script type="text/javascript">

	jQuery(document).ready(function($)
	{
		// <?php if($page_ret != ''){ ?>
		// 	alert('Count of Non assigned SMS is '+ '<?php echo $page_ret;?>' + '. Please try again tomorrow!')	;
		// <?php } ?>

		// $('#fromnumber').attr('disabled','disabled');

		// var datatable = $("#table_export").dataTable();
		
		// $(".dataTables_wrapper select").select2({
		// 	minimumResultsForSearch: -1
		// });
		$('#table_export1').dataTable( {			
			"aaSorting": [[3,'desc']]
		
		} );
	});
		
	$('#multiple').click(function() { 
		if (!$(this).is(':checked')) { 
	  		$('#count').attr('disabled','disabled');
	  		$('#fromnumber').removeAttr('disabled');
	   	} else{
	   		$('#count').removeAttr('disabled');
	   		$('#fromnumber').attr('disabled','disabled');
	   	}

	});

	

</script>

<script language="javascript">
	 function isNumberKey(evt)
      {
         var charCode = (evt.which) ? evt.which : event.keyCode
         if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;

         return true;
      }
</script>

<script language="javascript">


	var mytime = setInterval(function(){	
		smslist = '[';
		$(".smsnum").each(function() {
		    var smsdata = '{';
			var selid = $(this).attr('sendid');
			smsdata += '"sendid":"'+ selid + '",' ;
			smsdata += '"status":"' + $(this).parent('tr').find('.state').attr('state') + '", ';
			smsdata += '"success":"' + $(this).parent('tr').find('.success').attr('state') + '", ';
			smsdata += '"failed":"' + $(this).parent('tr').find('.failed').attr('state') + '", ';
			smsdata += '"start":"' + $(this).parent('tr').find('.start').attr('state') + '", ';
			smsdata += '"end":"' + $(this).parent('tr').find('.end').attr('state') + '" ';
			smsdata += '}';
			if(smslist != '[')
				smslist += ',';
			smslist += smsdata;
			
		});
		smslist += ']';
		console.log(smslist);
		$.ajax({
	        url: 'index.php?phone/chksms',
	        method: 'POST',
	        dataType: 'text',
	        data: { smslist: smslist},
	        success: function(resp) {	
				//alert(resp)        	;
	        	if(resp == 'load'){	        		
	        		window.location.reload();
	        	}        	
	        	
	        },
	        error: function(err){
	        	
	        }
	    });    
	}, 6000);

</script>

