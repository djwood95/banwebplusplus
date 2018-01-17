<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');
	
set_time_limit(3000);

use Sunra\PhpSimple\HtmlDomParser;
use PHPHtmlParser\Dom;
use DiDom\Document;

class Scraper extends Mapper {
	
	private $courseDescriptions;
	private $subjects = ['ACC', 'AF', 'AR', 'ATM', 'BMB', 'BL', 'BE', 'BUS', 'BA', 'CM', 'CH', 'CEE', 'CSE', 'CS', 'CMG', 'EC', 'ED', 'EE', 'EET', 'ENG', 'ESL', 'ENT', 'FIN', 'FW', 'GE', 'HU', 'EH', 'MGT', 'MIS', 'MKT', 'MY', 'MA', 'MEEM', 'MET', 'OSM', 'HON', 'PE', 'PH', 'PSY', 'SA', 'SS', 'SU', 'SAT', 'TE', 'UN', 'FA'];


	public function updateSections($subject, $mode) {

		$semesterList = self::getAvailableSemesters();
		$startMemory = memory_get_usage();

		if($mode == "detailed") $this->courseDescriptions = self::getCourseDescriptions();
		//print_r($this->courseDescriptions);

		if($subject == "all") {
			foreach($semesterList as $semesterData) {
				foreach($this->subjects as $subject) {
					echo $semesterData['name']." ".$semesterData['year']." | $subject | $mode<br/>";
					self::scrapeSemester($semesterData, $subject, $mode);
				}
			}
		} else {
			foreach($semesterList as $semesterData) {
				self::scrapeSemester($semesterData, $subject, $mode);
			}
		}

		$memory = memory_get_usage() - $startMemory;
		echo $memory/1000 . " kb";
	}
 

	private function scrapeSemester($semesterData, $subject, $mode) {

		$semesterCode = $semesterData['code'];
		$curl = curl_init();

	    curl_setopt_array($curl, array(
	      CURLOPT_URL => "https://www.banweb.mtu.edu/pls/owa/bzckschd.p_get_crse_unsec",
	      CURLOPT_RETURNTRANSFER => true,
	      CURLOPT_ENCODING => "",
	      CURLOPT_MAXREDIRS => 10,
	      CURLOPT_TIMEOUT => 30,
	      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	      CURLOPT_CUSTOMREQUEST => "POST",
	      CURLOPT_POSTFIELDS => "begin_ap=a&begin_hh=0&begin_mi=0&end_ap=a&end_hh=0&end_mi=0&sel_attr=dummy&sel_attr=%25&sel_camp=dummy&sel_camp=%25&sel_crse=&sel_day=dummy&sel_from_cred=&sel_insm=dummy&sel_instr=dummy&sel_instr=%25&sel_levl=dummy&sel_levl=%25&sel_ptrm=dummy&sel_ptrm=%25&sel_schd=dummy&sel_schd=%25&sel_sess=dummy&sel_subj=dummy&sel_subj=$subject&sel_title=&sel_to_cred=&term_in=$semesterCode",
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

      curl_close($curl);

      if ($err) {
        return "cURL Error #:" . $err;
      } else {
        //Parse the HTML
		$data = self::parseHTML($response, $semesterData, $mode);
		print_r($data);
		echo "<hr/>";
		self::updateDatabase($data, $mode);
      }

	}

	private function updateDatabase($data, $mode) {
		foreach($data as $courseData) {
			if($courseData != null) {
				$courseNum = $courseData['courseNum'];
				$crn = $courseData['crn'];
				print_r($courseData)."<br/><br/>";

				if($mode == "detailed") {
					if(self::courseExists($courseNum)) {
						$stmt = $this->db->prepare("UPDATE Courses SET CourseName=:title, Description=:description, SemestersOffered=:semestersOffered, LectureCredits=:lecCredits, RecitationCredits=:recCredits, LabCredits=:labCredits, Restrictions=:restrictions, Prereq=:preReqs WHERE CourseNum=:courseNum");
						$stmt->execute([
							'title' => $courseData['title'],
							'description' => $courseData['description'],
							'semestersOffered' => $courseData['semestersOffered'],
							'lecCredits' => $courseData['lecCredits'],
							'recCredits' => $courseData['recCredits'],
							'labCredits' => $courseData['labCredits'],
							'restrictions' => $courseData['restrictions'],
							'preReqs' => $courseData['preReqs'],
							'courseNum' => $courseNum
						]);
					} else {
						$stmt = $this->db->prepare("INSERT INTO Courses (CourseNum, CourseName, Description, SemestersOffered, LectureCredits, RecitationCredits, LabCredits, Restrictions, Prereq)
											VALUES(:courseNum, :title, :description, :semestersOffered, :lecCredits, :recCredits, :labCredits, :restrictions, :preReqs)");
						$stmt->execute([
							'courseNum' => $courseNum,
							'title' => $courseData['title'],
							'description' => $courseData['description'],
							'semestersOffered' => $courseData['semestersOffered'],
							'lecCredits' => $courseData['lecCredits'],
							'recCredits' => $courseData['recCredits'],
							'labCredits' => $courseData['labCredits'],
							'restrictions' => $courseData['restrictions'],
							'preReqs' => $courseData['preReqs']
						]);
					}

					if(self::sectionExists($crn, $courseData['semester'], $courseData['year'])) {
						$stmt = $this->db->prepare("UPDATE Sections SET CourseNum=:courseNum, SectionNum=:section, Type=:type, Days=:days, SectionTime=:time, Location=:location, SectionActual=:act, Capacity=:cap, Instructor=:instructor, Dates=:dates, Fee=:fee, Online=:online, Credits=:credits, lastModified=:currentTime WHERE CRN=:crn AND Semester=:semester AND Year=:year");
						$stmt->execute([
							'courseNum' => $courseNum,
							'section' => $courseData['section'],
							'type' => $courseData['type'],
							'days' => $courseData['days'],
							'time' => $courseData['time'],
							'location' => $courseData['location'],
							'act' => $courseData['act'],
							'cap' => $courseData['cap'],
							'instructor' => $courseData['instructor'],
							'dates' => $courseData['dates'],
							'fee' => $courseData['fee'],
							'online' => $courseData['online'],
							'credits' => $courseData['credits'],
							'crn' => $courseData['crn'],
							'semester' => $courseData['semester'],
							'year' => $courseData['year'],
							'currentTime' => date("Y-m-d H:i:s")
						]);
					} else {
						$stmt = $this->db->prepare("INSERT INTO  Sections (CourseNum, SectionNum, Type, Days, SectionTime, Location, SectionActual, Capacity, Instructor, Dates, Fee, Online, Credits, 												   Semester, Year, CRN, lastModified)
													VALUES(:courseNum, :section, :type, :days, :time, :location, :act, :cap, :instructor, :dates, :fee, :online, :credits, :semester, :year, :crn, :currentTime)");
						$stmt->execute([
							'courseNum' => $courseNum,
							'section' => $courseData['section'],
							'type' => $courseData['type'],
							'days' => $courseData['days'],
							'time' => $courseData['time'],
							'location' => $courseData['location'],
							'act' => $courseData['act'],
							'cap' => $courseData['cap'],
							'instructor' => $courseData['instructor'],
							'dates' => $courseData['dates'],
							'fee' => $courseData['fee'],
							'online' => $courseData['online'],
							'credits' => $courseData['credits'],
							'semester' => $courseData['semester'],
							'year' => $courseData['year'],
							'crn' => $courseData['crn'],
							'currentTime' => date("Y-m-d H:i:s")
						]);
					}

				// SIMPLE MODE //
				} else {
					if(self::sectionExists($crn, $courseData['semester'], $courseData['year'])) {
						$stmt = $this->db->prepare("UPDATE Sections SET SectionNum=:section, Days=:days, SectionTime=:time, Location=:location, SectionActual=:act, Capacity=:cap, Instructor=:instructor, lastModified=:currentTime  WHERE CRN=:crn AND Semester=:semester AND Year=:year");
						$stmt->execute([
							'section' => $courseData['section'],
							'days' => $courseData['days'],
							'time' => $courseData['time'],
							'location' => $courseData['location'],
							'act' => $courseData['act'],
							'cap' => $courseData['cap'],
							'instructor' => $courseData['instructor'],
							'crn' => $courseData['crn'],
							'semester' => $courseData['semester'],
							'year' => $courseData['year'],
							'currentTime' => date("Y-m-d H:i:s")
						]);
					} else {
						// Due to issues with things like CRN not being set in simple mode, insertions only made in detailed mode
						/*
						$stmt = $this->db->prepare("INSERT INTO Sections (SectionNum, Days, SectionTime, Location, SectionActual, Capacity, Instructor)
											VALUES(:section, :days, :time, :location, :act, :cap, :instructor)");
						$stmt->execute([
							'section' => $courseData['section'],
							'days' => $courseData['days'],
							'time' => $courseData['time'],
							'location' => $courseData['location'],
							'act' => $courseData['act'],
							'cap' => $courseData['cap'],
							'instructor' => $courseData['instructor']
						]);
						*/
					}
				}
			}
		}
	}

	private function courseExists($courseNum) {
		$stmt = $this->db->prepare("SELECT COUNT(*) AS numCourses FROM Courses WHERE CourseNum=:courseNum");
		$stmt->execute([
			'courseNum' => $courseNum
		]);

		while($row = $stmt->fetch()) {
			$numCourses = $row['numCourses'];
		}

		return ($numCourses == 1); //return true if course exists
	}

	private function sectionExists($crn, $semester, $year) {
		$stmt = $this->db->prepare("SELECT COUNT(*) AS numCourses FROM Sections WHERE CRN=:crn AND Semester=:semester AND year=:year");
		$stmt->execute([
			'crn' => $crn,
			'semester' => $semester,
			'year' => $year
		]);

		while($row = $stmt->fetch()) {
			$numCourses = $row['numCourses'];
		}

		return ($numCourses == 1); //return true if course exists
	}

	/* Reads HTML file -> Parses relevant data -> 
	Returns assoc. array with all data (note that array can vary depending on mode) */
	private function parseHTML($html, $semesterData, $mode) {

		$semesterCode = $semesterData['code'];
		$dom = new Document($html);
		$dataTable = $dom->find('table.datadisplaytable')[0];
		$tableRows = $dataTable->find('tr');

		// Stop if no info found to prevent errors later on
		if(strpos($html, "No classes were found that meet your search criteria") !== false) {
			return null;
		}

		$allData = array();
		foreach($tableRows as $i => $row) {
			$cols = $row->find('td');
			if(count($cols) == 16 && count($cols[0]->find('a')) > 0) {

				$data = array();
				//echo "<b>".count($cols)."</b><br/><br/>";
				//Get the basics that need frequent updating
				//print_r($cols);
				//echo "<br/><br/>";
				$data['semester'] = $semesterData['name'];
				$data['year'] = $semesterData['year'];
				$data['instructor'] = trim($cols[12]->text());
				$crn = trim($cols[0]->find('a')[0]->text());
				$data['crn'] = $crn;
				$subj = trim($cols[1]->text());
				$crse = trim($cols[2]->text());
				$data['courseNum'] = $subj." ".$crse;
				$data['section'] = trim($cols[3]->text());
				$data['days'] = trim(preg_replace('/[^MTWRF]/', '', $cols[7]->text()));
				$data['time'] = trim($cols[8]->text());
				$data['cap'] = (int) trim(preg_replace('/[^0-9]/', '', $cols[9]->text()));
				$data['act'] = (int) trim(preg_replace('/[^0-9]/', '', $cols[10]->text()));
				//$rem = (int) trim(preg_replace('/[^0-9]/', '', $cols[11]->text())); (not necessary so ignored)
				$data['location'] = trim($cols[14]->text());

				if($mode == "detailed") {

					if($data['section'][0] == "L") {
						$data['type'] = "Lab";
					} elseif($data['section'][0] == "R") {
						$data['type'] = "Lecture";
					} else {
						$data['type'] = "Unkown";
					}

					$campus = trim($cols[4]->text());
					$data['online'] = ($campus == "1" ? 0 : 1);
					$data['credits'] = trim($cols[5]->text());
					$data['title'] = trim($cols[6]->text());
					$data['dates'] = trim($cols[13]->text());
					$data['fee'] = trim($cols[15]->text());

					// If course description has not been retrieved, get it from banweb
					if(isset($this->courseDescriptions[$data['courseNum']])) {
						$extraInfo = $this->courseDescriptions[$data['courseNum']];
						//print_r($extraInfo);
						//echo "<br/>";
						$data = array_merge($data, $extraInfo);
					} else {
						$extraInfo = self::getExtraInfo($semesterCode, $subj, $crse, $crn);
						//print_r($extraInfo);
						//echo "<br/>";
						$data = array_merge($data, $extraInfo);
						//echo " ";
					}
			
				}

				$allData[] = $data;
			}

		}

		return $allData;

	}

	private function getExtraInfo($semesterCode, $subject, $courseNum, $crn) {

		$html = self::getHtmlFromUrl("https://www.banweb.mtu.edu/pls/owa/bwckschd.p_disp_listcrse?term_in=$semesterCode&subj_in=$subject&crse_in=$courseNum&crn_in=$crn");

		// Stop if no info found to prevent errors later on
		if(strpos($html, "No classes were found that meet your search criteria") !== false) {
			return null;
		}

		$dom = HtmlDomParser::str_get_html( $html );
		$dataTable = $dom->find('table.datadisplaytable')[0];
		$html = $dataTable->find('tr',0)->find('td',0);

		preg_match("/<td class=\"dddefault\"> <b>[\S\s]+<\/b><br> ([\S\s]+?)<br>/", $html, $description);
		$description = $description[1];

		preg_match("/<b>Lec-Rec-Lab:<\/b> \((\d)-(\d)-(\d)\)<br>/", $html, $labRecLec);
		$labCredits = count($labRecLec) > 0 ? $labRecLec[1] : 0;
		$recCredits = count($labRecLec) > 0 ? $labRecLec[2] : 0;
		$lecCredits = count($labRecLec) > 0 ? $labRecLec[3] : 0;

		preg_match("/<b>Semesters Offered:<\/b> ([\S\s]+?)<br>/", $html, $semestersOffered);
		$semestersOffered = $semestersOffered[1];

		preg_match("/<b>Pre-Requisite\(s\):<\/b> ([\S\s]+?)<br> <\/td>/", $html, $preReqs);
		$preReqs = count($preReqs) > 0 ? $preReqs[1] : null;
		$preReqs = str_replace("and", "&", $preReqs);
		$preReqs = str_replace("or", "|", $preReqs);

		preg_match("/<b>Restrictions: <\/b>([\S\s]+?)(?><br>|<\/td>)/", $html, $rescrictions);
		$restrictions = $rescrictions[1];

		
		/*
		$topItems = explode("<br>", $html);
		
		$description = trim($topItems[1]);

		$labRecLec = trim(explode("</b>", $topItems[3])[1]);
		$labRecLec = explode("-", $labRecLec);
		$labCredits = trim(str_replace("(", "", $labRecLec[0]));
		$recCredits = trim($labRecLec[1]);
		$lecCredits = trim(str_replace(")", "", $labRecLec[2]));

		$semestersOffered = trim(explode("</b>", $topItems[4])[1]);

		$preReqs = trim(explode("</b>", $topItems[5])[1]);
		$preReqs = str_replace(" or ", "|", $preReqs);
		$preReqs = str_replace(" and ", "&", $preReqs);
		$preReqs = str_replace(" </td>", "", $preReqs);
		*/

		$data = [
			'description' => $description,
			'labCredits' => $labCredits,
			'recCredits' => $recCredits,
			'lecCredits' => $lecCredits,
			'preReqs' => $preReqs,
			'restrictions' => $restrictions,
			'semestersOffered' => $semestersOffered
		];

		return $data;
	}

	private function getCourseDescriptions() {
		$html = self::getHtmlFromUrl("https://www.banweb.mtu.edu/pls/owa/stu_ctg_utils.p_online_all_courses_ug");
		$sections = explode("<hr>", $html);
		$courseDescriptions = array();

		$courses = explode("<br><br>", $html);
		//echo count($courses)." ";
		foreach($courses as $courseHtml) {
			preg_match("/<b>([A-Z]{2,3} [0-9]{4}) - ([\S\s]+?)<\/b><br>([\S\s]+?)<br><b>Credits:<\/b>([\S\s]+?)<br>\n<b>Lec-Rec-Lab:<\/b> \(([0-9])-([0-9])-([0-9])\)<br>\n<b>Semesters Offered:<\/b>([\S\s]+?)<br>/", $courseHtml, $matches);
			$courseNum = $matches[1];
			//echo $courseNum." ";
			$courseDescriptions[$courseNum] = [
				'title' => trim($matches[2]),
				'description' => trim($matches[3]),
				'lecCredits' => (int) $matches[5],
				'recCredits' => (int) $matches[6],
				'labCredits' => (int) $matches[7],
				'semestersOffered' => trim($matches[8])
			];

			preg_match("/<b>Pre-Requisite\(s\):<\/b> ([\S\s]+?)<br>/", $courseHtml, $matches);
			$courseDescriptions[$courseNum]['preReqs'] = count($matches) > 0 ? $matches[1] : null;

			preg_match("/<b>Restrictions: <\/b>\n([\S\s]+?)<br>/", $courseHtml, $matches);
			$courseDescriptions[$courseNum]['restrictions'] = count($matches) > 0 ? $matches[1] : "";
		}

		return $courseDescriptions;
	}

	private function getHtmlFromUrl($url) {
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "$url",
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
			return "cURL Error #:" . $err;
		}

		return $response;
	}

	private function getAvailableSemesters() {
		$currentYear = date("Y");

		$html = self::getHtmlFromUrl("https://www.banweb.mtu.edu/pls/owa/bzskfcls.p_sel_crse_search");

		$dom = HtmlDomParser::str_get_html( $html );
		$semesterList = $dom->find('#term_input_id')[0]->find('option');
		$earliestYear = date("Y");
		foreach($semesterList as $semesterElement) {
			$semesterName = $semesterElement->plaintext;
			$semesterCode = $semesterElement->value;
			$semesterYear = explode(" ", $semesterName)[1];
			$semesterName2 = explode(" ", $semesterName)[0];

			//If semester is within last year, add it to list of semesters to scrape.
			if($semesterYear >= $earliestYear){
				$semesterCodeList[] = ['code' => $semesterCode, 'name' => $semesterName2, 'year' => $semesterYear];
			}
		}

		return $semesterCodeList;
	}

}

?>