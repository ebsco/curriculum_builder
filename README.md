*Current Version: 2.2 (Nov 2015)*

## Important Note

*If you choose to host Curriculum Builder yourself, please note that EBSCO Support cannot provide assistance with the hosting and troubleshooting of your tool.  If you notice errors with the code, please let us know, but installing this tool locally means you assume all responsibility for the tool.*

*Please see license.txt for information on use of this code.*

Thank you for downloading Curriculum Builder: Open Source.  This plugin is an LTI Tool Provider 1.0 (http://www.imsglobal.org/toolsinteroperability2.cfm), and is compatible with any learning management system that is LTI-compliant.  A list of compliant LMS's (including Moodle, Blackboard, Canvas, and Desire2Learn) can be found here: http://developers.imsglobal.org/catalog.html

Please skip down to **Upgrading an Existing Installation** if you already have an earlier version of Curriculum Builder running.

## Instructions for New Installations

### Environment Requirements

This tool requires PHP 5.x or higher, MySQL 5 or higher, and an Apache web server.  You will need to have the **mcrypt** libraries installed.

Even if you intend to use this for EBSCOhost-only databases, you will also need an EDS API profile on your account, along with its profileid and an associated username and password.  

If you are a current EDS customer, then send the following as an email to support@ebsco.com in order to get an API profile and the required credentials:
"I would like to request a new EDS API profile be set up on our account.  Please ensure that this profile contains the same databases, CustomLinks, and all other settings as found on our main EDS profile.  When this profile has been created, please create a new user in the Authentication tab that has access to the group that contains this EDS API profile.  Finally, please send me this new userid, its associated password, and the profileid for the EDS API profile you create.  This is for the EDS Reading List Tool."

If you are NOT an EDS customer, you can build your API profile yourself pretty quickly following these instructions:

1. In EBSCOadmin, click on *Profile Maintenance*
2. Click on *Add New Profile*
3. Give your profile an ID (recommended: *cbuilder*) - you'll need this later
4. Give your profile a name (recommended: *Self-Hosted Curriculum Builder*)
5. In the **Interface** dropdown, select EDS API
6. The rest of the options can stay at their defaults.  Click Submit and it will build your profile.
7. Click *Customize Services* at the top of the screen
8. Select your new EDS API Profile (the one titled with the name provided in step 4) from the **Choose Profile** dropdown menu
9. Click on the *Databases* tab and *Enable All* databases in the tab.  You can also be more selective if you'd rather leave some databases out.  Click *Submit* when done.
10. In the *Searching* tab, click on *Modify* next to **Limiters**
11. Default the *Full Text* limiter to **on**.  You can also sort and relabel the other limiters here.
12. Click *Submit*, then click *View Changes on EBSCO* near the top of the settings.  This will take you to the EDS API Console - you do not have to do anything here.  Clicking the link was required to "push" the changes.
13. In EBSCOadmin, click on the *Authentication* tab
14. In the *UserID/Password* tab, click *Add New User*
15. Make sure the *Group ID* is set to **Main User Group**, then create a new userID and password for Curriculum Builder and write them down somewhere.  You'll need these and the profile ID from step 3 later.
16. Click *Submit*


### Setting up the Database

This process will create the database required by the EDS-in-LMS Plugin.  The database will contain eight tables:

Table Name | Description
--- | ---
readings | individual entries for each item in each reading list, tied to a specific list in the lists table
authors | name and contact information for reading list users
authorlists | connects lists to authors
lists | entries for each reading list
credentials | manages the EDS Profiles that connect to this tool
credentialconsumers | ties EDS Profiles to LMSs
oauth | manages the Consumer Key / Secret pairs that allow LMSs to connect to this tool
authtokens | manages authentication tokens for the EDS API
studentaccess | records student names as they access lists
studentreadings | records student names as they access individual readings
folders | list of folders in reading lists

#### Set Up Steps
1. Create a new database in MySQL
2. Create a user and grant all privileges on that new database.
3. Import the **reading_list.sql** file included in the root directory of this plugin.

### Hosting Instructions
1. Place this directory of files in a web accessible place
2. Make note of the URL for the **lti.php** file found in this directory
3. Open and edit **connect.php** and enter the values needed to connect to your MySQL instance.
4. In a web browser, navigate to your **update.php** file. This will run the database updates.

### Setting up Consumer Key / Secret Pairs
1. Open the **conf/keys.php** file found in the conf directory.
2. Set the **username** and **password** variables to whatever values you wish.  This username and password is used only to log in to **manageaccess.php** and no where else.  If possible, restrict access to manageaccess.php to a local IP address.
3. Point your web browser to the **manageaccess.php** file.  Log in with the username and password set in **conf/keys.php**.  Here, you will need to create at least one Consumer Key/Secret combination.  It can be any value you wish.
4. Once created, click on *Configure / Admin* in the row for that key/secret combination.  This will launch the admin panel for that key/secret pair.
5. Fill in all the options, and for EBSCO User ID, Password and Profile ID, use the values you received from EBSCO or that you created yourself from the **Environment Requirements** above.

  * *If you wish to have multiple institutions connect to your tool, it is advisable to create a separate Consumer Key/Secret combination for each one.*
    
## Upgrading an existing Curriculum Builder installation without GitHub

1. Open your **connect.php** file.  Take note of the values you have for the **hostname**, **username**, and **password** for the *mysqli_connect* function.  ALSO note the **name of the database** in the *mysqli_select_db* function.  You will need all of these values later.
2. Open your **conf/keys.php** file if you have one, and take note of the **username** and **password** variables.  You will also use these later.
3. Make a back up of your entire directory in the event of a failed upgrade.
4. Replace the entire code base with this new code base.
5. Open **connect.php**, and replace the values from Step 1 above.
6. Open **conf/keys.php**, and if you had a **keys.php** file before, replace the values from Step 2 above.  Otherwise, make a new **username** and **password**.
7. In a web browser, navigate to your **update.php** file.  This will run the database updates.

Your instance of Curriculum Builder is now up-to-date.

## Configuring Your Learning Management System

Videos and instructions can be found at http://ebsco.libguides.com/curriculumbuilder

## Curriculum Builder Administration
To maintain the back-end database, set logos and branding elements, select adminstrative options, etc, point your browser to your tool's **admin.php** file.  Login with the consumer key and secret from above.  In this screen, you will configure Curriculum Builder to connect up with the EDS API, filling in the username, password, and profile information received from EBSCO support.

For more information on administration, see http://ebsco.libguides.com/curriculumbuilder
