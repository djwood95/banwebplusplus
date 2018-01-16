<?php

class CourseMapper extends Mapper {

	public function getAvailableSemesters() {
		$stmt = $this->db->prepare("SELECT DISTINCT Semester,Year FROM Sections ORDER BY Year DESC");
		$stmt->execute();

		$results = [];
		while($row = $stmt->fetch()) {
			$results[] = $row['Semester'] . " " . $row['Year'];
		}

		return $results;
	}

	/**
	 * @param  $query - search query string
	 * @param  $semester - filter by a semester (eg Fall 2017)
	 * @return JSON object that contains an array of resulting courses with corresponding info from database
	 */
	public function search($query, $semester) {
		$semesterName = explode(" ", $semester)[0];
		$semesterYear = explode(" ", $semester)[1];
		if(strlen($query) <= 3) {
			$query = "%$query%";
		}else{
			$query_wildcard = "%" . str_replace(" ", "%", $query) . "%";
			$query = "%$query%";
		}

		$stmt = $this->db->prepare("SELECT c.*, s.*, c.CourseNum as CourseNum FROM Courses c
									INNER JOIN Sections s ON c.CourseNum = s.CourseNum
									WHERE (c.CourseNum LIKE :query OR c.CourseName LIKE :query_wildcard OR s.Instructor LIKE :query_wildcard) AND Semester=:semesterName AND Year=:semesterYear
									ORDER BY c.CourseNum");
		$result = $stmt->execute([
			'query' => $query,
			'query_wildcard' => $query_wildcard,
			'semesterName' => $semesterName,
			'semesterYear' => $semesterYear
		]);

		$results = [];
		
		while($row = $stmt->fetch()) {
			
			$courseNum = $row['CourseNum'];
			if($results[$courseNum] == ""){	//First time seeing this course
				$results[$courseNum]['CourseName'] = $row['CourseName'];	//add general information
				$results[$courseNum]['Description'] = $row['Description'];
				$CRN = $row['CRN'];
				$sectionInfo = [];	//new course - reset sectionInfo list
				$sectionInfo[$CRN]['CourseNum'] = $row['CourseNum'];
				$sectionInfo[$CRN]['SectionNum'] = $row['SectionNum'];	//add section info for the first section
				$sectionInfo[$CRN]['Type'] = $row['Type'];
				$sectionInfo[$CRN]['Days'] = $row['Days'];
				$sectionInfo[$CRN]['SectionTime'] = $row['SectionTime'];
				$sectionInfo[$CRN]['Location'] = $row['Location'];
				$sectionInfo[$CRN]['Instructor'] = $row['Instructor'];
				$sectionInfo[$CRN]['SectionActual'] = $row['SectionActual'];
				$sectionInfo[$CRN]['Capacity'] = $row['Capacity'];
				$sectionInfo[$CRN]['Semester'] = self::getSemesterFromDate($row['Dates'], $row['Year']);
				$results[$courseNum]['SectionInfo'] = $sectionInfo;
			}else{	//This course has already been seen - just add new section info
				$sectionInfo = $results[$courseNum]['SectionInfo']; //load in the sections we already have
				$CRN = $row['CRN'];
				$sectionInfo[$CRN]['CourseNum'] = $row['CourseNum'];
				$results[$courseNum]['Description'] = $row['Description'];
				$sectionInfo[$CRN]['SectionNum'] = $row['SectionNum'];	//then add the new section (indexed by CRN)
				$sectionInfo[$CRN]['Type'] = $row['Type'];
				$sectionInfo[$CRN]['Days'] = $row['Days'];
				$sectionInfo[$CRN]['SectionTime'] = $row['SectionTime'];
				$sectionInfo[$CRN]['Location'] = $row['Location'];
				$sectionInfo[$CRN]['Instructor'] = $row['Instructor'];
				$sectionInfo[$CRN]['SectionActual'] = $row['SectionActual'];
				$sectionInfo[$CRN]['Capacity'] = $row['Capacity'];
				$sectionInfo[$CRN]['Semester'] = self::getSemesterFromDate($row['Dates'], $row['Year']);
				$results[$courseNum]['SectionInfo'] = $sectionInfo;	//then add the updated sectionInfo array back to the main results array
			}
		}


		if(!$stmt) die("SQL Error");

		if(count($results) == 0) return "No Results.";

		return $results;
	}


	/**
	 * @param  $dateRange (eg 09/05-12/15)
	 * @param  $year
	 * @return String - name of semester (Fall 2017)
	 */
	private function getSemesterFromDate($dateRange, $year) {
		$dateRange = trim(explode("|", $dateRange)[0]);
		$startDate = explode("-", $dateRange)[0];
		$startDateMonth = (int) explode("/", $startDate)[0];
		$endDate = explode("-", $dateRange)[1];
		$endDateMonth = (int) explode("/", $endDate)[0];

		if($startDateMonth == 1 && $endDateMonth == 4) {
			$semester = "Spring $year";
		}elseif($startDateMonth == 1 && ($endDateMonth == 2 || $endDateMonth == 3)) {
			$semester = "Spring $year (A)";
		}elseif($endDateMonth == 4 && ($startDateMonth > 1 && $startDateMonth < 4)) {
			$semester = "Spring $year (B)";
		}elseif($startDateMonth == 5 && $endDateMonth == 8) {
			$semester = "Summer $year";
		}elseif($startDateMonth == 5 && $endDateMonth == 6) {
			$semester = "Summer $year (A)";
		}elseif($startDateMonth == 7 && $endDateMonth == 8) {
			$semester = "Summer $year (B)";
		}elseif(($startDateMonth == 8 || $startDateMonth == 9) && $endDateMonth == 12) {
			$semester = "Fall $year";
		}elseif(($startDateMonth == 8 || $startDateMonth == 9) && ($endDateMonth == 10 || $endDateMonth == 11)) {
			$semester = "Fall $year (A)";
		}elseif(($startDateMonth == 10 || $startDateMonth == 11) && $endDateMonth == 12) {
			$semester = "Fall $year (B)";
		}else{
			$semester = "Unknown Semester";
		}

		return $semester;
	}

	public function getCourseInfo($courseNum, $semester) {
		$semesterName = explode(" ", $semester)[0];
		$semesterYear = explode(" ", $semester)[1];
		$stmt = $this->db->prepare("SELECT s.*, c.*, s.timestamp AS lastModified FROM Courses c JOIN Sections s ON c.CourseNum = s.CourseNum
									WHERE c.CourseNum=:courseNum AND s.Semester=:semesterName AND s.Year=:semesterYear
									ORDER BY s.timestamp DESC");
		$stmt->execute([
			'courseNum' => $courseNum,
			'semesterName' => $semesterName,
			'semesterYear' => $semesterYear
		]);

		if(!$stmt) return "SQL Error!";

		$results = [];
		while($row = $stmt->fetch()) {
			$courseNum = $row['CourseNum'];
			if(!is_array($results[$courseNum])){	//First time seeing this course
				$results[$courseNum]['CourseName'] = $row['CourseName'];	//add general information
				$results[$courseNum]['Description'] = $row['Description'];
				$results[$courseNum]['Credits'] = $row['Credits'];
				$results[$courseNum]['Prereq'] = $row['Prereq'];
				$results[$courseNum]['Coreq'] = $row['Coreq'];
				$results[$courseNum]['LectureCredits'] = $row['LectureCredits'];
				$results[$courseNum]['RecitationCredits'] = $row['RecitationCredits'];
				$results[$courseNum]['LabCredits'] = $row['LabCredits'];
				$results[$courseNum]['Restrictions'] = $row['Restrictions'];
				$results[$courseNum]['SemestersOffered'] = $row['SemestersOffered'];
				$CRN = $row['CRN'];
				$sectionInfo[$CRN]['SectionNum'] = $row['SectionNum'];	//add section info for the first section
				$sectionInfo[$CRN]['Type'] = $row['Type'];
				$sectionInfo[$CRN]['Days'] = $row['Days'];
				$sectionInfo[$CRN]['SectionTime'] = $row['SectionTime'];
				$sectionInfo[$CRN]['Location'] = $row['Location'];
				$sectionInfo[$CRN]['Instructor'] = $row['Instructor'];
				$sectionInfo[$CRN]['SectionActual'] = $row['SectionActual'];
				$sectionInfo[$CRN]['Capacity'] = $row['Capacity'];
				$sectionInfo[$CRN]['Semester'] = self::getSemesterFromDate($row['Dates'], $row['Year']);
				$results[$courseNum]['SectionInfo'] = $sectionInfo;

				$lastModifiedText = round((time() - strtotime($row['lastModified'])) / 60) . " minutes ago";
				$results[$courseNum]['lastModified'] = $lastModifiedText;
			}else{	//This course has already been seen - just add new section info
				$sectionInfo = $results[$courseNum]['SectionInfo']; //load in the sections we already have
				$CRN = $row['CRN'];
				$sectionInfo[$CRN]['SectionNum'] = $row['SectionNum'];	//then add the new section (indexed by CRN)
				$sectionInfo[$CRN]['Type'] = $row['Type'];
				$sectionInfo[$CRN]['Days'] = $row['Days'];
				$sectionInfo[$CRN]['SectionTime'] = $row['SectionTime'];
				$sectionInfo[$CRN]['Location'] = $row['Location'];
				$sectionInfo[$CRN]['Instructor'] = $row['Instructor'];
				$sectionInfo[$CRN]['SectionActual'] = $row['SectionActual'];
				$sectionInfo[$CRN]['Capacity'] = $row['Capacity'];
				$sectionInfo[$CRN]['Semester'] = self::getSemesterFromDate($row['Dates'], $row['Year']);
				$results[$courseNum]['SectionInfo'] = $sectionInfo;	//then add the updated sectionInfo array back to the main results array
			}
		}

		return $results;
	}

	public function getCourseInfoForCalendar($CRN, $courseNum) {
		$stmt = $this->db->prepare("SELECT s.*, c.* FROM Sections s JOIN Courses c ON s.CourseNum = c.CourseNum WHERE s.CRN=:CRN AND s.CourseNum=:courseNum");
		$stmt->execute([
			'CRN' => $CRN,
			'courseNum' => $courseNum
		]);

		$results = [];
		while($row = $stmt->fetch()){
			$results[] = $row;
		}

		return $results;
	}

}


?>