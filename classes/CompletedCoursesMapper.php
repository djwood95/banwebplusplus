<?php

class CompletedCoursesMapper extends Mapper {

    public function getSubjects() {
        $stmt = $this->db->prepare("SELECT CourseNum FROM Courses");
        $stmt->execute();
        if(!$stmt) die("SQL Error");

        $subjectList = [];
        while($row = $stmt->fetch()) {
            $subject = explode(" ", $row['CourseNum'])[0];
            if(!in_array($subject, $subjectList))
                $subjectList[] = $subject;
        }

        return $subjectList;
    }

    public function getCoursesInSubj($subject) {
        $stmt = $this->db->prepare("SELECT CourseNum, CourseName FROM Courses");
        $stmt->execute();
        if(!$stmt) die("SQL Error");

        $courseList = [];
        while($row = $stmt->fetch()) {
            $thisSubject = explode(" ", $row['CourseNum'])[0];
            if($thisSubject == $subject)
                $courseList[] = [
                    'CourseName' => $row['CourseName'],
                    'CourseNum' => $row['CourseNum'],
                    'Subject' => $thisSubject,
                    'Completed' => self::isCompleted($row['CourseNum'])
                ];
        }

        return $courseList;
    }

    private function isCompleted($courseNum) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM CompletedCourses WHERE CourseNum=:courseNum AND GoogleId=:userId");
        $stmt->execute([
            'courseNum' => $courseNum,
            'userId' => $_SESSION['userId']
        ]);

        $numResults = $stmt->fetchColumn();

        return $numResults == 1;
    }

    public function markComplete($courseNum, $subject) {
        $stmt = $this->db->prepare("INSERT INTO CompletedCourses (GoogleId, Subject, CourseNum) VALUES (:userId, :subject, :courseNum)");
        $stmt->execute([
            'userId' => $_SESSION['userId'],
            'subject' => $subject,
            'courseNum' => $courseNum
        ]);

        if(!$stmt) return false;

        return true;
    }

    public function markIncomplete($courseNum) {
        $stmt = $this->db->prepare("DELETE FROM CompletedCourses WHERE GoogleId=:userId AND CourseNum=:courseNum");
        $stmt->execute([
            'courseNum' => $courseNum,
            'userId' => $_SESSION['userId']
        ]);

        if(!$stmt) return false;

        return true;
    }

    public function getPreReqCourseNames($courseList) {
        $courseNamesList = [];

        $stmt = $this->db->prepare("SELECT CourseName FROM Courses WHERE CourseNum=:courseNum");
        foreach($courseList as $courseNum) {
            $courseNum = trim($courseNum);
            if(substr($courseNum, -1) == 'C'){
                $coReq = " (Can be taken at same time)";
                $courseNum = substr($courseNum, 0, -1);
            }else{
                $coReq = "";
            }

            //echo $courseNum;

            $stmt->execute(['courseNum' => $courseNum]);

            while($row = $stmt->fetch()) {
                $courseNamesList[] = ['courseName' => $row['CourseName'].$coReq, 'isComplete' => self::courseIsComplete($courseNum)];
            }
        }

        return $courseNamesList;
    }

    private function courseIsComplete($courseNum) {

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM CompletedCourses WHERE CourseNum=:CourseNum AND GoogleId=:userId");
        $stmt->execute([
            'CourseNum' => $courseNum,
            'userId' => $_SESSION['userId']
        ]);

        return $stmt->fetchColumn() == 1;

    }
}

?>
