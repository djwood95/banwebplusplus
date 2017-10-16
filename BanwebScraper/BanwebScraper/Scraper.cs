using System;
using System.Collections.Generic;
using System.Data;
using System.IO;
using System.Linq;
using System.Runtime.Remoting.Channels;
using System.Text.RegularExpressions;
using System.Threading.Tasks;
using HtmlAgilityPack;
using MySql.Data.MySqlClient;

namespace BanwebScraper
{
    internal class Scraper
    {
        private readonly string _connectionString, _courseInfoFilepath, _sectionInfoFilepath, _coursePushCommand;
        private readonly string[] _coursePushCommands, _sectionPushCommands;
        private readonly List<List<object>> _courseParameters, _sectionParameters;
        private readonly HashSet<int> _sectionList, _courseList;

        public Scraper(string courseInfoFilepath, string sectionInfoFilepath)
        {
            _connectionString = new MySqlConnectionStringBuilder
            {
                Server = "159.203.102.52",
                Port = 3306,
                Database = "banwebpp",
                UserID = "dbuser",
                Password = "BanWeb++"
            }.ConnectionString;

            _sectionList = RunQuery("SELECT crn FROM Sections");
            _courseList = RunQuery("SELECT CourseNum FROM Courses");

            _courseInfoFilepath = courseInfoFilepath;
            _sectionInfoFilepath = sectionInfoFilepath;
            
            _coursePushCommands = new[]
            {
                "INSERT INTO Courses (CourseNum,Name,Description,SemestersOffered,Credits,LectureCredits,RecitationCredits,LabCredits,Prereq,Coreq) VALUES(@Crse,@Title,@Descr,@Sem,@Cred,@Lec,@Rec,@Lab,@Prereqs,@Coreqs)",
                "UPDATE Courses SET Name = @Title, Description = @Descr, SemestersOffered = @Sem, Credits = @Cred, LectureCredits = @Lec, RecitationCredits = @Rec, LabCredits = @Lab, Prereq = @Preqeqs, Coreq = @Coreqs WHERE CourseNum = @Crse"
            };
            _sectionPushCommands = new[]
            {
                "INSERT INTO Sections (CourseNum,CRN,SectionNum,Days,SectionTime,Location,SectionActual,Capacity,SlotsRemaining,Instructor,Dates,Fee) VALUES (CONCAT(@Subj,' ',@Crse),@CRN,@Sec,@Days,@Time,@Loc,@Act,@Cap,@Rem,@Inst,@Dates,@Fee)",
                "UPDATE Sections SET CourseNum=CONCAT(@Subj,' ',@Crse), SectionNum=@Sec, Days=@Days, SectionTime=@Time, Location=@Loc, SectionActual=@Act, Capacity=@Cap, SlotsRemaining=@Rem, Instructor=@Inst, Dates=@Dates, Fee=@Fee WHERE CRN=@CRN"
            };

            _sectionParameters = new List<List<object>>
            {
                new List<object> {"@CRN", MySqlDbType.Int32},
                new List<object> {"@Subj", MySqlDbType.VarChar, 6},
                new List<object> {"@Crse", MySqlDbType.Int32},
                new List<object> {"@Sec", MySqlDbType.VarChar, 3},
                new List<object> {"@Cmp", MySqlDbType.VarChar, 3},//
                new List<object> {"@Cred", MySqlDbType.VarChar, 16},//
                new List<object> {"@Title", MySqlDbType.VarChar, 255},//
                new List<object> {"@Days", MySqlDbType.VarChar, 10},
                new List<object> {"@Time", MySqlDbType.VarChar, 64},
                new List<object> {"@Cap", MySqlDbType.Int32},
                new List<object> {"@Act", MySqlDbType.Int32},
                new List<object> {"@Rem", MySqlDbType.Int32},
                new List<object> {"@Inst", MySqlDbType.VarChar, 128},
                new List<object> {"@Dates", MySqlDbType.VarChar, 64},
                new List<object> {"@Loc", MySqlDbType.VarChar, 8},
                new List<object> {"@Fee", MySqlDbType.VarChar, 255}
            };
            _courseParameters = new List<List<object>>
            {
                new List<object> {"@Crse", MySqlDbType.VarChar, 8},
                new List<object> {"@Title", MySqlDbType.VarChar, 255},
                new List<object> {"@Descr", MySqlDbType.VarChar, 5000},
                new List<object> {"@Cred", MySqlDbType.VarChar, 16},
                new List<object> {"@Lec", MySqlDbType.Int32},
                new List<object> {"@Rec", MySqlDbType.Int32},
                new List<object> {"@Lab", MySqlDbType.Int32},
                new List<object> {"@Sem", MySqlDbType.VarChar, 32},
                new List<object> {"@Prereqs", MySqlDbType.VarChar, 255},
                new List<object> {"@Coreqs", MySqlDbType.VarChar, 255}
            };
        }

        public void Run()
        {
            // wait time reader, consider using:
            // https://stackoverflow.com/a/18342182

            while (true)
            {
                //GetClassInfo(); // runs every day
                for (var i = 0; i < 24; i++)
                {
                    GetSectionInfo(); // runs every hour
                    Task.Delay(3600000).Wait();
                }
            }
        }

        private void GetSectionInfo()
        {
            var doc = GetFile(_sectionInfoFilepath);
            var resultSet = ParseSections(doc, 3);
            Push(resultSet, _sectionPushCommands, _sectionParameters);
        }
        private void GetClassInfo()
        {
            var doc = GetFile(_courseInfoFilepath);
            var resultSet = ParseCourses(doc);
            Push(resultSet, _coursePushCommands, _courseParameters);
        }

        private static HtmlDocument GetFile(string expectedFilepath)
        {
            HtmlDocument doc = null;
            doc = new HtmlDocument();
            doc.Load(expectedFilepath);

            return doc;
        }
        private static IEnumerable<List<object>> ParseSections(HtmlDocument doc, int tableIndex)
        {
            var resultSet = new List<List<object>>();
            var table = doc.DocumentNode.SelectNodes("//table")[tableIndex];
            foreach (var row in table.SelectNodes("tr"))
            {
                if (row.SelectNodes("td") == null) continue;
                var resultRow = new List<object>();

                var cells = row.SelectNodes("td");
                if (cells[0].InnerText == "&nbsp;")
                {
                    for (var i = 0; i < cells.Count; i++)
                        resultSet[resultSet.Count-1][i] += (string)resultSet[resultSet.Count-1][i] != cells[i].InnerText && cells[i].InnerText != "&nbsp;" ? "|" + cells[i].InnerText : "";
                    continue;
                }

                foreach (var cell in row.SelectNodes("td"))
                {
                    resultRow.Add(cell.InnerText);
                    for (var i = 1; i < int.Parse(cell.Attributes["colspan"]?.Value ?? "0"); i++)
                        resultRow.Add(cell.InnerText);
                }
                if (resultRow.Count > 0) resultSet.Add(resultRow);
            }
            return resultSet;
        }
        private static IEnumerable<List<object>> ParseCourses(HtmlDocument doc)
        {
            return new List<object>[0];
        }
        private void Push(IEnumerable<List<object>> resultSet, IReadOnlyList<string> commands, IEnumerable<List<object>> parameterSet)
        {
            using (var connection = new MySqlConnection(_connectionString))
            {
                connection.Open();
                using (var transaction = connection.BeginTransaction())
                using (var insertCommand = new MySqlCommand(commands[0], connection, transaction))
                using (var updateCommand = new MySqlCommand(commands[1], connection, transaction))
                {
                    foreach (var parameter in parameterSet)
                        if (parameter.Count == 2)
                        {
                            insertCommand.Parameters.Add((string) parameter[0], (MySqlDbType) parameter[1]);
                            updateCommand.Parameters.Add((string) parameter[0], (MySqlDbType) parameter[1]);
                        }
                        else
                        {
                            insertCommand.Parameters.Add((string) parameter[0], (MySqlDbType) parameter[1], (int) parameter[2]);
                            updateCommand.Parameters.Add((string) parameter[0], (MySqlDbType) parameter[1], (int) parameter[2]);
                        }
                    insertCommand.Prepare();
                    updateCommand.Prepare();

                    foreach (var row in resultSet)
                    {
                        try
                        {
                            var command = _sectionList.Contains(int.Parse(ScrubHtml(row[0]))) ? updateCommand : insertCommand;
                            for (var i = 0; i < command.Parameters.Count; i++)
                            {
                                switch (command.Parameters[i].MySqlDbType)
                                {
                                    case MySqlDbType.Int32:
                                        command.Parameters[i].Value = int.Parse(ScrubHtml(row[i])); break;
                                    case MySqlDbType.VarChar:
                                        command.Parameters[i].Value = ScrubHtml(row[i]); break;
                                }
                            }
                            command.ExecuteNonQuery();
                        }
                        catch (Exception e)
                        {
                            Console.WriteLine(e);
                        }
                    }
                    transaction.Commit();
                }
            }
        }
        private static string ScrubHtml(object htmlstring)
        {
            var s = (string) htmlstring;
            var s1 = Regex.Replace(s, @"<[^>]+>|&nbsp;", "").Trim();
            var s2 = Regex.Replace(s1, @"\s{2,}", " ");
            return s2;
        }
        private HashSet<int> RunQuery(string query)
        {
            var dt = new DataTable();
            var result = new HashSet<int>();

            using (var connection = new MySqlConnection(_connectionString))
            using (var command = new MySqlCommand(query, connection))
            {
                connection.Open();
                dt.Load(command.ExecuteReader());
                foreach (DataRow dr in dt.Rows)
                    result.Add((int) dr[0]);
            }

            return result;
        }
    }
}