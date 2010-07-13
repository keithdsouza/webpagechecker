<?php
/**
	Author: Keith Dsouza
	Loc: http://keithdsouza.com
	Created: 07/13/2010
**/

require_once('snoopy.class.php');

define("DUMP_LOCATION", "/home/keith/pagedumps/");
define("NOTIFY_EMAIL", "addnotifyemailhere");

$websites_to_check = array("test1" => "http://keithdsouza.com",
										"test2" => "http://anotherwebsite.com/");

//run the checks
check_websites_for_change();

function check_websites_for_change() {
	global $websites_to_check;
	$snoopy = new Snoopy;							
	$snoopy->agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
	$snoopy->maxredirs = 2;
	$snoopy->offsiteok = true;
	$snoopy->expandlinks = false;	
		echo "In here";
	foreach($websites_to_check as $websitename => $websiteurl) {
		//only check if the page was fetched and responded with a 200
		if($snoopy->fetch($websiteurl)) {
			if($snoopy->status == 200) {
				$response = $snoopy->results;
				
				$filename = DUMP_LOCATION . strtolower($websitename) . ".dump";
				if(file_exists($filename)) {
					$oldpage = read_content($filename);
					//something changed, send email alert
					if(md5($oldpage) != md5($response)) {
						echo "Olad Page is ".$oldpage;
						echo "Response is ".$response;
						echo "Page changed for ".$websiteurl;
						$subject = "URL Content Has Changed for ".$websitename;
						$body = "URL: ".$websiteurl." Content Changed";
						email_notify($subject, $body);
						
						//overwrite old page with new page for new checks
						write_content($filename, $response);
					}
				}
				else {
					/*
					* if file does not exist this is the first time we are loading it
					* this will create the dump file and it will be used for compare next time
					*/
					write_content($filename, $response);
				}
			}
		}
	}
}

function read_content($filename) {
	$handle = fopen($filename, 'r');
	$content = fread($handle,filesize($filename));
	fclose($handle);
	return $content;
}

function write_content($filename, $content) {
	$handle = fopen($filename, 'w');
	fwrite($handle, $content);
	fclose($handle);
}

function email_notify($subject, $message = "") {
	if($message) {
		$subject = $message;
	}
	$body = "$message";
	mail(NOTIFY_EMAIL, $subject, $body);
}
?>