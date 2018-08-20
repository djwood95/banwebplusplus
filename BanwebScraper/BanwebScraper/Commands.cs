using MySql.Data.MySqlClient;
using System.Collections.Generic;
using System.Data;
using System.Linq;

namespace BanwebScraperReboot
{
    /// <summary>
    /// Various MySql commands and helpers to use on a database
    /// </summary>
    internal static class Commands
    {
        private static readonly string connectionString = $"server=159.203.102.52;port=3306;database=banwebpp;user id=dbuser;password={Secret.Password};SSLMode=None";

        /// <summary>
        /// Gets old course information
        /// </summary>
        /// <returns>A Dictionary full of old course information, organized by CourseNum</returns>
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
        /// <summary>
        /// Gets old section information
        /// </summary>
        /// <param name="semester">The semester to fetch information for</param>
        /// <param name="year">The year to fetch information for</param>
        /// <returns>A Dictionary full of old course information, organized by CRN</returns>
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

        /// <summary>
        /// Gets a table of emails that need to be sent
        /// </summary>
        /// <returns>A DataTable with information on emails that need to be sent</returns>
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
        /// <summary>
        /// Gets section data to be used in the email section of the program
        /// </summary>
        /// <returns>A DataTable with all section information</returns>
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
        /// <summary>
        /// Updates the database with sent email information
        /// </summary>
        /// <param name="ids">The IDs of the emails that were sent</param>
        public static void UpdateSentEmails(List<long> ids)
        {
            IssueCommands(ids.Select(x => new MySqlCommand($"UPDATE EmailAlerts SET Sent = 1 WHERE Id = {x}")));
        }

        /// <summary>
        /// Function for issuing a large number of MySql commands in a transaction
        /// </summary>
        /// <param name="commands">The commands to issue</param>
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
