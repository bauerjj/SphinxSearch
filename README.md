SphinxSearch
============

This is a advanced search plugin for Vanilla Forums  >= v2.0.18.4 . It is based on the Sphinx Search engine v2.0.5

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
As of v1.0, the following are a list of widgets
######Advanced Search Landing Page
This overrides the current search algorithm and substitues 

######Releated Searches
  * 


