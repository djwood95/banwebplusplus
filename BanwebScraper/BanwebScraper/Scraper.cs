using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using HtmlAgilityPack;
using System.Data;
using MySql.Data.MySqlClient;

namespace BanwebScraper
{
    class Scraper
    {
        private HtmlDocument doc;
        private string connectionString;

        public Scraper(string filepath)
        {
            doc = new HtmlDocument();
            doc.Load(filepath);
        }

        private static DataTable PushToDb(MySqlCommand command)
        {
            DataTable dt = new DataTable();
            using (var connection = new MySqlConnection())
            {
                try
                {
                    command.Connection = connection;
                    dt.Load(command.ExecuteReader());
                }
                catch (Exception e)
                {
                    Console.Write($"Exception Caught: {e}");
                }
            }
            return dt;
        }
        private static void PushToDb(IEnumerable<MySqlCommand> commands)
        {
            using (var connection = new MySqlConnection())
            {
                using (var transaction = connection.BeginTransaction())
                {
                    try
                    {
                        foreach (var command in commands)
                        {
                            command.Connection = connection;
                            command.Transaction = transaction;
                            command.ExecuteNonQuery();
                        }
                        transaction.Commit();
                    }
                    catch (Exception e)
                    {
                        Console.Write($"Exception Caught: {e}");
                        transaction.Rollback();
                    }
                }
            }
        }
    }
}