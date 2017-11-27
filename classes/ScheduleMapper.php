<?php

class ScheduleMapper extends Mapper {

    public function SaveSchedule($ScheduleName, $Semester, $Year, $CRNList) {

          $stmt = $this->db->prepare("INSERT into StudentSchedule (ScheduleName, GoogleId, Semester, ScheduleYear, CRN) VALUES(:scheduleName, :userID, :semester, :year, :CRNList) ON DUPLICATE KEY UPDATE CRN=:CRNList");
 +        $stmt->execute(['scheduleName' => $ScheduleName, 'userID' => $_SESSION['userId'], 'semester' => $Semester, 'year' => $Year, 'CRNList' => $CRNList]);
          if(!$stmt) die("SQL Error");
          return;
        }
    }

?>