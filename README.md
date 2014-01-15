SphinxSearch
============

####An advanced search plugin for Vanilla Forums based on the Sphinx Search engine v2.0.6 and above

[Live Demo #1](https://forums.robertsspaceindustries.com/search?Search=search)

[Live Demo #2](http://homebrewforums.net/search?Search=beer)

[Photo Album](http://imgur.com/a/jQ5WE#1)

[Offical Plugin Page](http://vanillaforums.org/addon/sphinxsearch-plugin)

Branches:

[Lite Version](https://github.com/mcuhq/SphinxSearchPlugin/tree/SphinxSearchLite)

[Integrated Installer Version] (https://github.com/mcuhq/SphinxSearchPlugin/tree/WithInstaller)

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

It indexes (optionally can store) the certain fields and then stores some attributes that are used to filter the results down such as comment count, category name, etc. Sphinx then returns a document ID which is then used in a typical MYSQL query to retrieve the meat and potatoes of it such as last comment ID, category URL code, etc. Almost all searches are returned instantly (<12ms).

Sphinx requires indexing, which is why cron tasks should be used on your server to run periodically. You can always manually index in the control panel for testing purposes. The 'Main' index will read the discussions and comments table in your database. The 'Delta' will do the same thing, except it will only pickup the ones since the last index was performed. Sphinx will search through both indexes, so you should index 'Delta' frequently and 'Main' during non-peak hours.

The plugin connects to 'searchd' and queries it using the Sphinx API. You should notice a significant speed increase and search relevance.

Any sphinx errors will be printed directly to the user

##Requirements

 1. PHP >= 5.3.0
 2. Sphinx Installed >= v2.0.6, either via distro or binary
 3. Vanilla Version
    - 20140114 and above: all v2.1x versions
    - 20131210 and below: v2.0.18.x
 4. Enable URL Rewriting ($Configuration['Garden']['RewriteUrls'] = TRUE)

Shared hosting will probably restrict sphinx from running properly on your host's servers, but you can try.

##Install
 1. Download the latest [Sphinx Search](http://sphinxsearch.com/downloads/) build and install it from binary on windows or distro on linux
 2. Download the [SphinxSearchPlugin](http://vanillaforums.org/addon/sphinxsearch-plugin) from Vanilla Forums's Plugin portal
 3. Extract the zip file to your webserver's plugin folder
 4. **Replace the default `sphinxapi.php` file** in the `SphinxSearch` plugin folder with the one from the downloaded archive in step 1
 5. Click "settings" after enabling the SphinxSearch plugin
 6. Launch the install wizard from within the plugin's settings view
 7. Follow the install steps
 8. Paste your new configuration file into your original `sphinx.conf` file
 9. Index all of the indexes (example: `/usr/bin/indexer --all --config /etc/sphinx/sphinx.conf`)
 10. Start the searchd daemon (example: `/usr/bin/searchd --config /etc/sphinx/sphinx.conf`)
 11. Perform a search on your website using the regular means
 12. Setup a cron job to run the three auto-generated cron files so that new comments/discussions/searches are indexed
    - Check the log file (`sphinx_cron.log`) inside of the plugin's `cron` folder to find any problems during the cron task
    - You may need to run the cron jobs as `sudo` if you see permissions errors in the sphinx log file


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
Provides simple backend for changing some settings.
  * Automatically generated configuration and cron files
  * Enable/Disable widgets and their parameters (max matches, view format)
  * Install FAQ

####Widgets
All of the widgets that display a discussion/thread title will have a tooltip that will display the first xxx amount of words from the original text. To show this text, simply hover over the title for a second to see the discussion body text.

As of v20120805, the following are a list of widgets
#####Advanced Search & result Page
This overrides the current search algorithm and substitutes a more advanced search option.

#####Post Searches
Much like on stackoverflow.com, any new discussion that is being typed into the title box will start sphinx looking for related threads in reference to the new potential thread. A box will appear the input box showing some relevant threads.

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

   *  'Did you Mean' feature on the main search
   *  Add a nice javascript dropdown menu to all of the search boxes that show some quick advanced search options
   *  Add more widgets related to top searches (maybe top searches for this month, week, year, etc)
   *  Improve the autocompete fields and speed of the jquery UI POST. For some reason, it is very slow and I would like to incorporate the stock Vanilla Forum's autocomplete magic, but I had problems doing so....


##FAQ

#####How does Sphinx work?
Sphinx indexes your discussion titles, body, and author names, making them easily searchable
It does not store your text, but rather uses a special data structure to optimize searching
Sphinx is as dedicated indexing/query engine, and therefore can do it a lot better, rather than MYSQL/MyISAM

#####Will this work for me?
Depends...are you on a shared host? If so, this is probably not for you, but talk to your hosting provider about having a daemon run on your server.
If sphinx runs for a just a few seconds/minutes and is then shutdown mysteriously, chances are that your host is killing it. Talk to your hosting provider.

#####What if sphinx is indexing and it shuts down searchd...now what?
Anytime sphinx is indexing, it will shut down all searches temporary (unless you have another instance of searchd setup).

#####What is the indexer and searchd?
These are two separate entities that work together. Indexer indexes your database fields and searchd listens on your server for search requests to query the indexer

#####What's the deal with the cron files?
Sphinx needs to reindex your database here and there to stay current. The 'Main' and 'Delta' index work together to achieve optimal results
You should index 'Main' once in a while, depending on the activity of your forum. Delta should be updated more frequent since it should only update much less than the Main index
Use the cron files to update sphinx during low peak times. Remember, reindex delta often, and main seldom. More info, see section 3.12 of the main sphinx documentation

#####How do I get rid of some of the top searches/tags?
Add the words to your stoplist.txt found in the assests folder of this plugin and then reindex. Over time, you should see these dissappear
Future versions may let you censor this easier, but for now be sure to enable the stopwords feature

##Donate
If you have found this plugin extremely useful, feel free to [donate here](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=kapotchy%40gmail%2ecom&lc=US&item_name=Sphinx%20Search&no_note=0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHostedGuest)

