<?php

class ScheduleMapper extends Mapper
{
    public function AddCourseToSchedule($ScheduleName, $UserID, $CRN)
    {
        $stmt = $this->db->prepare("CALL AddCourseToSchedule(:scheduleName, :userID, :crn");
        $stmt->execute(['scheduleName' => $ScheduleName, 'userID' => $UserID, 'crn' => $CRN]);
        if(!$stmt) die("SQL Error");
        return;
    }
    
    public function AddSchedule($ScheduleName, $UserID, $Semester, $Year, $CRN)
    {
        //$stmt = $this->db->prepare("CALL AddSchedule(:scheduleName, :userID, :semester, :year");
        $stmt = $this->db->prepare("INSERT into StudentSchedule (ScheduleName, GoogleId, Semester, ScheduleYear, CRN) VALUES(:scheduleName, :userID, :semester, :year) ON DUPLICATE KEY UPDATE CRN = VALUES(:CRN)");
        $stmt->execute(['scheduleName' => $ScheduleName, 'userID' => $UserID, 'semester' => $Semester, 'year' => $Year, 'crn' => $CRN]);
        if(!$stmt) die("SQL Error");
        return;
    }
}

?>