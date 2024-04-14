<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<div class="col-sm-5">
		      	<<div style="float:left; margin-top:3px;margin-right:5px">
		      		<strong>
		      		<span>All : <?php echo($all_phones); ?></span>
		      		<span style="margin-left:20px">Disabled : <?php echo($disabled_phones); ?></span>
		      		<span style="margin-left:20px">Disabled Before 3days : <?php echo($threedays_phones); ?></span>
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
					<?php echo ('Upload Apk File');?>
                    	</a></li>			
		</ul>
    	<!------CONTROL TABS END------>
		<div class="tab-content">            
			<!----CREATION FORM STARTS---->
			<div class="tab-pane box  active" id="upload" style="padding: 5px">
                <div class="box-content">
                	<?php echo form_open_multipart(base_url() . 'index.php?view/upload/create' , array('class' => 'form-horizontal form-groups-bordered validate','target'=>'_top'));?>
                        <div class="padded">
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?php echo ('Version');?></label>
                                <div class="col-sm-5">
                                    <input type="text" name="version" class="form-control" id="version" value="2.0" >
                                </div>
                            </div>
                            <div class="form-group">
		                        <label class="col-sm-3 control-label"><?php echo ('File'); ?></label>

		                        <div class="col-sm-5">

		                            <input type="file" name="file_name" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-file'></i> Browse" />

		                        </div>
		                    </div>		                    
                        </div>

                        <div class="form-group">
                              <div class="col-sm-offset-3 col-sm-5">
                                  <button type="submit" class="btn btn-info"><?php echo ('Upload');?></button>
                              </div>
						   </div>
                    
                    <?php echo form_close();?>             
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
		$('#fromnumber').attr('disabled','disabled');

		var datatable = $("#table_export").dataTable();
		
		$(".dataTables_wrapper select").select2({
			minimumResultsForSearch: -1
		});
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
