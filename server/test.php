<?php 

chdir('data_mining_py');
$command = escapeshellcmd('python data_mining.py');
$output = shell_exec($command);
//echo $output;


if(1!=1)    echo 'foo' ;


?>