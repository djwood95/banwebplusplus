<?php

class ScheduleMapper extends Mapper {

    public function SaveSchedule($ScheduleName, $Semester, $Year, $CRNList) {

        if(self::scheduleExists($ScheduleName, $Semester, $Year)) {

        } else {

        }
    }

    private function scheduleExists($name, $semester, $year) {
        $stmt = $con->prepare("SELECT COUNT(*) FROM studentschedule WHERE GoogleId=:userId AND ScheduleName=:scheduleName AND Semester=:semester");
        $stmt->execute([
            'userId' => $_SESSION['userId'],
            'scheduleName' => $ScheduleName,
            'semester' => $semester
        ]);

        return $stmt->fetchColumn() == 1; // return true if there is a schedule w/same name/semester for user
    }
    
}

?>