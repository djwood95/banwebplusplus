<?php
	
	/* Get descriptions and pre-req/co-req info and dump into file */
	$html = file_get_contents("https://www.banweb.mtu.edu/pls/owa/stu_ctg_utils.p_online_all_courses_ug");
	$file = fopen(__DIR__ . "/../banwebFiles/descriptions.html", 'w');
    fwrite($file, $html);
    fclose($file);

?>