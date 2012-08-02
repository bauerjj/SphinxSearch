SphinxSearch
============

This is a advanced search plugin for Vanilla Forums  >= v2.0.18.4 . It is based on the Sphinx Search engine v2.0.5

Live Demo: http://mcuhq.com/mcuhq/vanilla/search?tar=srch


####How it Works
For nitty gritty details behind sphinx, you should look at their main documentation: http://sphinxsearch.com/docs/current.html

Basically, it indexes (optinally can store) the fields listed above and then stores some attributes that are used to filter the results down such as comment count, category name, etc. Sphinx then returns a document ID which is then used in a typicall MYSQL query to retrieve the meat and potatoes of it such as last comment ID, category URL code, etc. Almost all searches are returned instantly (<12ms). 

The plugin connects to searchd and queries it using the shpinx API. You should notice a significant speed increase and search relevance. 

##Install
No backend knowledge is required to install this! Everything is done for you via the install wizard. It comes bundled with the build of the sphinx search engine. To install, run the install wizard and complete the **3** step process. If the wizard encounters any errors, it will tell you. If the installer package does not work on your server, you can perform a manual installation via your distro's package manager and then tell the wizard where to find your installed files. 

To enable the plugin, simply download it from Vanilla Forum's plugin portal and move it to your webserver's plugin folder. Enable it from the dashboard. Some files need to be given write permissions by the installer:
  * pid/log/error.txt
  * ~SphinxSearch/Install 

The install process allows you to run long tasks, such as *./configure* and *./make* in the background. While this is going on, the terminal output will be presented to you. This is also possible to do while indexing your indexes, which may take a long time depending on the amount of documents in the database.

After installation, do the following in the control panel:

1. Start searchd 
2. index 'Main' 
3. index 'Delta' 
4. index 'Stats' 
5. Stop and then start searchd again
6. Search for something on your forums through the usual means
7. Setup a cron job to run the three cron files

##Features
####Search
The follwing fields are indexed and thus searchable:
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

As of v1.0, the following are a list of widgets
######Advanced Search & result Page
This overrides the current search algorithm and substitues a more advanced search option. This will automatically revert to the default search engine if it detects that sphinx is not running. This will happend during indexing. All of the existing search queries will still be valid, but the advanced options will have no effect on the results. The number of results shown on each page is configurable in the admin settings. The view format can be set by the user (classic, table, simple, sleak)


######Post Searches
Much like on stackoverflow, any new discussion that is being typed into the title box will start sphinx looking for related threads in reference to the new potential thread. A box will appear the input box showing some relevant threads.

######Related Main Threads
For each main search, the side panel will include a list of related threads based on the query. 

######Related Main Searches
For each main search, the side panel will include a list of full search phrases that match the current one.

######Top Keywords
The plugin will keep a list of all search queries and display the top xx amount of keywords in the panel on the advanced search landing page. 

######Top Searches
Sidepanel displays the top searches in the panel on the advanced search landing page

######Search Help
Simply shows how to use the extended search syntax in the left side panel on the advanced search landing page

######Related Discussion Threads
For each discussion, a list of related threads in reference to the currently viewed one can be either shown in the sidepanel or below/above each discussion. 


####Developers
This plugin was built to encourage others to add to its functionality. The widgets are implemented in such a way to make a new addition relativly easy. 

To learn how to do so, look at an existing widget. All of them extend the abstract class, *widgets*, which provides common generic routines used by all of the widgets. All of the settings related to sphinx and the sphinx client are passed as construct parameters. This is important!! Only one instance of the sphinx API should be used since only **1** query is made. The widgets add a query to main batch which is then run before each rendered view. An exception to this rule are things that need to be executed from a handler inside of Vanilla such as the PostSearch

####Improvements
There are multiple improvements that can be made. Here are a list of widgets that can be done, but I have lost track of time:

   *  'Did you Mean' feature on the main search
   *  Include some of the advanced search options on the result page...maybe a javascript toggle div
   *  Locales other than English
   *  More filter options
   *  Improve the autocompete fields and speed of the jquery UI POST. For some reason, it is very slow and I woud like to incorporate the stock Vanilla Forum's autocomplete magic, but I had problems doing so....



