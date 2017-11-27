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
2. Search for the course (Can be something like 'CS 1121' or 'Concur' with autofilling for 'Concurrent Programming'.
3. View Available slots by pressing the info button under the search tab for the class you are interested in
4. Click the green "Add" button next to the section you are interested in and it will add the course to the calendar to the right.
5. Login to Google (Top Right) and have the ability to upload your schedule to your primary google calendar.
6. When logged in you can save and load schedules perminantly.

Details on functionality:
There are multiple separate parts to banwebplusplus. Those include:
1. Banweb HTML Scraper and Parser
2. Backend database that the parser pushes to
3. User interface that is used to interact with everything (located at banwebplusplus.me)

To go into more detail on each of those 3 parts:
1. Scraper/Parser:
  This scrapes banweb's public page that shows class's and all information, then parses it into a mysql table that can be fed into our database tables. 
2. Database:
  The database is what holds all the tables for user information, class information, and anything that you would want to access when scheduling classes on banweb. It also includes tables for the schedules for each user.
3. User interface:
  Located in the view folder, main page index.html, this is what the user will be interfacing with (until we get a login page working). On here you can currently view a non-working calendar and a beginning to work search function. The search function searches the database for classes that match the search terms and returns them below. 
