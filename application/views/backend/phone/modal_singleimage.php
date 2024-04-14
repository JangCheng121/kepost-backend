<?php 
	$param2 = str_replace('AA', '+', $param2);
    $param2 = str_replace('BB', '(', $param2);
    $param2 = str_replace('CC', ')', $param2);
    $sql = "select unumber from phone where number = '".$param2."'";
    $number = $this->db->query($sql)->row()->unumber;

	$edit_data = $this->db->get_where('images' , array('number'=>$number,'filename' => $param3))->result_array();
	foreach ($edit_data as $row):
?>

<div class="row">
<img src="<?= base_url('/uploads/'.$number.'/images/' . $row['filename']) ?>" alt="avatar" style="width: 100%; height: 100%; ">
</div>
<?php endforeach;?>