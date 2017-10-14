using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Threading.Tasks;
using HtmlAgilityPack;
using MySql.Data.MySqlClient;

namespace BanwebScraper
{
    class Scraper
    {
        private readonly string _connectionString;

        public Scraper()
        {
            _connectionString = new MySqlConnectionStringBuilder
            {
                Server = "159.203.102.52",
                Port = 3306,
                Database = "banwebpp",
                UserID = "dbuser",
                Password = "BanWeb++"
            }.ConnectionString;
        }

        public async void Run()
        {
            // wait time reader, consider using:
            // https://stackoverflow.com/a/18342182

            while (true)
            {
                GetClassInfo(); // runs every day
                for (var i = 0; i < 24; i++)
                {
                    GetSectionInfo(); // runs every hour
                    await Task.Delay(3600000);
                }
            }
        }

        private void GetSectionInfo()
        {
            try
            {
                var doc = GetFile(string.Empty); // todo: get filepath here
                var resultSet = ParseHtml(doc, 3);
                Push(resultSet);
            }
            catch (Exception e)
            {
                Console.WriteLine(e);
            }
        }

        private void GetClassInfo()
        {
            throw new NotImplementedException();
        }

        private static HtmlDocument GetFile(string expectedFilepath)
        {
            HtmlDocument doc = null;
            try
            {
                doc = new HtmlDocument();
                doc.Load(expectedFilepath);
            }
            catch (IOException e)
            {
                Console.WriteLine(e);
            }
            return doc;
        }

        private static List<List<object>> ParseHtml(HtmlDocument doc, int tableIndex)
        {
            var table = doc.DocumentNode.SelectNodes("//table")[tableIndex];
            return table.SelectNodes("tr")
                .Select(row => row.SelectNodes("td").Select(cell => cell.InnerText).Cast<object>().ToList())
                .Where(resultRow => resultRow.Count != 0).ToList();
        }

        private void Push(IEnumerable<List<object>> resultSet)
        {
            const string preparedCommand =
                @"INSERT INTO classes
                (CRN,Subj,Crse,Sec,Cmp,Cred,Title,Days,Time,Cap,Act,Rem,Instructor,Date,Location,Fee)
                VALUES (@CRN,@Subj,@Crse,@Sec,@Cmp,@Cred,@Title,@Days,@Time,@Cap,@Act,@Rem,@Instructor,@Date,@Location,@Fee)";
            using (var connection = new MySqlConnection(_connectionString))
            {
                using (var command = new MySqlCommand(preparedCommand, connection))
                {
                    command.Parameters.Add("@CRN", MySqlDbType.Int32);
                    command.Parameters.Add("@Subj", MySqlDbType.VarChar, 3);
                    command.Parameters.Add("@Crse", MySqlDbType.Int32);
                    command.Parameters.Add("@Sec", MySqlDbType.VarChar, 2);
                    command.Parameters.Add("@Cmp", MySqlDbType.VarChar, 3);
                    command.Parameters.Add("@Cred", MySqlDbType.Float);
                    command.Parameters.Add("@Title", MySqlDbType.VarChar, 255);
                    command.Parameters.Add("@Days", MySqlDbType.VarChar, 5);
                    command.Parameters.Add("@Time", MySqlDbType.VarChar, 16);
                    command.Parameters.Add("@Cap", MySqlDbType.Int32);
                    command.Parameters.Add("@Act", MySqlDbType.Int32);
                    command.Parameters.Add("@Rem", MySqlDbType.Int32);
                    command.Parameters.Add("@Instructor", MySqlDbType.VarChar, 16);
                    command.Parameters.Add("@Date", MySqlDbType.VarChar, 16);
                    command.Parameters.Add("@Location", MySqlDbType.VarChar, 8);
                    command.Parameters.Add("@Fee", MySqlDbType.VarChar, 32);
                    command.Prepare();

                    foreach (var row in resultSet)
                    {
                        try
                        {
                            if (row.Count != 16) continue;
                            for (var i = 0; i < 16; i++)
                            {
                                command.Parameters[0].Value = (int) row[0];
                                command.Parameters[1].Value = (string) row[1];
                                command.Parameters[2].Value = (int) row[2];
                                command.Parameters[3].Value = (string) row[3];
                                command.Parameters[4].Value = (string) row[4];
                                command.Parameters[5].Value = (float) row[5];
                                command.Parameters[6].Value = (string) row[6];
                                command.Parameters[7].Value = (string) row[7];
                                command.Parameters[8].Value = (string) row[8];
                                command.Parameters[9].Value = (int) row[9];
                                command.Parameters[10].Value = (int) row[10];
                                command.Parameters[11].Value = (int) row[11];
                                command.Parameters[12].Value = (string) row[12];
                                command.Parameters[13].Value = (string) row[13];
                                command.Parameters[14].Value = (string) row[14];
                                command.Parameters[15].Value = (string) row[15];
                            }
                            command.ExecuteNonQuery();
                        }
                        catch (Exception e)
                        {
                            Console.WriteLine(e);
                        }
                    }
                }
            }
        }
    }
}