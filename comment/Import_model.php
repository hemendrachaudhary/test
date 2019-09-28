<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
 
class Import_model extends CI_Model {

    public function importData($data) {

        $res = $this->db->insert_batch('entry_item',$data);
        if($res){
            return TRUE;
        }else{
            return FALSE;
        }

    }

     public function importData2($data) {

        $res = $this->db->insert_batch('distribute_item',$data);
        if($res){
            return TRUE;
        }else{
            return FALSE;
        }

    }
 public function importData3($data) {

        $res = $this->db->insert_batch('sales',$data);
        if($res){
            return TRUE;
        }else{
            return FALSE;
        }

    }
 
}
 
?>