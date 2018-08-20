using MySql.Data.MySqlClient;
using System;
using System.Collections.Generic;

namespace BanwebScraperReboot
{
    internal class Course
    {
        public string Num { get; set; }
        public string Name { get; set; }
        public string Description { get; set; }
        public string Credits { get; set; }
        public string Lec { get; set; }
        public string Rec { get; set; }
        public string Lab { get; set; }
        public List<string> Offered { get; set; }
        public string Restrictions { get; set; }
        public string PreRequisites { get; set; }
        public string CoRequisites { get; set; }

        public MySqlCommand GetInsertCommand()
        {
            MySqlCommand command = new MySqlCommand("INSERT INTO Courses " +
                "(CourseNum,CourseName,Description,SemestersOffered,Credits,LectureCredits,RecitationCredits,LabCredits,Restrictions,Prereq,Coreq) " +
                "VALUES (@Crse,@Title,@Descr,@Sem,@Cred,@Lec,@Rec,@Lab,@Rest,@Prereqs,@Coreqs)");
            AddParameters(command);
            return command;
        }
        public MySqlCommand GetUpdateCommand()
        {
            MySqlCommand command = new MySqlCommand("UPDATE Courses " +
                "SET CourseName=@Title, Description=@Descr, SemestersOffered=@Sem, " +
                "Credits=@Cred, LectureCredits=@Lec, RecitationCredits=@Rec, LabCredits=@Lab, " +
                "Restrictions=@Rest, Prereq=@Prereqs, Coreq=@Coreqs WHERE CourseNum=@Crse");
            AddParameters(command);
            return command;
        }

        private void AddParameters(MySqlCommand command)
        {
            command.Parameters.AddWithValue("@Crse", Num);
            command.Parameters.AddWithValue("@Title", Name);
            command.Parameters.AddWithValue("@Descr", Description);
            command.Parameters.AddWithValue("@Cred", Credits);
            command.Parameters.AddWithValue("@Lec", string.IsNullOrEmpty(Lec) ? "0" : Lec);
            command.Parameters.AddWithValue("@Rec", string.IsNullOrEmpty(Rec) ? "0" : Rec);
            command.Parameters.AddWithValue("@Lab", string.IsNullOrEmpty(Lab) ? "0" : Lab);
            command.Parameters.AddWithValue("@Sem", string.Join(", ", Offered));
            command.Parameters.AddWithValue("@Rest", Restrictions);
            command.Parameters.AddWithValue("@Prereqs", PreRequisites);
            command.Parameters.AddWithValue("@Coreqs", CoRequisites);
        }
    }

    internal class Section
    {
        public string Crn { get; set; }
        public string Subject { get; set; }
        public string CourseNum { get; set; }
        public string SectionNum { get; set; }
        public string Campus { get; set; }
        public string Credits { get; set; }
        public string Title { get; set; }
        public string Days { get; set; }
        public string Time { get; set; }
        public string Cap { get; set; }
        public string Act { get; set; }
        public string Rem { get; set; }
        public string Instructor { get; set; }
        public string Dates { get; set; }
        public string Location { get; set; }
        public string Fee { get; set; }

        public bool IsOnline { get; set; }
        public string Year { get; set; }
        public string Semester { get; set; }

        internal void Add(Section s)
        {
            Campus += NotEqualOrWhiteSpace(Campus, s.Campus) ? $"|{s.Campus}" : "";
            Days += NotEqualOrWhiteSpace(Days, s.Days) ? $"|{s.Days}" : "";
            Time += NotEqualOrWhiteSpace(Time, s.Time) ? $"|{s.Time}" : "";
            Instructor += NotEqualOrWhiteSpace(Instructor, s.Instructor) ? $"|{s.Instructor}" : "";
            Dates += NotEqualOrWhiteSpace(Dates, s.Dates) ? $"|{s.Dates}" : "";
            Location += NotEqualOrWhiteSpace(Location, s.Location) ? $"|{s.Location}" : "";
            Fee += NotEqualOrWhiteSpace(Fee, s.Fee) ? $"|{s.Fee}" : "";
        }
        private bool NotEqualOrWhiteSpace(string x, string y) => x != y && !string.IsNullOrWhiteSpace(y);

        public MySqlCommand GetInsertCommand()
        {
            MySqlCommand command = new MySqlCommand("INSERT INTO Sections " +
                "(CourseNum,CRN,SectionNum,Days,SectionTime,Location,SectionActual,Capacity,SlotsRemaining,Instructor,Dates,Fee,Year,Semester,Online,Credits,Type) " +
                "VALUES (CONCAT(@Subj,' ',@Crse),@CRN,@Sec,@Days,@Time,@Loc,@Act,@Cap,@Rem,@Inst,@Dates,@Fee,@Yr,@Sem,@Ol,@Cred,@Cmp)");
            AddParameters(command);
            return command;
        }
        public MySqlCommand GetUpdateCommand()
        {
            MySqlCommand command = new MySqlCommand("UPDATE Sections " +
                "SET CourseNum=CONCAT(@Subj,' ',@Crse), SectionNum=@Sec, Days=@Days, SectionTime=@Time, " +
                "Location=@Loc, SectionActual=@Act, Capacity=@Cap, SlotsRemaining=@Rem, Instructor=@Inst, " +
                "Dates=@Dates, Fee=@Fee, Online=@Ol, Credits=@Cred, Type=@Cmp, LastModified=@Mod " +
                "WHERE CRN=@CRN AND Year=@Yr AND Semester=@Sem");
            AddParameters(command);
            return command;
        }

        private void AddParameters(MySqlCommand command)
        {
            command.Parameters.AddWithValue("@CRN", Crn);
            command.Parameters.AddWithValue("@Subj", Subject);
            command.Parameters.AddWithValue("@Crse", CourseNum);
            command.Parameters.AddWithValue("@Sec", SectionNum);
            command.Parameters.AddWithValue("@Cmp", Campus);
            command.Parameters.AddWithValue("@Cred", Credits);
            command.Parameters.AddWithValue("@Title", Title);
            command.Parameters.AddWithValue("@Days", Days);
            command.Parameters.AddWithValue("@Time", Time);
            command.Parameters.AddWithValue("@Cap", Cap);
            command.Parameters.AddWithValue("@Act", Act);
            command.Parameters.AddWithValue("@Rem", Rem);
            command.Parameters.AddWithValue("@Inst", Instructor);
            command.Parameters.AddWithValue("@Dates", Dates);
            command.Parameters.AddWithValue("@Loc", Location);
            command.Parameters.AddWithValue("@Fee", Fee);
            command.Parameters.AddWithValue("@Ol", IsOnline);
            command.Parameters.AddWithValue("@Mod", DateTime.Now.ToString("yyyy-MM-dd hh:mm:ss"));
            command.Parameters.AddWithValue("@Yr", Year);
            command.Parameters.AddWithValue("@Sem", Semester);
        }
    }
}
