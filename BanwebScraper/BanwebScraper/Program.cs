using System.Threading;

namespace BanwebScraperReboot
{
    class Program
    {
        static void Main(string[] args)
        {
            Scraper s = new Scraper();
            while (true)
            {
                s.Run();
                Thread.Sleep(10000);
            }
        }
    }
}
