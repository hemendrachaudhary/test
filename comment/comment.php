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