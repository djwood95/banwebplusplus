using System;
using System.Diagnostics;
using System.Threading;

namespace BanwebScraper
{
    internal static class Program
    {
        private static void Main()
        {
            var stopwatch = new Stopwatch();
            var scraper = new Scraper();
            while (true)
            {
                try
                {
                    stopwatch.Restart();
                    scraper.Run();
                    var time = 60000 - (int) stopwatch.ElapsedMilliseconds;
                    Thread.Sleep(time > 0 ? time : 0);
                }
                catch (Exception e)
                {
                    Console.WriteLine($"\n--{e}");
                }
            }
        }
    }
}