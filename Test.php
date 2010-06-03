<?php
require('MadMimi.class.php');
$mimi = new MadMimi('nicholas@madmimi.com', 'f745b56de62ab9b46f613173a10806fb');

print($mimi->Search('nicholas'));

$arr = array('rock' => 'roll', 'all' => 'night');
print(http_build_query($arr));
?>