CREATE TABLE IF NOT EXISTS Users(
  GoogleId VARCHAR(500) NOT NULL,
  Email VARCHAR(300) NOT NULL
);
CREATE TABLE IF NOT EXISTS Courses(
  CourseNum VARCHAR(100),
  CourseName VARCHAR(300),
  Description VARCHAR(5000),
  SemestersOffered VARCHAR(100),
  Credits FLOAT(3,1),
  LectureCredits INT(11),
  RecitationCredits INT(11),
  LabCredits INT(11),
  Prereq VARCHAR(5000),
  Coreq VARCHAR(5000)
);
CREATE TABLE IF NOT EXISTS Sections(
  CourseNum VARCHAR(300),
  CRN INTEGER,
  SectionNum VARCHAR(100),
  Type VARCHAR(100),
  Days VARCHAR(100),
  SectionTime VARCHAR(300),
  Location VARCHAR(300),
  SectionActual VARCHAR(300),
  Capacity INTEGER,
  SlotsRemaining INTEGER,
  Instructor VARCHAR(150),
  Dates VARCHAR(100),
  Year INT(11),
  Fee VARCHAR(100)
);
CREATE TABLE IF NOT EXISTS StudentSchedule(
  ScheduleName VARCHAR(100),
  GoogleId VARCHAR(300) NOT NULL,
  Semester VARCHAR(100),
  ScheduleYear VARCHAR(100),
  CRN VARCHAR(500)
);	