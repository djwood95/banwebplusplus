
DELIMITER //

CREATE PROCEDURE InsertUser(InGoogleId VARCHAR(500), InEmail VARCHAR(300))
  BEGIN
	IF (SELECT GoogleId FROM Users WHERE GoogleId = InGoogleID IS NOT NULL) THEN 
		INSERT INTO Users(GoogleId, Email) VALUES (InGoogleId, InEmail);
        END IF;
  END//
DELIMITER ;

DELIMITER //  
  CREATE PROCEDURE InsertStudentSchedule(InGoogleId VARCHAR(500), InSemester VARCHAR(100), InScheduleYear VARCHAR(100), InCRN VARCHAR(500))
  BEGIN
		INSERT INTO StudentSchedule(GoogleId, Semester, ScheduleYear, CRN) VALUES (InGoogleId, InSemester, InScheduleYear, InCRN);

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
  CREATE PROCEDURE DeleteSchedule(InScheduleId INT)
  BEGIN
		DELETE FROM StudentSchedule WHERE ScheduleId = InScheduleId;
  END//
DELIMITER ;

DELIMITER //  
  CREATE PROCEDURE DeleteScheduleCRN(InGoogleId VARCHAR(500), InScheduleId INT, InCRN INT)
  BEGIN
		DELETE FROM StudentSchedule WHERE ScheduleId = InScheduleId AND GoogleId = InGoogleID AND CRN = InCRN;
  END//
DELIMITER ;
/*
DELIMITER //  
  CREATE PROCEDURE GetCourse(InCourseNum VARCHAR(100))
  BEGIN
		SELECT * FROM Courses WHERE CourseNum = InCourseNum;
  END//
DELIMITER ;

DELIMITER //  
  CREATE PROCEDURE GetStudentSchedules(InGoogleId VARCHAR(500))
  BEGIN
		SELECT ScheduleId FROM StudentSchedule WHERE GoogleId = InGoogleId;
  END//
DELIMITER ;

DELIMITER //  
  CREATE PROCEDURE GetSchedule(InScheduleId INT)
  BEGIN
		SELECT * FROM StudentSchedule WHERE ScheduleId = InScheduleId;
  END//
DELIMITER ;

DELIMITER //  
  CREATE PROCEDURE GetUser(InGoogleId VARCHAR(500))
  BEGIN
		SELECT * FROM Users WHERE GoogleId = InGoogleId;
  END//
DELIMITER ;


DELIMITER //  
  CREATE PROCEDURE GetSectionInfo(InCRN INT)
  BEGIN
		SELECT * FROM Sections WHERE CRN = InCRN;
  END//
DELIMITER ;

DELIMITER //  
  CREATE PROCEDURE GetSections(InCourseNum VARCHAR(100))
  BEGIN
		SELECT CRN FROM Sections WHERE CourseNum = InCourseNum;
  END//
DELIMITER ;
*/