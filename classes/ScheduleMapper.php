<?php

class ScheduleMapper extends Mapper {

    public function SaveSchedule($ScheduleName, $Semester, $Year, $CRNList) {

          $stmt = $this->db->prepare("INSERT into StudentSchedule (ScheduleName, GoogleId, Semester, ScheduleYear, CRN) VALUES(:scheduleName, :userID, :semester, :year, :CRNList) ON DUPLICATE KEY UPDATE CRN=:CRNList");
          $stmt->execute(['scheduleName' => $ScheduleName, 'userID' => $_SESSION['userId'], 'semester' => $Semester, 'year' => $Year, 'CRNList' => $CRNList]);
          if(!$stmt) die("SQL Error");
          return;
    }

    public function getScheduleList() {

        $stmt = $this->db->prepare("SELECT * FROM StudentSchedule WHERE GoogleId=:userId");
        $stmt->execute([
            'userId' => $_SESSION['userId']
        ]);

        $scheduleList = [];
        while($row = $stmt->fetch()) {
            $scheduleList[] = $row;
        }

        return $scheduleList;

    }

    public function openSchedule($id) {

        $stmt = $this->db->prepare("SELECT * FROM StudentSchedule WHERE id=:id");
        $stmt->execute([
            'id' => $id
        ]);

        clearCalendar();
        while($row = $stmt->fetch()) {
            
            $CRNList = explode(",", $row['CRN']);
            $semester = $row['Semester'];
            $year = $row['ScheduleYear'];
            $courseList = [];
            $stmt = $this->db->prepare("SELECT CourseNum FROM Sections WHERE CRN=:CRN AND Semester=:Semester AND Year=:Year");

            foreach($CRNList as $CRN) {
                $stmt->execute(['CRN' => $CRN, 'Semester' => $semester, 'Year' => $year]);
                while($row2 = $stmt->fetch()) {
                    $courseNum = $row2['CourseNum'];
                    $courseList[] = ['CRN' => $CRN, 'CourseNum' => $courseNum];
                }
            }
        }

        return $courseList;

    }

    public function getScheduleInfo($id) {

        $stmt = $this->db->prepare("SELECT * FROM StudentSchedule WHERE id=:id");
        $stmt->execute(['id' => $id]);

        $scheduleInfo = [];
        while($row = $stmt->fetch()) {
            $scheduleInfo[] = $row;
        }

        return $scheduleInfo;

    }

}

?>