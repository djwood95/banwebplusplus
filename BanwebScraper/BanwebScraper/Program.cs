namespace BanwebScraper
{
    internal static class Program
    {
        private static void Main()
        {
            var scraper = new Scraper("D:\\Downloads\\course.html", "D:\\Downloads\\section.html");
            scraper.Run();
        }
    }
}