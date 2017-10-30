# banwebplusplus
------------------------------------------------------------------------------------------
                 ____                          _                
                |  _ \                        | |     _     _   
                | |_) | __ _ _ ____      _____| |__ _| |_ _| |_ 
                |  _ < / _` | '_ \ \ /\ / / _ \ '_ \_   _|_   _|
                | |_) | (_| | | | \ V  V /  __/ |_) ||_|   |_|  
                |____/ \__,_|_| |_|\_/\_/ \___|_.__/            
                                                                
------------------------------------------------------------------------------------------                                                 

Rundown of how everything works:

To use banwebplusplus:
Go to banwebplusplus.me

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
  
Remember this is a WIP. More functionality to be added in coming weeks. 
