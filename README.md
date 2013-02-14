SphinxSearch
============

####An advanced search plugin for Vanilla Forums based on the Sphinx Search engine v2.0.6

[Live Demo #1](http://homebrewforums.net/search?Search=beer)

[Live Demo #2](http://mcuhq.com/mcuhq/vanilla/search?tar=srch)

[Photo Album](http://imgur.com/a/jQ5WE#1)

[Offical Plugin Page](http://vanillaforums.org/addon/sphinxsearch-plugin)

Table of contents
-----------------

1. [How it Works](#how-it-works)
2. [Requirements](#requirements)
3. [Install](#install)
4. [Features](#features)
	1. [Search](#search)
	2. [Admin](#admin)
	3. [Widgets](#widgets)
5. [Developers](#developers)
6. [Improvements](#improvements)
7. [FAQ](#faq)
8. [Donate](#donate)

##How it Works
For nitty gritty details behind sphinx, you should look at their main documentation: http://sphinxsearch.com/docs/current.html

Basically, it indexes (optinally can store) the certain fields and then stores some attributes that are used to filter the results down such as comment count, category name, etc. Sphinx then returns a document ID which is then used in a typicall MYSQL query to retrieve the meat and potatoes of it such as last comment ID, category URL code, etc. Almost all searches are returned instantly (<12ms).

Sphinx requires indexing, which is why cron tasks should be used on your server to run periodically. You can always manually index in the control panel for testing purposes. The 'Main' index will read the discussions and comments table in your database. The 'Delta' will do the same thing, except it will only pickup the ones since the last index was performed. Sphinx will search through both indexes, so you should index 'Delta' frequently and 'Main' during non-peak hours.

The plugin connects to searchd and queries it using the shpinx API. You should notice a significant speed increase and search relevance.

##Requirements

 1. Linux Only!
 2. PHP >= 5.3.0
 3. Shell Access
 4. Spawn a Daemon (searchd)
 5. Port Forwarding
 6. Vanilla >= v2.0.18.4

Sphinx does work on windows, it's just that I have not built the installer for windows as of yet. Maybe you can help!
Shared hosting will probably restrict searchd from running properly on your host's servers, but you can try.

##Install
No backend knowledge is required to install this! Everything is done for you via the install wizard. It comes bundled with the build of the sphinx search engine. To install, run the install wizard and complete the **3** step process. If the wizard encounters any errors, it will tell you. If the installer package does not work on your server, you can perform a manual installation via your distro's package manager and then tell the wizard where to find your installed files.

To enable the plugin, simply download it from Vanilla Forum's plugin portal and move it to your webserver's plugin folder. Enable it from the dashboard. Some files need to be given write permissions by the installer:
  * ~SphinxSearch/Install folder
  * ~SphinxSearch/Install/pid/error/output.txt files


The install process allows you to run long tasks, such as *./configure* and *./make* in the background. While this is going on, the terminal output will be presented to you. This is also possible to do while indexing your indexes, which may take a long time depending on the amount of documents in the database.

After installation, do the following in the control panel:

1. Start searchd
2. index 'Main'
3. index 'Delta'
4. index 'Stats'
5. Stop and then start searchd again
6. Search for something on your forums through the usual means
7. Setup a cron job to run the three cron files
8. ERASE ALL FILES IN /cache folder

##Features
####Search
The following fields are indexed and thus searchable:
   * Thread title
   * Body text
   * Author name

The following is a list of search filters:
  * By Username
  * Titles only
  * Threads with 1 or more replies
  * Certain sub forums
  * Tags (if tagging plugin enabled)
  * Date
  * Order By date, views, replies, relevance or mixture

There are multiple viewing formats avaliable such as table or vertical layout. The tags and usernames are autocomplete fields. It also supports the sphinx extended syntax such as: *@title hello @body world*

####Admin
Provides a backend to manage essentially everything related to sphinx.
  * Linux installer for the prepackaged tarball. Can also use existing system binaries if installed already
  * Control panel for managing the indexer and search daemon
  * Automatically generated configuration and cron files
  * Numerous supported options that can be enabled in the sphinx configuration (morphology, stemming, etc)
  * Enable/Disable widgets and their parameters (word highlighting, max matches, view format)
  * Install FAQ

####Widgets
All of the widgets that display a discussion/thread title will have a tooltip that will display the first xxx amount of words from the original text. To show this text, simply hover over the title for a second to see the discussion body text.

As of v20120805, the following are a list of widgets
#####Advanced Search & result Page
This overrides the current search algorithm and substitues a more advanced search option. This will automatically revert to the default search engine if it detects that sphinx is not running. This will happend during indexing. All of the existing search queries will still be valid, but the advanced options will have no effect on the results. The number of results shown on each page is configurable in the admin settings. The view format can be set by the user (classic, table, simple, sleek)


#####Post Searches
Much like on stackoverflow, any new discussion that is being typed into the title box will start sphinx looking for related threads in reference to the new potential thread. A box will appear the input box showing some relevant threads.

#####Related Main Threads
For each main search, the side panel will include a list of related threads based on the query.

#####Related Main Searches
For each main search, the side panel will include a list of full search phrases that match the current one.

#####Top Keywords
The plugin will keep a list of all search queries and display the top xx amount of keywords in the panel on the advanced search landing page.

#####Top Searches
Sidepanel displays the top searches in the panel on the advanced search landing page

#####Search Help
Simply shows how to use the extended search syntax in the left side panel on the advanced search landing page

#####Related Discussion Threads
For each discussion, a list of related threads in reference to the currently viewed one can be either shown in the sidepanel or below/above each discussion.

#####HitBox
For each search, there is a hitbox that will detail the number of documents that matched each word and number of hits total. A caveat of this is that the analysis of each word comes **BEFORE** any filtering, so the results may differ than what the hitbox says. For instance, 26 documents in the document for the word, *vanilla*, may only result in 8 threads/comments with that same word due to filtering or post processing.

##Developers
This plugin was built to encourage others to add to its functionality. The widgets are implemented in such a way to make a new addition relativly easy.

To learn how to do so, look at an existing widget. All of them extend the abstract class, *widgets*, which provides common generic routines used by all of the widgets. All of the settings related to sphinx and the sphinx client are passed as construct parameters. This is important!! Only one instance of the sphinx API should be used since only **1** query is made. The widgets add a query to main batch which is then run before each rendered view. An exception to this rule are things that need to be executed from a handler inside of Vanilla such as the PostSearch

##Improvements
There are multiple improvements that can be made. Here are a list of widgets that can be done, but I have lost track of time:

   *  Windows support
   *  'Did you Mean' feature on the main search
   *  Add a nice javascript dropdown menu to all of the search boxes that show some quick advanced search options
   *  Add more widgets related to top searches (maybe top searches for this month, week, year, etc)
   *  Improve the autocompete fields and speed of the jquery UI POST. For some reason, it is very slow and I woud like to incorporate the stock Vanilla Forum's autocomplete magic, but I had problems doing so....


##FAQ

#####Will this work for me?
Depends...are you on a shared host? If so, this is probably not for you, but talk to your hosting provider about having a daemon run on your server.
If sphinx runs for a just a few seconds/minutes and is then shutdown mysteriously, chances are that your host is killing it. Talk to your hosting provider.
Windows support may come in later releases if there is enough demand. If the auto installer does not work for you, try installing using your distro's package manger and then telling the plugin where the requested files are.

#####Can't find indexer at path: Not Detected
You will encounter this at the control panel if sphinx is not properly installed. The control will not respond to anything until these paths are resolved by using the install wizard
You may also encounter this if PHP cannot read files outside of your typical www folders. Since sphinx maybe installed elsewhere on your system, make sure that PHP
has read access to wherever sphinx searchd/indexer/conf is located. This can be done in your php.ini under "open_basedir"

#####What if sphinx is indexing and it shuts down searchd...now what?
Anytime sphinx is indexing, it will shut down all searches temporary (unless you have another instance of searchd setup). The default search will be in effect immidiatly until searchd is running again. This is done automatically for you

#####What is the indexer and searchd?
These are two seperate entities that work together. Indexer indexes your database fields and searchd listens on your server for search requests to query the indexer

#####How does Sphinx work?
Sphinx indexes your discussion titles, body, and author names, making them easily searchable
It does not store your text, but rather uses a special data structure to optimize searching
Sphinx is as dedicated indexing/query engine, and therefore can do it a lot better, rather than MYSQL/MyISAM

#####Run in Background?
This lets all of the index and install commands to run in the background. The progress is then printed onto your screen (black terminal look-a-alike). The benefit of this is that you can see the progress in real time. A benefit of NOT running in background is that you can spot errors easier, although your browser will be waiting for each task to complete and it will appear that the website has frozen. This is not the case...let it finish.

#####What's the deal with the cron files?
Sphinx needs to reindex your database here and there to stay current. The 'Main' and 'Delta' index work together to achieve optimal results
You should index 'Main' once in a while, depending on the activity of your forum. Delta should be updated more frequent since it should only update much less than the Main index
Use the cron files to update sphinx during low peak times. Remember, reindex delta often, and main seldom. More info, see section 3.12 of the main sphinx documenation

#####How do I get rid of some of the top searches/tags?
Add the words to your stoplist.txt found in the assests folder of this plugin and then reindex. Over time, you should see these dissappear
Future versions may let you censor this easier, but for now be sure to enable the stopwords feature

#####Error xxxx Permission denied
This error mostly occurs when NOT using pre packaged sphinx installer. You have to give sphinx read/write permission to all log and data temp files by using CHMOD

#####Control Panel says 10 queries were made, but I only made 1 search?
Total Queries does not mean 'Total Searches'(i.e 12 queries != 12 individual searches on your site.
For each search, there are other numerous searches being processed such as related threads and top searches

#####You get a "stop: kill() on pid xxxx failed: Operation not permitted Sphinx"
This is because you started sphinx from the Command line or some other means and now there are user permission problems...stop searchd through the same means you started it with

#####fsockopen(): unable to connect to localhost::xxxx ....
First try to start searchd and then check the port again

#####"Failed to open log/pid file".
You should kill all instances of searchd by using 'ps' in the command line. If that does not work, delete all files in your ../sphinx/var/log folder and reboot

#####My indexes are reindexed through my cron job, but the index time is incorrect
Yea, I know...this is only updated if you index through the control panel

##Donate
If you have found this plugin extremely useful, feel free to [donate here](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=kapotchy%40gmail%2ecom&lc=US&item_name=Sphinx%20Search&no_note=0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHostedGuest)

