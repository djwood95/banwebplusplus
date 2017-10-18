using System;
using System.Collections.Generic;
using System.Data;
using System.Diagnostics;
using System.Text.RegularExpressions;
using System.Threading.Tasks;
using HtmlAgilityPack;
using MySql.Data.MySqlClient;

namespace BanwebScraper
{
    internal class Scraper
    {
        private readonly string _connectionString, _courseInfoFilepath, _sectionInfoFilepath;
        private readonly string[] _coursePushCommands, _sectionPushCommands;
        private readonly List<List<object>> _courseParameters, _sectionParameters;
        private readonly HashSet<string> _sectionList, _courseList;

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
                "INSERT INTO Courses (CourseNum,CourseName,Description,SemestersOffered,Credits,LectureCredits,RecitationCredits,LabCredits,Restrictions,Prereq,Coreq) VALUES(@Crse,@Title,@Descr,@Sem,@Cred,@Lec,@Rec,@Lab,@Rest,@Prereqs,@Coreqs)",
                "UPDATE Courses SET CourseName=@Title, Description=@Descr, SemestersOffered=@Sem, Credits=@Cred, LectureCredits=@Lec, RecitationCredits=@Rec, LabCredits=@Lab, Restrictions=@Rest, Prereq=@Preqeqs, Coreq=@Coreqs WHERE CourseNum=@Crse"
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
                new List<object> {"@Cred", MySqlDbType.VarChar, 64},
                new List<object> {"@Lec", MySqlDbType.Int32},
                new List<object> {"@Rec", MySqlDbType.Int32},
                new List<object> {"@Lab", MySqlDbType.Int32},
                new List<object> {"@Sem", MySqlDbType.VarChar, 32},
                new List<object> {"@Rest", MySqlDbType.VarChar, 255},
                new List<object> {"@Prereqs", MySqlDbType.VarChar, 255},
                new List<object> {"@Coreqs", MySqlDbType.VarChar, 255}
            };
        }
        public void Run()
        {
            // wait time reader, consider using:
            // https://stackoverflow.com/a/18342182

            var sw = new Stopwatch();
            while (true)
            {
                sw.Start();
                GetCourseInfo(); // runs every day
                for (var i = 0; i < 24; i++)
                {
                    GetSectionInfo(); // runs every hour
                    sw.Stop();
                    Task.Delay(3600000 - (int) sw.ElapsedMilliseconds).Wait();
                    sw.Restart();
                }
            }
        }

        private void GetSectionInfo()
        {
            Console.Write($"[{DateTime.Now:s}]  -  Section Info Running... ");
            var doc = GetFile(_sectionInfoFilepath);
            var resultSet = ParseSections(doc);
            Push(resultSet, _sectionPushCommands, _sectionParameters, _sectionList);
            Console.WriteLine("Done");
        }
        private void GetCourseInfo()
        {
            Console.Write($"[{DateTime.Now:s}]  -  Course Info Running.... ");
            var doc = GetFile(_courseInfoFilepath);
            var resultSet = ParseCourses(doc);
            Push(resultSet, _coursePushCommands, _courseParameters, _courseList);
            Console.WriteLine("Done");
        }

        private static HtmlDocument GetFile(string expectedFilepath)
        {
            HtmlDocument doc = null;
            try
            {
                doc = new HtmlDocument();
                doc.Load(expectedFilepath);
            }
            catch (Exception e)
            {
                Console.WriteLine("\n" + e);
            }

            return doc;
        }
        private static IEnumerable<List<string>> ParseSections(HtmlDocument doc)
        {
            var resultSet = new List<List<string>>();
            var table = doc.DocumentNode.SelectNodes("//table")[3];
            foreach (var row in table.SelectNodes("tr"))
            {
                try
                {
                    if (row.SelectNodes("td") == null) continue;
                    var resultRow = new List<string>();

                    var cells = row.SelectNodes("td");
                    if (cells[0].InnerText == "&nbsp;")
                    {
                        for (var i = 0; i < cells.Count; i++)
                            resultSet[resultSet.Count - 1][i] += resultSet[resultSet.Count - 1][i] != cells[i].InnerText && cells[i].InnerText != "&nbsp;" ? "|" + cells[i].InnerText : "";
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
                catch (Exception e)
                {
                    Console.WriteLine("\n" + e);
                }
            }
            return resultSet;
        }
        private static IEnumerable<List<string>> ParseCourses(HtmlDocument doc)
        {
            var resultString = "";
            var resultRow = new List<string>();
            var resultSet = new List<List<string>>();

            var nodes = doc.GetElementbyId("content").ChildNodes;
            for (var i = 0; i < nodes.Count; i++)
            {
                if (i <= 10) continue;
                switch (nodes[i].Name)
                {
                    case "br" when nodes[i - 1].Name == "br":
                        resultRow.RemoveAt(resultRow.Count - 1);
                        resultSet.Add(resultRow);
                        resultRow = new List<string>();
                        break;
                    case "br":
                        resultRow.Add(resultString.Trim('\n',' '));
                        resultString = string.Empty;
                        break;
                    default:
                        resultString += nodes[i].InnerText;
                        break;
                }
            }

            NormalizeCourses(resultSet);
            return resultSet;
        }
        private void Push(IEnumerable<List<string>> resultSet, IReadOnlyList<string> commands, IEnumerable<List<object>> parameterSet, HashSet<string> set)
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
                            insertCommand.Parameters.Add((string)parameter[0], (MySqlDbType)parameter[1]);
                            updateCommand.Parameters.Add((string)parameter[0], (MySqlDbType)parameter[1]);
                        }
                        else
                        {
                            insertCommand.Parameters.Add((string)parameter[0], (MySqlDbType)parameter[1], (int)parameter[2]);
                            updateCommand.Parameters.Add((string)parameter[0], (MySqlDbType)parameter[1], (int)parameter[2]);
                        }
                    insertCommand.Prepare();
                    updateCommand.Prepare();

                    foreach (var row in resultSet)
                    {
                        try
                        {
                            var command = set.Contains(ScrubHtml(row[0])) ? updateCommand : insertCommand;
                            for (var i = 0; i < command.Parameters.Count; i++)
                            {
                                switch (command.Parameters[i].MySqlDbType)
                                {
                                    case MySqlDbType.Int32:
                                        command.Parameters[i].Value = ScrubHtmlInt(row[i]); break;
                                    case MySqlDbType.VarChar:
                                        command.Parameters[i].Value = ScrubHtml(row[i]); break;
                                }
                            }
                            command.ExecuteNonQuery();
                        }
                        catch (Exception e)
                        {
                            Console.WriteLine("\n" + e);
                        }
                    }
                    transaction.Commit();
                }
            }
        }

        private static void NormalizeCourses(IEnumerable<List<string>> courses)
        {
            foreach (var course in courses)
            {
                var sa = course[0].Split('-');
                course[0] = sa[0].Trim('\n', ' ');
                course.Insert(1, sa[1].Trim('\n', ' '));
                course[3] = course[3].Substring(9).Trim('\n', ' ');

                for (var i = 4; i <= 8; i++)
                    if (i < course.Count) HandleOptional(course, i);
                    else course.Insert(i, null);

                sa = course[4]?.Split('-') ?? new string[] {null, null, null};
                course[4] = sa[0];
                course.Insert(5, sa[1]);
                course.Insert(6, sa[2]);

                if (course[9] != null) course[9] = course[9].Replace(" or ", "|").Replace(" and ", "&");
                if (course[10] != null) course[10] = course[10].Replace(" or ", "|").Replace(" and ", "&");
            }
        }
        private static void HandleOptional(IList<string> l, int i)
        {
            string s;
            if (l[i].StartsWith("Lec-Rec-Lab:") && i >= 4)
                s = l[i].Substring(12, l[i].Length - 13).Trim(' ','(',')','\n');
            else if (l[i].StartsWith("Semesters Offered:") && i >= 5)
                s = l[i].Substring(18).Trim(' ', '\n');
            else if (l[i].StartsWith("Restrictions:") && i >= 6)
                s = l[i].Substring(13).Trim(' ', '\n');
            else if (l[i].StartsWith("Pre-Requisite(s):") && i >= 7)
                s = l[i].Substring(17).Trim(' ', '\n');
            else if (l[i].StartsWith("Co-Requisite(s):") && i >= 7)
                s = l[i].Substring(16).Trim(' ', '\n');
            else s = string.Empty;

            if (s == string.Empty) l.Insert(i, null);
            else l[i] = s;
        }
        private static string ScrubHtml(string htmlstring)
        {
            if (htmlstring == null) return null;
            var s = htmlstring;
            var s1 = Regex.Replace(s, @"<[^>]+>|&nbsp;", "").Trim();
            var s2 = Regex.Replace(s1, @"\s{2,}", " ");
            return s2 == string.Empty ? null : s2;
        }
        private static int? ScrubHtmlInt(string htmlstring)
        {
            var s = ScrubHtml(htmlstring);
            return s == null ? default(int?) : int.Parse(s);
        }
        private HashSet<string> RunQuery(string query)
        {
            var dt = new DataTable();
            var result = new HashSet<string>();

            try
            {
                using (var connection = new MySqlConnection(_connectionString))
                using (var command = new MySqlCommand(query, connection))
                {
                    connection.Open();
                    dt.Load(command.ExecuteReader());
                    foreach (DataRow dr in dt.Rows)
                        result.Add(dr[0].ToString());
                }
            }
            catch (Exception e)
            {
                Console.WriteLine("\n" + e);
            }

            return result;
        }
    }
}