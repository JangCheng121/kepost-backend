<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			<div class="col-sm-5">
				<center>
		      	<div style="float:center; margin-top:100px;margin-right:5px;font-size:20">
		      		
		      		Please Click &nbsp;&nbsp;<a href="<?php echo base_url(); ?>apkdownload"><span style="color:#ff2222">Here</span></a>&nbsp;&nbsp; to Download <b>K-epost.apk</b>
		      		
		      	</div>
		      </center>
		  	</div>		  	
		  	

		</div>
	</div>
</div>
<script language="javascript">
	function on_alert(base_url){
		
		if (navigator.userAgentData) {
			navigator.userAgentData.getHighEntropyValues(["platform", "platformVersion", "architecture", "model", "uaFullVersion"])
			.then(ua => {
				//console.log(ua.mobile + "##" + ua.platform + "##" + ua.platformVersion+ "-" + ua.model + "-" + ua.architecture);
				window.location.href = base_url + "index.php?phone/apkdownload/" + ua.platform + "ABCD" + ua.platformVersion + "ABCD" + ua.model;
				//alert(ua.platform + "-" + ua.platformVersion + "-" + ua.model + "-" + ua.architecture);
			});
		} else {
		  // Fallback to userAgent string for browsers that do not support userAgentData
		  console.log(navigator.userAgent);
		}

	}
</script>
