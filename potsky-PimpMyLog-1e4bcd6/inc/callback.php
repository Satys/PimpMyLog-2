<?php
	require_once 'logm.php';
	// header( 'Content-type: text/html; charset=utf-8' );
	tail('C:\\wamp\\www\\task\\log3.log', 'callback'); 
	//tail('C:\wamp32\bin\apache\Apache2.2.21\logs\dummy-host2.example.com-access.log'); 
	function tail($file, $callback = null, $usec_delay = 500)
	{
		if(!file_exists($file) || !is_readable($file))
		{
			trigger_error('Cannot access '.$file, E_USER_ERROR);
		}
		if(!is_null($callback) && !is_callable($callback))
		{
			trigger_error('Bad callback argument', E_USER_ERROR);
		}

		$fp = fopen($file, 'r');
		if(filesize($file) > 2048)
		{
			fseek($fp, -2048, SEEK_END);
			
		}
		$data = '';
		// while($data != "\n")
		// {
			// $data = fgetc($fp);
			
		// }
		$out = '';
		while(true)
		{
			while($data = fread($fp, 1024))
			{	
				$data = explode("\n", $data);
				$out .= $data[0];
				if(isset($data[1]))
				{
					$out .= "\n";
					if(!is_null($callback))
					{
						call_user_func($callback, $out);
					}
					else
					{
						echo $out;
					}
					$out = $data[1];
				}
				echo 'Now Sleep';
			}
			
			usleep($usec_delay);
			
		}
	}  

	function callback($out){
		// ob_end_flush();
		preg_match("|^\\[(.*)\\] \\[(.*)\\]  \\[Log\\] (.*)( \\[(.*)\\]   )(: (.*))*$|U", $out, $matches);
		logmonitor($matches[5], $matches[3]);
		echo $out;
		//flush();
	//    ob_flush();
		/// ob_start();
	}

	die();
?>