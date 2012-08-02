SphinxSearch
============

This is a advanced search plugin for Vanilla Forums  >= v2.0.18.4 . It is based on the Sphinx Search engine v2.0.5

Live Demo: http://mcuhq.com/mcuhq/vanilla/search?tar=srch

##Install
To enable the plugin, simply download it from the plugin portal and move it to your plugin's folder. Enable it from the dashboard. Some files need to be given write permissions by the installer:
  * pid/log/error.txt
  * ~SphinxSearch/Install 
  * 
  * 
The install process allows you to run long tasks, such as *./configure* and *./make* in the background. While this is going on, the terminal output will be presented to you. This is also possible to do while indexing your indexes, which may take a long time depending on the amount of documents in the database.

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
######Advanced Search Page
This overrides the current search algorithm and substitues a more advanced search option. This will automatically revert to the default search engine if it detects that sphinx is not running. This will happend during indexing. All of the existing search queries will still be valid, but the advanced options will have no effect on the results. 

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




