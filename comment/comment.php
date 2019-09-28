<!-- CREATE TABLE `comment` (
  `comment_id` int(11) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `parrent_cooment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 -->

<?php

$con=mysqli_connect('localhost','root','','test');
$result = mysqli_query($con,"SELECT * FROM comment");
$arrs = array();

while ($row = mysqli_fetch_assoc($result)) {
    $arrs[] = $row;
}

function build_tree($arrs, $parent_id=0, $level=0) {
    foreach ($arrs as $arr) {
        if ($arr['parrent_cooment_id'] == $parent_id) {
            echo str_repeat("-----", $level)." ".$arr['comment']."<br />";
            build_tree($arrs, $arr['comment_id'], $level+1);
        }
    }
}

build_tree($arrs);
?>





$this->load->library('upload');

            $config['upload_path'] = './assets/images/attendance/'; 
            $config['allowed_types'] = 'gif|jpg|jpeg|png';
            $config['max_size'] = 10000; 
             $config['file_name'] = time();
            $this->upload->initialize($config);
            $atn_img="";
            if ( $this->upload->do_upload('atn_img'))
            {
            $atn_img=base_url().'assets/images/attendance/'.$this->upload->data('file_name'); 
            }
            else
            {
        $atn_img='';
            }
