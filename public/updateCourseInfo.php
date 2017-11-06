<?php
	
	/* When this script is run, class schedule information is taken from banweb for all semesters within the past year
	 * And placed into html files in the banwebFiles/ directory with the format YYYYMM.html, where MM is the month number
	 * that the semester starts in (08 = fall, 01 = spring, 05 = summer).
	 * There is no return unless there is an error
	**/

	require __DIR__ . '/../vendor/autoload.php';
	use Sunra\PhpSimple\HtmlDomParser;
	
	$semesterCodeList = getAvailableSemesters();

	foreach($semesterCodeList as $semesterCode) {
		//scrapeSemester($semesterCode);
		echo $semesterCode . "<br/>";
	}

	/* Get information from specified semester */
	function scrapeSemester($semesterCode) {

		$curl = curl_init();

	    curl_setopt_array($curl, array(
	      CURLOPT_URL => "https://www.banweb.mtu.edu/pls/owa/bzckschd.p_get_crse_unsec",
	      CURLOPT_RETURNTRANSFER => true,
	      CURLOPT_ENCODING => "",
	      CURLOPT_MAXREDIRS => 10,
	      CURLOPT_TIMEOUT => 30,
	      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	      CURLOPT_CUSTOMREQUEST => "POST",
	      CURLOPT_POSTFIELDS => "begin_ap=a&begin_hh=0&begin_mi=0&end_ap=a&end_hh=0&end_mi=0&sel_attr=dummy&sel_attr=%25&sel_camp=dummy&sel_camp=%25&sel_crse=&sel_day=dummy&sel_from_cred=&sel_insm=dummy&sel_instr=dummy&sel_instr=%25&sel_levl=dummy&sel_levl=%25&sel_ptrm=dummy&sel_ptrm=%25&sel_schd=dummy&sel_schd=%25&sel_sess=dummy&sel_subj=dummy&sel_subj=&sel_title=&sel_to_cred=&term_in=$semesterCode",
	      CURLOPT_HTTPHEADER => array(
	        "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
	        "accept-encoding: gzip, deflate, br",
	        "accept-language: en-US,en;q=0.8",
	        "cache-control: no-cache",
	        "content-type: application/x-www-form-urlencoded",
	        "cookie: TESTID=set; __cfduid=d26e6bb2d71946ec2c6bb2e8aab9009ba1493084278; __unam=7bc3574-15d17e1c11e-7ed8a39b-1; _ga=GA1.2.2134920387.1493136985; _ceg.s=ou35tf; _ceg.u=ou35tf; __utma=52490228.2134920387.1493136985.1501625072.1501724211.34; __utmc=52490228; __utmz=52490228.1501625072.33.8.utmcsr=facebook.com|utmccn=(referral)|utmcmd=referral|utmcct=/; sghe_magellan_null_username=; sghe_magellan_null_locale=en_US",
	        "origin: https://www.banweb.mtu.edu",
	        "postman-token: 98536ccb-0465-7e79-622c-07db2d99db1b",
	        "referer: https://www.banweb.mtu.edu/pls/owa/bzckschd.p_get_crse_unsec",
	        "upgrade-insecure-requests: 1",
	        "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36"
	      ),
	    ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      if(!$response) die($err);

      curl_close($curl);

      if ($err) {
        return "cURL Error #:" . $err;
      } else {
        //Save response into an html file
        $file = fopen(__DIR__ . "/../banwebFiles/$semesterCode.html", 'w') or die("could not open file");
        fwrite($file, $response);
        fclose($file);
        //return "success";
        //echo $response;
      }

	}

	/* Generate list of semesters available on Banweb starting from the current semester forward
	 * @return array<SemesterCodeString>
	 */
	function getAvailableSemesters() {
		$currentYear = date("Y");

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://www.banweb.mtu.edu/pls/owa/bzskfcls.p_sel_crse_search",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
		    "accept-encoding: gzip, deflate, br",
		    "accept-language: en-US,en;q=0.8",
		    "cache-control: no-cache",
		    "cookie: TESTID=set; MTUSTUDENT=set; __cfduid=d26e6bb2d71946ec2c6bb2e8aab9009ba1493084278; __unam=7bc3574-15d17e1c11e-7ed8a39b-1; _ceg.s=owwpq3; _ceg.u=owwpq3; _ceir=1; MTUPHPBB_data=a%3A2%3A%7Bs%3A11%3A%22autologinid%22%3Bs%3A0%3A%22%22%3Bs%3A6%3A%22userid%22%3Bs%3A5%3A%2219847%22%3B%7D; MTUPHPBB_sid=4739afde565c0abc3142e85180223c20; MTUPHPBB_t=a%3A4%3A%7Bi%3A63325%3Bi%3A1506972700%3Bi%3A63294%3Bi%3A1506972724%3Bi%3A63318%3Bi%3A1506972744%3Bi%3A63336%3Bi%3A1506972796%3B%7D; _ga=GA1.2.2134920387.1493136985; auditCookie=\"M/48rcrRqLJvUBBXLLcAEAOFd/CrqnQt3X0c1F165PlzyPcGcGyolrhJBoHELnyYyAKi4paZbnYWllDdMpTkiny3jxjXkkJyLLtpS8uZ5lY=\"; sghe_magellan_M75783634_locale=en_US; __utma=52490228.2134920387.1493136985.1507738703.1507749784.68; __utmc=52490228; __utmz=52490228.1507749784.68.29.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided); sghe_magellan_null_username=; sghe_magellan_null_locale=en_US",
		    "postman-token: 4a0fbea9-40dd-ca1a-c0df-981e32016396",
		    "referer: http://www.mtu.edu/registrar/students/registration/prepare/",
		    "upgrade-insecure-requests: 1",
		    "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			die("cURL Error #:" . $err);
		}
		

		$dom = HtmlDomParser::str_get_html( $response );
		$semesterList = $dom->find('#term_input_id')[0]->find('option');
		$earliestYear = date("Y");

		echo $earliestYear;
		foreach($semesterList as $semesterElement) {
			$semesterName = $semesterElement->plaintext;
			$semesterCode = $semesterElement->value;
			$semesterYear = (int) explode(" ", $semesterName)[1];

			//If semester is within last year, add it to list of semesters to scrape.
			if($semesterYear > $earliestYear){
				$semesterCodeList[] = $semesterCode;
			}
		}

		return $semesterCodeList;
	}


	

?>