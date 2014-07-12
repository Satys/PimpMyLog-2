<?php
	require_once 'logm.php';
        $handle = file("C:\\wamp\\www\\task\\log3.log", "r");
        
        echo filesize($handle);
        
       if ($handle){
           while (($buffer = fgets($handle)) !== false) {
               preg_match("|^\\[(.*)\\] \\[(.*)\\] \\[Log\\] (##:## (.*) ##:##)( \\[(.*)\\] )(: (.*))*$|U", $buffer, $matches);
               
               $date = new Time($matches[1]);
               $result = $date->format('Y-m-d H:i:s');
               echo "$result";
               
               logmonitor($matches[5], $matches[3]);
//                echo $matches[3]."\n";
           }
       }else {
           echo 'adshbadf';
       }
       fclose($handle);
?>

