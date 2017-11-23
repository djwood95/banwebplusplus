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
    
    public function AddSchedule($ScheduleName, $UserID, $Semester, $Year)
    {
        $stmt = $this->db->prepare("CALL AddSchedule(:scheduleName, :userID, :semester, :year");
        $stmt->execute(['scheduleName' => $ScheduleName, 'userID' => $UserID, 'semester' => $Semester, 'year' => $Year]);
        if(!$stmt) die("SQL Error");
        return;
    }
}

?>