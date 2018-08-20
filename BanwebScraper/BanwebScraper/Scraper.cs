using HtmlAgilityPack;
using MySql.Data.MySqlClient;
using System;
using System.Collections.Generic;
using System.Data;
using System.Linq;
using System.Net;
using System.Net.Mail;
using System.Text.RegularExpressions;
using System.Threading;

namespace BanwebScraperReboot
{
    /// <summary>
    /// Main class of the BanwebScraper program
    /// </summary>
    public class Scraper
    {
        private bool _firstRun = true;
        private readonly char[] _trimChars = { ' ', '\n' };
        private const string
            _sectionInfoUrl = "https://banwebplusplus.me/banwebFiles/",
            _refreshUrl = "https://banwebplusplus.me/public/updateCourseInfo.php",
            _courseInfoUrl = "https://www.banweb.mtu.edu/pls/owa/stu_ctg_utils.p_online_all_courses_ug";

        /// <summary>
        /// Runs the main tasks of the program, will reattempt a number of times on failure
        /// </summary>
        /// <param name="maxTries">The number of times to attempt a function</param>
        public void Run(int maxTries = 5)
        {
            for (int i = 0; i < maxTries && _firstRun && !RunCourses(); i++)
                Thread.Sleep(10000);
            for (int j = 0; j < maxTries && !RunSections(); j++)
                Thread.Sleep(10000);
            for (int k = 0; k < maxTries && !SendEmails(); k++)
                Thread.Sleep(10000);
            _firstRun = false;
        }

        #region Courses
        /// <summary>
        /// Runs the course scraper
        /// </summary>
        /// <returns>True if it sucessfully pushes to the database, false otherwise</returns>
        public bool RunCourses()
        {
            Console.WriteLine($"[{DateTime.Now:s}] - Running Course Scraper");

            HtmlDocument doc;
            HtmlWeb web = new HtmlWeb();
            try
            {
                doc = web.Load(_courseInfoUrl);
            }
            catch (Exception e)
            {
                Console.WriteLine($"{e.GetType()} encountered while getting course HTML\n{e}");
                GC.Collect();
                return false;
            }

            IEnumerable<Course> courseInfo;
            try
            {
                courseInfo = ParseCourses(doc);
            }
            catch (Exception e)
            {
                Console.WriteLine($"{e.GetType()} encountered while parsing course HTML\n{e}");
                GC.Collect();
                return false;
            }

            try
            {
                PushCourses(courseInfo, Commands.GetOldCourses());
            }
            catch (Exception e)
            {
                Console.WriteLine($"{e.GetType()} encountered while parsing courses to SQL\n{e}");
                GC.Collect();
                return false;
            }

            GC.Collect();
            Console.WriteLine($"[{DateTime.Now:s}] - Course Scraper Finished");
            return true;
        }

        /// <summary>
        /// Parses course information from HTML
        /// </summary>
        /// <param name="doc">An HtmlDocument with the information to parse</param>
        /// <returns>An IEnumerable of Course information</returns>
        private IEnumerable<Course> ParseCourses(HtmlDocument doc)
        {
            HtmlNode content = doc.GetElementbyId("content_body");
            content.RemoveChild(content.SelectSingleNode("ul")); // remove the list of navigation links
            List<HtmlNode> nodes = content.ChildNodes.ToList();

            List<Course> courses = new List<Course>();
            for (int i = 0; i < nodes.Count; i++)
            {
                switch (nodes[i].Name)
                {
                    case "h4":
                        var name = string.Join("-", nodes[i].InnerText.Split('-').Skip(1));
                        var list = nodes[i + 4].SelectNodes("li");
                        if(nodes[i].InnerText.Split('-')[0].Trim(_trimChars) == "ACC 4990")
                        {

                        }
                        courses.Add(new Course
                        {
                            Num = RemoveDoubleSpaces(nodes[i].InnerText.Split('-')[0].Trim(_trimChars)),
                            Name = RemoveDoubleSpaces(name.Trim(_trimChars)),
                            Description = RemoveDoubleSpaces(nodes[i + 2].InnerText.Trim(_trimChars)),
                            Credits = GetAttributeText(list, "credits:"),
                            Lec = TryGetArray(GetAttributeText(list, "lec-rec-lab:", new[] { '(', ')' }).Split('-'), 0),
                            Rec = TryGetArray(GetAttributeText(list, "lec-rec-lab:", new[] { '(', ')' }).Split('-'), 1),
                            Lab = TryGetArray(GetAttributeText(list, "lec-rec-lab:", new[] { '(', ')' }).Split('-'), 2),
                            Offered = GetAttributeText(list, "semesters offered:").Split(',').Select(x => x.Trim(_trimChars)).ToList(),
                            Restrictions = GetAttributeText(list, "restrictions:"),
                            PreRequisites = GetAttributeText(list, "pre-requisite(s):").Replace(" or ", "|").Replace(" and ", "&"),
                            CoRequisites = GetAttributeText(list, "co-requisite(s):").Replace(" or ", "|").Replace(" and ", "&")
                        });
                        break;
                    default: break;
                }
            }

            return courses;
        }
        /// <summary>
        /// Gets the text from an HtmlNode that may or may not be there based on the label from another node
        /// </summary>
        /// <param name="list">The list to fetch information from</param>
        /// <param name="name">The name of the label to search for</param>
        /// <param name="extraTrimChars">Optional parameter to add more trim characters</param>
        /// <returns>Either an empty string or the text in the specified HtmlNode</returns>
        private string GetAttributeText(HtmlNodeCollection list, string name, char[] extraTrimChars = null)
        {
            if (extraTrimChars == null) extraTrimChars = new char[] { };
            return RemoveDoubleSpaces(list
                .FirstOrDefault(x => x.ChildNodes[0].InnerText.Trim(_trimChars.Concat(extraTrimChars).ToArray()).ToLower() == name)
                ?.ChildNodes[1].InnerText.Trim(_trimChars.Concat(extraTrimChars).ToArray()) ?? "");
        }
        /// <summary>
        /// Replaces multiple adjacent spaces or newlines with one space
        /// </summary>
        /// <param name="s">The string to be replaced</param>
        /// <returns>The altered string</returns>
        private string RemoveDoubleSpaces(string s)
        {
            return Regex.Replace(s, @"\s+", " ");
        }
        /// <summary>
        /// Attempts to get an index of the specified array
        /// </summary>
        /// <param name="array">The array to fetch from</param>
        /// <param name="index">The index to attempt to fetch</param>
        /// <returns>Either the empty string or the array element</returns>
        private string TryGetArray(string[] array, int index)
        {
            return array.Length > index ? array[index] : "";
        }

        /// <summary>
        /// Pushes course information to the database
        /// </summary>
        /// <param name="courses">The list of course informaiton to push</param>
        /// <param name="oldCourses">The list of course information that is already in the database</param>
        private void PushCourses(IEnumerable<Course> courses, Dictionary<string, DataRow> oldCourses)
        {
            List<MySqlCommand> courseCommands = new List<MySqlCommand>();
            foreach (Course c in courses)
            {
                if (!oldCourses.ContainsKey(c.Num))
                {
                    courseCommands.Add(c.GetInsertCommand());
                }
                else if (CourseDataChanged(c, oldCourses[c.Num]))
                {
                    courseCommands.Add(c.GetUpdateCommand());
                }
            }
            Commands.IssueCommands(courseCommands);
        }
        /// <summary>
        /// Checks if new course data is different than what is already in the database
        /// </summary>
        /// <param name="c">New data</param>
        /// <param name="dr">Old data</param>
        /// <returns>True if new data is different, false otherwise</returns>
        private bool CourseDataChanged(Course c, DataRow dr)
        {
            return (c.Num != dr["CourseNum"].ToString() ||
                c.Name != dr["CourseName"].ToString() ||
                c.Description != dr["Description"].ToString() ||
                c.Credits != dr["Credits"].ToString() ||
                c.Lec != dr["LectureCredits"].ToString() ||
                c.Rec != dr["RecitationCredits"].ToString() ||
                c.Lab != dr["LabCredits"].ToString() ||
                string.Join(", ", c.Offered) != dr["SemestersOffered"].ToString() ||
                c.Restrictions != dr["Restrictions"].ToString() ||
                c.PreRequisites != dr["Prereq"].ToString() ||
                c.CoRequisites != dr["Coreq"].ToString());
        }
        #endregion

        #region Sections
        /// <summary>
        /// Runs the section scraper
        /// </summary>
        /// <returns>True if it successfully pushes to the database, false otherwise</returns>
        public bool RunSections()
        {
            Console.WriteLine($"[{DateTime.Now:s}] - Running Section Scraper");
            
            HtmlWeb web = new HtmlWeb();
            web.Load(_refreshUrl);

            foreach (string section in GetSections())
            {
                HtmlDocument doc = new HtmlDocument();
                try
                {
                    doc.Load(new WebClient().OpenRead(_sectionInfoUrl + section));
                }
                catch (Exception e)
                {
                    Console.WriteLine($"{e.GetType()} encountered while getting section info\n{e}");
                    GC.Collect();
                    return false;
                }

                string semester = "";
                string year = section.Substring(0, 4);
                List<Section> sections = new List<Section>();
                try
                {
                    switch (section.Substring(4, 2))
                    {
                        case "01": semester = "Spring"; break;
                        case "05": semester = "Summer"; break;
                        case "09": semester = "Fall"; break;
                        default: semester = "Unknown"; break;
                    }
                    sections = ParseSections(doc, semester, year);
                }
                catch (Exception e)
                {
                    Console.WriteLine($"{e.GetType()} encountered while parsing section info\n{e}");
                    GC.Collect();
                    return false;
                }

                try
                {
                    PushSections(sections, Commands.GetOldSections(semester, year));
                }
                catch (Exception e)
                {
                    Console.WriteLine($"{e.GetType()} encountered while pushing section info\n{e}");
                    GC.Collect();
                    return false;
                }
            }

            GC.Collect();
            Console.WriteLine($"[{DateTime.Now:s}] - Section Scraper Finished");
            return true;
        }

        /// <summary>
        /// Gets the HTML page names of sections that need to be pushed
        /// </summary>
        /// <returns>A list of HTML page names</returns>
        private IEnumerable<string> GetSections()
        {
            HtmlWeb web = new HtmlWeb();
            var rows = web.Load(_sectionInfoUrl).DocumentNode.SelectSingleNode("//table").SelectNodes("tr");
            var sectionRows = rows.Where(x => x.SelectNodes("td")?[1].InnerText.Length == 11).ToList();
            sectionRows.RemoveAll(x => (DateTime.Now - DateTime.Parse(x.SelectNodes("td")[2].InnerText)).TotalDays >= 1);
            return sectionRows.Select(x => x.SelectNodes("td")[1].InnerText);
        }
        /// <summary>
        /// Parses section information from HTML
        /// </summary>
        /// <param name="doc">The HtmlDocument to parse</param>
        /// <param name="semester">The semester of the section data</param>
        /// <param name="year">The year of the section data</param>
        /// <returns>A list of parsed section information</returns>
        private List<Section> ParseSections(HtmlDocument doc, string semester, string year)
        {
            List<Section> sections = new List<Section>();
            HtmlNode table = doc.DocumentNode.SelectNodes("//table")[3];
            foreach (HtmlNode row in table.SelectNodes("tr"))
            {
                HtmlNodeCollection cells = row.SelectNodes("td");
                if (cells == null) continue;

                List<string> rowData = new List<string>();
                foreach (HtmlNode cell in cells)
                    for (int i = 0; i < int.Parse(cell.Attributes["colspan"]?.Value ?? "1"); i++)
                        rowData.Add(RemoveDoubleSpaces(cell.InnerText.Trim(_trimChars).Replace("&nbsp;", "")));

                bool online = false;
                if (rowData[4].Contains("OL"))
                {
                    online = true;
                    rowData[4] = rowData[4].Replace("OL", "");
                }

                Section s = new Section
                {
                    Crn = rowData[0],
                    Subject = rowData[1],
                    CourseNum = rowData[2],
                    SectionNum = rowData[3],
                    Campus = rowData[4],
                    Credits = rowData[5],
                    Title = rowData[6],
                    Days = rowData[7],
                    Time = rowData[8],
                    Cap = rowData[9],
                    Act = rowData[10],
                    Rem = rowData[11],
                    Instructor = rowData[12],
                    Dates = rowData[13],
                    Location = rowData[14],
                    Fee = rowData[15],
                    IsOnline = online,
                    Year = year,
                    Semester = semester
                };

                if (s.Crn == "")
                    sections[sections.Count - 1].Add(s);
                else sections.Add(s);
            }
            return sections;
        }

        /// <summary>
        /// Pushes section information to the database
        /// </summary>
        /// <param name="sections">New section information</param>
        /// <param name="oldSections">Old section information</param>
        private void PushSections(List<Section> sections, Dictionary<string, DataRow> oldSections)
        {
            List<MySqlCommand> sectionCommands = new List<MySqlCommand>();
            foreach (Section s in sections)
            {
                if (!oldSections.ContainsKey(s.Crn))
                {
                    sectionCommands.Add(s.GetInsertCommand());
                }
                else if (SectionDataChanged(s, oldSections[s.Crn]))
                {
                    sectionCommands.Add(s.GetUpdateCommand());
                }
            }
            Commands.IssueCommands(sectionCommands);
        }
        /// <summary>
        /// Checks if new course data is different than what is already on the database
        /// </summary>
        /// <param name="s">New section information</param>
        /// <param name="dr">Old section information</param>
        /// <returns>True if data has changed, false otherwise</returns>
        private bool SectionDataChanged(Section s, DataRow dr)
        {
            return $"{s.Subject} {s.CourseNum}" != dr["CourseNum"].ToString() ||
                s.SectionNum != dr["SectionNum"].ToString() ||
                s.Campus != dr["Type"].ToString() ||
                s.Credits != dr["Credits"].ToString() ||
                s.Days != dr["Days"].ToString() ||
                s.Time != dr["SectionTime"].ToString() ||
                s.Cap != dr["Capacity"].ToString() ||
                s.Act != dr["SectionActual"].ToString() ||
                s.Rem != dr["SlotsRemaining"].ToString() ||
                s.Instructor != dr["Instructor"].ToString() ||
                s.Dates != dr["Dates"].ToString() ||
                s.Location != dr["Location"].ToString() ||
                s.Fee != dr["Fee"].ToString() ||
                s.IsOnline != Convert.ToBoolean(dr["Online"]);
        }
        #endregion

        /// <summary>
        /// Sends email notifications if classes have opened up since last run
        /// </summary>
        /// <returns>True if all notifications are successful, false otherwise</returns>
        public bool SendEmails()
        {
            Console.WriteLine($"[{DateTime.Now:s}] - Sending Emails");
            List<long> sentEmails = new List<long>();
            bool failedSomewhere = false;
            try
            {
                DataTable emails = Commands.GetEmailsToSend();
                DataTable info = Commands.GetEmailData();
                SmtpClient client = new SmtpClient("smtp.gmail.com")
                {
                    Credentials = new NetworkCredential("banwebplusplus@gmail.com", Secret.Password),
                    EnableSsl = true
                };

                foreach (DataRow email in emails.Rows)
                {
                    DataRow section = info.Select($"CRN = {email["CRN"]} Semester = {email["Semester"]} Year = {email["Year"]}").FirstOrDefault();
                    if (section != null && Convert.ToInt32(section["SlotsRemaining"]) > 0)
                    {
                        string crn = email["CRN"].ToString();
                        string courseNum = section["CourseNum"].ToString();
                        string courseName = section["CourseName"].ToString();
                        int slots = Convert.ToInt32(section["SlotsRemaining"]);
                        MailMessage message = new MailMessage("banwebplusplus@gmail.com", email["Email"].ToString(),
                            $"BanwebPlusPlus Class Opening Notification for: {courseNum}",
                            $"This is an automated message from BanwebPlusPlus. " +
                            $"As of last pull, there {(slots > 1 ? "are" : "is")} currently " +
                            $"{slots} {(slots > 1 ? "openings" : "opening")} in {courseName}({crn}).");
                        client.Send(message);
                        sentEmails.Add(Convert.ToInt64(email["Id"]));
                    }
                }
            }
            catch (Exception e)
            {
                Console.WriteLine($"{e.GetType()} encountered while sending update emails\n{e}");
                failedSomewhere = true;
            }

            try
            {
                Commands.UpdateSentEmails(sentEmails);
            }
            catch (Exception e)
            {
                Console.WriteLine($"{e.GetType()} encountered while updating database with send emails. This is probably gonna result in some people getting repeat emails\n{e}");
                failedSomewhere = true;
            }

            Console.WriteLine($"[{DateTime.Now:s}] - Finished Sending Emails");
            return !failedSomewhere;
        }
    }
}
