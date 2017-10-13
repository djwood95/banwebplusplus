using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using HtmlAgilityPack;


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
    }
}