# banwebplusplus
------------------------------------------------------------------------------------------
                 ____                          _                
                |  _ \                        | |     _     _   
                | |_) | __ _ _ ____      _____| |__ _| |_ _| |_ 
                |  _ < / _` | '_ \ \ /\ / / _ \ '_ \_   _|_   _|
                | |_) | (_| | | | \ V  V /  __/ |_) ||_|   |_|  
                |____/ \__,_|_| |_|\_/\_/ \___|_.__/            
                     MAKE SCHEDULING GREAT AGAIN!                                           
------------------------------------------------------------------------------------------                                                 

HOW TO USE AND RUNDOWN OF HOW STUFF WORKS:

To use banwebplusplus:
Go to banwebplusplus.me

How to add classes to schedule (currently):
1. Go to banwebplusplus.me
2. Search for the course (Can be something like 'CS 1121' or 'Concur' with autofilling for 'Concurrent Programming'). You can search by course number (CS 1121), course name (Concurrent), or instructor.
3. View Available slots by pressing the info button under the search tab for the class you are interested in. This will show you detailed course information, including a description, restrictions and prerequisites, and detailed information about each available section.
4. Click the green "Add" button next to the section you are interested in and it will add the course to the calendar to the right.
5. Login to Google (Top Right) and have the ability to upload your schedule to your primary Google Calendar, as well as save schedules for later viewing.

Details on functionality:
There are multiple separate parts to banwebplusplus. Those include:
1. Banweb HTML Scraper and Parser
Written in C# for speed benefits, this scrapes banweb's public page that shows class's and all information, then parses it into a consistent format that is imported into our own database. The scraper runs approximately every 5 minutes to keep information up-to-date. Code for the scraper is available in the BanWebScraper folder, but is run on a separate server from the rest of the site, since it requires a Windows server to run.
2. Backend database that the parser pushes to
The database is what holds all the tables for user information, class information, and anything that you would want to access when scheduling classes on banweb. It also includes tables for the schedules for each user.
3. User interface that is used to interact with everything (located at banwebplusplus.me)
Located in the view folder, main page index.php, this is what the user will be interfacing with. It is setup as a single-page application - the only page fully loaded is index.php. All of the site's functions are then triggered through javascript, which requests information from the backend database when necessary. PHP is used on the backend to interface between the javascript and MySQL database.
