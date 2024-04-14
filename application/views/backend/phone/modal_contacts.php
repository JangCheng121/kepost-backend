                    <?php
                        $param2 = str_replace('AA', '+', $param2);
                        $param2 = str_replace('BB', '(', $param2);
                        $param2 = str_replace('CC', ')', $param2);

                        $query = $this->db->get_where('contacts', array('number' => $param2));
                        $contact_info	=	$query->result_array();
						
					?>

                	<center>
                        
                   	<div style="font-size: 20px;font-weight: 200;margin: 10px;"><?php echo $param2;?></div>
                    
                    <div class="row">
                        <div class="col-md-12">
                        
                            <table  class="table table-bordered table-hover table-striped datatable" id="table_export"  style="text-align:center;">
                                <thead>
                                    <tr>
                                        <th><div style="text-align:center;"><?php echo ('Name');?></div></th>
                                        <th><div style="text-align:center;"><?php echo ('Number');?></div></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($contact_info as $row):?>
                                    <tr>
                                        <td><?php echo $row['name'];?></td>
                                        <td><?php echo $row['contact_number'];?></td>
                                        
                                    </tr>
                                    <?php endforeach;?>
                                </tbody>
                            </table>
                                
                        </div>
                    </div>
                  