using System;
using System.Collections.Generic;
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
        private readonly string _connectionString, _courseInfoFilepath, _sectionInfoFilepath, _coursePushCommand, _sectionPushCommand;
        private readonly List<List<object>> _courseParameters, _sectionParameters;

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

            _courseInfoFilepath = courseInfoFilepath;
            _sectionInfoFilepath = sectionInfoFilepath;

            _sectionPushCommand = @"CALL SectionPush(@CRN,@Subj,@Crse,@Sec,@Cmp,@Cred,@Title,@Days,@Time,@Cap,@Act,@Rem,@Instructor,@Date,@Location,@Fee)";
            _coursePushCommand = @"CALL CoursePush(@Crse,@Title,@Description,@Cred,@Lec,@Rec,@Lab,@Sem,@Prereqs,@Coreqs)";

            _sectionParameters = new List<List<object>>
            {
                new List<object> {"@CRN", MySqlDbType.Int32},
                new List<object> {"@Subj", MySqlDbType.VarChar, 3},
                new List<object> {"@Crse", MySqlDbType.Int32},
                new List<object> {"@Sec", MySqlDbType.VarChar, 3},
                new List<object> {"@Cmp", MySqlDbType.VarChar, 3},
                new List<object> {"@Cred", MySqlDbType.VarChar, 16},
                new List<object> {"@Title", MySqlDbType.VarChar, 255},
                new List<object> {"@Days", MySqlDbType.VarChar, 5},
                new List<object> {"@Time", MySqlDbType.VarChar, 32},
                new List<object> {"@Cap", MySqlDbType.Int32},
                new List<object> {"@Act", MySqlDbType.Int32},
                new List<object> {"@Rem", MySqlDbType.Int32},
                new List<object> {"@Instructor", MySqlDbType.VarChar, 128},
                new List<object> {"@Date", MySqlDbType.VarChar, 16},
                new List<object> {"@Location", MySqlDbType.VarChar, 8},
                new List<object> {"@Fee", MySqlDbType.VarChar, 32},
            };
            _courseParameters = new List<List<object>>
            {
                new List<object> {"@Crse", MySqlDbType.VarChar, 8},
                new List<object> {"@Title", MySqlDbType.VarChar, 255},
                new List<object> {"@Description", MySqlDbType.VarChar, 5000},
                new List<object> {"@Cred", MySqlDbType.VarChar, 16},
                new List<object> {"@Lec", MySqlDbType.Int32},
                new List<object> {"@Rec", MySqlDbType.Int32},
                new List<object> {"@Lab", MySqlDbType.Int32},
                new List<object> {"@Sem", MySqlDbType.VarChar, 32},
                new List<object> {"@Prereqs", MySqlDbType.VarChar, 255},
                new List<object> {"@Coreqs", MySqlDbType.VarChar, 255}
            };
        }

        public async void Run()
        {
            // wait time reader, consider using:
            // https://stackoverflow.com/a/18342182

            while (true)
            {
                //GetClassInfo(); // runs every day
                for (var i = 0; i < 24; i++)
                {
                    GetSectionInfo(); // runs every hour
                    await Task.Delay(3600000);
                }
            }
        }

        private void GetSectionInfo()
        {
            var doc = GetFile(_sectionInfoFilepath);
            var resultSet = ParseHtml(doc, 3);
            Push(resultSet, _sectionPushCommand, _sectionParameters);
        }

        private void GetClassInfo()
        {
            var doc = GetFile(_courseInfoFilepath);
            var resultSet = ParseHtml(doc, 1);
            Push(resultSet, _coursePushCommand, _courseParameters);

        }

        private static HtmlDocument GetFile(string expectedFilepath)
        {
            HtmlDocument doc = null;
            doc = new HtmlDocument();
            doc.Load(expectedFilepath);

            return doc;
        }

        private static IEnumerable<List<object>> ParseHtml(HtmlDocument doc, int tableIndex)
        {
            var resultSet = new List<List<object>>();
            var table = doc.DocumentNode.SelectNodes("//table")[tableIndex];
            foreach (var row in table.SelectNodes("tr"))
            {
                if (row.SelectNodes("td") == null) continue;
                var resultRow = new List<object>();
                foreach (var cell in row.SelectNodes("td"))
                {
                    resultRow.Add(cell.InnerText);
                }
                if (resultRow.Count > 0) resultSet.Add(resultRow);
            }
            return resultSet;
        }
        private void Push(IEnumerable<List<object>> resultSet, string commandText, IEnumerable<List<object>> parameterSet)
        {
            using (var connection = new MySqlConnection(_connectionString))
            {
                connection.Open();
                using (var command = new MySqlCommand(commandText, connection))
                {
                    foreach (var parameter in parameterSet)
                        if (parameter.Count == 2)
                            command.Parameters.Add((string) parameter[0], (MySqlDbType) parameter[1]);
                        else
                            command.Parameters.Add((string) parameter[0], (MySqlDbType) parameter[1], (int) parameter[2]);
                    command.Prepare();

                    foreach (var row in resultSet)
                    {
                        for (var i = 0; i < command.Parameters.Count; i++)
                        {
                            switch (command.Parameters[i].MySqlDbType)
                            {
                                case MySqlDbType.Int32:
                                    command.Parameters[i].Value = int.Parse(ScrubHtml(row[i]));
                                    break;
                                case MySqlDbType.VarChar:
                                    command.Parameters[i].Value = ScrubHtml(row[i]);
                                    break;
                                case MySqlDbType.Float:
                                    command.Parameters[i].Value = float.Parse(ScrubHtml(row[i]));
                                    break;
                            }
                        }
                        command.ExecuteNonQuery();
                    }
                }
            }
        }

        private string ScrubHtml(object htmlstring)
        {
            var s = (string) htmlstring;
            var s1 = Regex.Replace(s, @"<[^>]+>|&nbsp;", "").Trim();
            var s2 = Regex.Replace(s1, @"\s{2,}", " ");
            return s2;
        }
    }
}