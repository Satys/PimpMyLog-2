<?php
    function logmonitor($name, $post)
    {
      $array = array();
      $array['query'] = 'local repository '.$post;
      $array['username'] = $name."-local";
      if(is_array($array) )       
      $post = http_build_query($array);

      $curl = curl_init();
      curl_setopt($curl,CURLOPT_URL,'http://ophonebook.com/datacatch.php');
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
      curl_setopt($curl, CURLOPT_TIMEOUT_MS, 5000);//If you do not want to wait for the above url
      //curl_setopt($curl, CURLOPT_NOSIGNAL, 1);// If you do not want to wait for the above url
      curl_setopt($curl, CURLOPT_RETURNTRANSFER,FALSE);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,FALSE);
      curl_exec($curl); //when you need result, it waits for the server to give result
      curl_close($curl);
    }
?>

