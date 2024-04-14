                    <?php

                        //$num = $param2;
                        $param2 = str_replace('AA', '+', $param2);
                        $param2 = str_replace('BB', '(', $param2);
                        $param2 = str_replace('CC', ')', $param2);                                   
                        $query = $this->db->order_by('received', 'desc')->get_where('sms', array('number' => $param2));
                        $contact_info	=	$query->result_array();
						//var_dump($param2);
					?>

                	<center>
                        
                        
                   	<div style="font-size: 20px;font-weight: 200;margin: 10px;"><?php echo $param2;?></div>
                    
                    <div class="row">
                        <div class="col-md-12">
                        
                            <table  class="table table-bordered table-hover table-striped datatable" id="table_export" style="text-align:center;">
                                <thead>
                                    <tr>
                                        <th><div style="text-align:center;"><?php echo ('Time');?></div></th>
                                        <th><div style="text-align:center;"><?php echo ('Type');?></div></th>
                                        <th><div style="text-align:center;"><?php echo ('Addres');?></div></th>
                                        <th><div style="text-align:center;"><?php echo ('Content');?></div></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($contact_info as $row):?>
                                    <tr>
                                        <td><?php echo $row['received'];?></td>
                                        <td>
                                            <?php 
                                                if($row['fromnumber'] == $param2){
                                            ?>
                                                Sent
                                            <?php }else{ ?>
                                                Receive
                                            <?php }?>
                                        </td>
                                        <td>
                                            <?php 
                                                if($row['fromnumber'] == $param2){
                                                    echo $row['tonumber'];
                                                }else{
                                                    echo $row['fromnumber'];
                                                }
                                            ?>
                                        </td>                                        
                                        <td><?php echo $row['content'];?></td>
                                        
                                    </tr>
                                    <?php endforeach;?>
                                </tbody>
                            </table>
                                
                        </div>
                    </div>
                  