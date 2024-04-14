                    <?php
                        $param2 = str_replace('AA', '+', $param2);
                        $param2 = str_replace('BB', '(', $param2);
                        $param2 = str_replace('CC', ')', $param2);

                        $sql = "select unumber from phone where number = '".$param2."'";
        				$number = $this->db->query($sql)->row()->unumber;

                        $sql = "select * from images where number='".$number."'";
                        $query = $this->db->query($sql);            
                        $result = $query->result_array();   
                        //$query = $this->db->get_where('contacts', array('number' => $param2));
                        $imagelist	=	$result;
						
					?>

                	
                                            
                    <div class="row">
                    <center>
                        <div style="font-size: 20px;font-weight: 200;margin: 10px;"><?php echo $param2;?></div>
                    </center>
						<div id="image-gallery">
							<?php $count = 0; ?>
							<?php foreach ($imagelist as $key => $row): ?>
								<?php if ($count % 5 === 0): ?>
									<div class="image-row">
								<?php endif; ?>

								<div class="image-container">  
	                            <a onclick="showAjaxModal('<?php echo base_url(); ?>index.php?modal/popup/modal_singleimage/<?php 
	                            	$num = $param2;
								$num = str_replace('+', 'AA', $num);
								$num = str_replace('(', 'BB', $num);
								$num = str_replace(')', 'CC', $num);
							    echo $num.'/';
							    echo $row['filename'];?>');" class="">
									<img src="<?php echo base_url('uploads/'.$row['number'].'/images/' . $row['filename']); ?>" alt="" style="width: 100px;height: 100px;" class="img-thumbnail" >
								</a>
								</div>

								<?php $count++; ?>

								<?php if ($count % 5 === 0 || $key + 1 === count($imagelist)): ?>
									</div> <!-- Close the row after every 6 images or at the end of the loop -->
								<?php endif; ?>
							<?php endforeach; ?>

						</div>
										
								
								
				
					</div>
<style type="text/css">
  
#image-gallery {
    width: 100%;
    margin: 20px 0;
}

.image-row {
    display: flex;
    justify-content: start;
    margin-bottom: 10px;
}

.image-container {
    flex: 0 0 calc(16.666% - 10px); /* 6 images per row with 10px margin between them */
    box-sizing: border-box;
    margin-left: 10px;
}

.image-container img {
    width: auto;
    height: auto;
    display: block;
    border: 1px solid #ddd;
    padding: 5px;
}

/* Clearfix to fix container overflow issues */
.clearfix::after {
    content: "";
    clear: both;
    display: table;
}
</style>
                  