using MySql.Data.MySqlClient;
using System.Collections.Generic;
using System.Data;
using System.Linq;

namespace BanwebScraperReboot
{
    internal static class Commands
    {
        private static readonly string connectionString = $"server=159.203.102.52;port=3306;database=banwebpp;user id=dbuser;password={Secret.Password};SSLMode=None";

        public static Dictionary<string, DataRow> GetOldCourses()
        {
            using (MySqlConnection connection = new MySqlConnection(connectionString))
            using (MySqlCommand command = new MySqlCommand($"SELECT * FROM Courses", connection))
            {
                connection.Open();
                DataTable table = new DataTable();
                table.Load(command.ExecuteReader());
                Dictionary<string, DataRow> dictionary = new Dictionary<string, DataRow>();
                foreach (DataRow row in table.Rows) dictionary.Add(row["CourseNum"].ToString(), row);
                return dictionary;
            }
        }
        public static Dictionary<string, DataRow> GetOldSections(string semester, string year)
        {
            using (MySqlConnection connection = new MySqlConnection(connectionString))
            using (MySqlCommand command = new MySqlCommand($"SELECT * FROM Sections WHERE Year = '{year}' AND SEMESTER = '{semester}'", connection))
            {
                connection.Open();
                DataTable table = new DataTable();
                table.Load(command.ExecuteReader());
                Dictionary<string, DataRow> dictionary = new Dictionary<string, DataRow>();
                foreach (DataRow row in table.Rows) dictionary.Add(row["CRN"].ToString(), row);
                return dictionary;
            }
        }
        public static DataTable GetEmailsToSend()
        {
            using (MySqlConnection connection = new MySqlConnection(connectionString))
            using (MySqlCommand command = new MySqlCommand($"SELECT * FROM EmailAlerts WHERE Sent = 0", connection))
            {
                connection.Open();
                DataTable table = new DataTable();
                table.Load(command.ExecuteReader());
                return table;
            }
        }
        public static DataTable GetEmailData()
        {
            using (MySqlConnection connection = new MySqlConnection(connectionString))
            using (MySqlCommand command = new MySqlCommand($"SELECT CourseName, Sections.* FROM `Sections` LEFT JOIN Courses ON Sections.CourseNum = Courses.CourseNum", connection))
            {
                connection.Open();
                DataTable table = new DataTable();
                table.Load(command.ExecuteReader());
                return table;
            }
        }
        public static void UpdateSentEmails(List<long> ids)
        {
            IssueCommands(ids.Select(x => new MySqlCommand($"UPDATE EmailAlerts SET Sent = 1 WHERE Id = {x}")));
        }

        public static void IssueCommands(IEnumerable<MySqlCommand> commands)
        {
            using (MySqlConnection connection = new MySqlConnection(connectionString))
            {
                connection.Open();
                using (MySqlTransaction transaction = connection.BeginTransaction())
                {
                    foreach (MySqlCommand command in commands)
                    {
                        command.Connection = connection;
                        command.Transaction = transaction;
                        command.ExecuteNonQuery();
                    }
                }
            }
        }
    }
}
