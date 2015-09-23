
Current Version: 2.1.1 (Sep 2015)

Thank you for downloading the EDS Reading List Tool.  This plugin is an LTI Tool Provider 1.0 (http://www.imsglobal.org/toolsinteroperability2.cfm), and is compatible with any learning management system that is LTI-compliant.  A list of compliant LMS's (including Moodle, Blackboard, Canvas, and Desire2Learn) can be found here: http://developers.imsglobal.org/catalog.html

Please skip down to "Upgrading an Existing Installation" if you already have an earlier version of Curriculum Builder running.

<h2>NEW INSTALLS (you have not installed Curriculum Builder before)</h2>

== ENVIRONMENT REQUIREMENTS ==
The EDS-in-LMS Plugin requires PHP 5.x or higher, MySQL 5 or higher, and an Apache web server.  You will need to have the mcrypt libraries installed.

You will also need an EDS API profile on your account, along with its profileid and an associated username and password.  These can be obtained from EBSCO.  Please send a request as follows:

"I would like to request a new EDS API profile be set up on our account.  Please ensure that this profile contains the same databases, CustomLinks, and all other settings as found on our main EDS profile.  When this profile has been created, please create a new user in the Authentication tab that has access to the group that contains this EDS API profile.  Finally, please send me this new userid, its associated password, and the profileid for the EDS API profile you create.  This is for the EDS Reading List Tool."

== SETTING UP THE MYSQL DATABASE ==
This process will create the database required by the EDS-in-LMS Plugin.  The database will contain eight tables:
    * readings - individual entries for each item in each reading list, tied to a specific list in the lists table
    * authors - name and contact information for reading list users
    * authorlists - connects lists to authors
    * lists - entries for each reading list
    * credentials - manages the EDS Profiles that connect to this tool
    * credentialconsumers - ties EDS Profiles to LMSs
    * oauth - manages the Consumer Key / Secret pairs that allow LMSs to connect to this tool
    * authtokens - manages authentication tokens for the EDS API
    * studentaccess - records student names as they access lists
    * studentreadings - records student names as they access individual readings


- Set Up Steps -
    1. Create a new database in MySQL
    2. Create a user and grant all privileges on that new database.
    3. Import the "reading_list.sql" file included in the root directory of this plugin.


== HOSTING INSTRUCTIONS ==
    1. Place this directory of files in a web accessible place
    2. Make note of the URL for the "lti.php" file found in this directory
    3. Open and edit "connect.php" and enter the values needed to connect to your MySQL instance.


== SETTING UP CONSUMER KEY / SECRET PAIRS ==
    1. Open the keys.php file found in the conf directory.
    2. Set the $username and $password variables to whatever values you wish.  This username and password is used only to log in to manageaccess.php and no where else.
    3. Point your web browser to the manageaccess.php file.  Log in with the username and password set in keys.php.  Here, you will need to create at least one Consumer Key/Secret combination.  It can be any value you wish.
    * If you wish to have multiple institutions connect to your tool, it is advisable to create a separate Consumer Key/Secret combination for each one.
    

== CONFIGURING THE TOOL IN YOUR LMS ==
Generally, you will follow the steps required of any LTI Tool Provider.  General instuctions for configuring an LTI tool in many LMS's can be found here: http://support.campusconcourse.com/entries/21762847-How-do-I-setup-an-LTI-consumer-in-my-specific-LMS-

You will be asked for a shared secret and consumer key.  You set this in the section above.
Set privacy setttings such that user names and emails will be shared with the tool if you want to enable student usage data collection.  Otherwise, these can be left to ‘Anonymous’ or ‘Private.’


== ADMINISTERING THE TOOL ==
To maintain the back-end database, open any reading list via your LMS, then change the browser URL to point to your tool's admin.php file.  Login with the consumer key and secret from above.  In this screen, you will configure CB to connect up with the EDS API, filling in the username, password, and profile information received from EBSCO support.



=== UPGRADING AN EXISTING CURRICULUM BUILDER ===

1. Open your connect.php file.  Take note of the values you have for the hostname, username, and password for the mysqli_connect function.  ALSO note the name of the database in the mysqli_select_db function.  You will need all of these values later.
2. Open your conf/keys.php file if you have one, and take note of the $username and $password variables.  You will also use these later.
3. Make a back up of your entire directory in the event of a failed upgrade.
4. Replace the ENTIRE code base with this new code base.
5. Open connect.php, and replace the values from Step 1 above.
6. Open conf/keys.php, and if you had a keys.php file before, replace the values from Step 2 above.  Otherwise, make a new $username and $password.
7. In a web browser, navigate to your update.php file.  This will run the database updates.

Your CB is now up-to-date.