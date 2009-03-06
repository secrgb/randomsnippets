<?php
   $linklist = array(
	1 => "www.site.com",
	2 => "another.site.net",
   );

   preg_match('@^(?:http://)?([^/]+)@i', $_SERVER['HTTP_REFERER'], $matches);
   $host = $matches[1];

   $cnt = count($linklist);
   for($i=1;$i<=$cnt;$i++)
   {
	
        if($host == $linklist[$i]) {
	   header("Location: http://www.destination.org/");
         }
    }

?>
