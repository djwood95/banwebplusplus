
DELIMITER //

CREATE PROCEDURE InsertUser(InGoogleId VARCHAR(500), InEmail VARCHAR(300))
BEGIN
	IF NOT EXISTS (SELECT GoogleId FROM Users WHERE GoogleId = InGoogleID)	THEN 
	INSERT INTO Users(GoogleId, Email) VALUES (InGoogleId, InEmail);
   END IF;
 END//
DELIMITER ;

DELIMITER //  
  CREATE PROCEDURE InsertStudentSchedule(InScheduleName VARCHAR(100), InGoogleId VARCHAR(500), InSemester VARCHAR(100), InScheduleYear VARCHAR(100), InCRN VARCHAR(500))
  BEGIN
		INSERT INTO StudentSchedule(ScheduleName, GoogleId, Semester, ScheduleYear, CRN) VALUES (InScheduleName, InGoogleId, InSemester, InScheduleYear, InCRN);

  END//
DELIMITER ;

DELIMITER //  
  CREATE PROCEDURE DeleteUser(InGoogleId VARCHAR(500))
  BEGIN
		DELETE FROM User WHERE GoogleId = InGoogleId;
  END//
DELIMITER ;

DELIMITER //  
  CREATE PROCEDURE DeleteCourse(InCourseNum VARCHAR(100))
  BEGIN
		DELETE FROM Courses WHERE CourseNum = InCourseNum;
        DELETE FROM Sections WHERE CourseNum = InCourseNum;
  END//
DELIMITER ;

DELIMITER //  
  CREATE PROCEDURE DeleteSection(InCRN INT)
  BEGIN
		DELETE FROM Sections WHERE CRN = InCRN;
  END//
DELIMITER ;

DELIMITER //  
  CREATE PROCEDURE DeleteSchedule(InScheduleName VARCHAR(100))
  BEGIN
		DELETE FROM StudentSchedule WHERE ScheduleName= InScheduleName;
  END//
DELIMITER ;

DELIMITER //  
  CREATE PROCEDURE DeleteScheduleCRN(InGoogleId VARCHAR(500), InScheduleName VARCHAR(100), InCRN INT)
  BEGIN
		DELETE FROM StudentSchedule WHERE ScheduleName = InScheduleName AND GoogleId = InGoogleID AND CRN = InCRN;
  END//
DELIMITER ;