SphinxSearch
============

#### An advanced search plugin for Vanilla Forums based on the Sphinx Search engine 

- [Live Demo from Roberts Space Industries](https://forums.robertsspaceindustries.com/search?Search=search) that serves hundreds of thousands of visits
- [Official Vanilla Plugin Page](http://vanillaforums.org/addon/sphinxsearch-plugin)

Branches:

- [Lite Version](https://github.com/bauerjj/SphinxSearchPlugin/tree/SphinxSearchLite) (DEPRECATED)
- [Integrated Installer Version](https://github.com/bauerjj/SphinxSearchPlugin/tree/WithInstaller) (DEPRECATED)

Donations:

XMR: xxx BTC: xxx

Table of contents
-----------------

 - [How it Works](#how-it-works)
 - [Features](#features)
 - [Requirements](#requirements)
 - [Install](#install)
 - [Contributing](#contributing)
 - [TODO](#todo)
 - [FAQ](#faq)
 - [Changelog](#changelog)

## How it Works
Please see the much more extensive [Official Sphinx Documentation](http://sphinxsearch.com/docs/manual-2.3.2.html)

Sphinx indexes Vanilla's database and stores attributes that are used to filter the results such as author, title, comment count, category name, etc. Sphinx then returns a document ID which is then used in a typical MYSQL query to retrieve the meat and potatoes of it such as last comment ID, category URL code, etc. Almost all searches are returned instantly (<12ms).

Sphinx requires indexing, which is why cron tasks must be used on your server to run periodically. The install wizard will automatically generate this for you. The `Main` index will read the discussions and comments table in your database. The `Delta` will do the same thing, except it will only pickup the ones since the last index was performed. Sphinx will search through both indexes, so you should index 'Delta' frequently and 'Main' during non-peak hours.

The plugin connects to a daemon called `searchd` and queries it using this plugin in conjunction with the SphinxSearch API. You should notice a significant speed increase and search relevance.

## Features
### Search
The following fields are indexed and thus searchable:
 - Thread title
 - Body text
 - Author name

The following is a list of search filters:
 - By Username
 - Titles only
 - Threads with 1 or more replies
 - Certain sub forums
 - Tags (if tagging plugin enabled)
 - Date
 - Order By date, views, replies, relevance or mixture

There are multiple viewing formats available such as table or vertical layout. It supports the sphinx extended syntax such as: `*@title hello @body world*`

### Widgets
The plugin contains a series of widgets in addition to the main search page. These can optionally disabled or hidden from view. Their CSS may need to be changed for your current theme. 

#### Advanced Search & result Page
This adds a more advanced search option.

![adv-search](https://user-images.githubusercontent.com/1715776/50308335-2a034000-0458-11e9-97ba-c9d26e690e76.png)

#### Post Searches
Much like on stackoverflow.com, any new discussion that is being typed into the title box will start sphinx looking for related threads in reference to the new potential thread. A box will appear the input box showing some relevant threads.

![post-searches](https://user-images.githubusercontent.com/1715776/50308352-338ca800-0458-11e9-8561-1a47764cec4d.png)

#### Related Threads and Searches
For each search or discussion thread, the side and/or bottom panel will include a list of related threads based on the query or currently viewed thread.

Main search page:
![main-search-all](https://user-images.githubusercontent.com/1715776/50310322-e5c76e00-045e-11e9-842c-7f0bfd3dffb7.png)

Discussions page:

![related-discussion](https://user-images.githubusercontent.com/1715776/50310331-ebbd4f00-045e-11e9-88cf-a560d5f89cc5.png)

#### Top Keywords and Searches
The plugin will keep a list of all search queries and display the top configurable amount of keywords in the panel on the advanced search landing page.

![top-keywords](https://user-images.githubusercontent.com/1715776/50310335-f1b33000-045e-11e9-9c6d-03af7c77f6b9.png)

#### HitBox
For each search, there is a hitbox that will detail the number of documents that matched each word and number of hits total. A caveat of this is that the analysis of each word comes **BEFORE** any filtering, so the results may differ than what the hitbox says. For instance, 26 documents in the document for the word, *vanilla*, may only result in 8 threads/comments with that same word due to filtering or post processing.

![hit-box](https://user-images.githubusercontent.com/1715776/50310346-f7107a80-045e-11e9-82ce-65215beb4b11.png)

### Settings

The widgets are configurable via the settings page. 

![settings](https://user-images.githubusercontent.com/1715776/50310356-fd065b80-045e-11e9-8267-69ae034b57ab.png)

The layout of the results page and the discussions page that appear in the bottom panel can have their layouts changed. Here are a few examples:

**Discussions**

Classic layout
![discussion-classic](https://user-images.githubusercontent.com/1715776/50310366-042d6980-045f-11e9-9ab6-20ad620c65d3.png)

Simple layout
![discussion-simple](https://user-images.githubusercontent.com/1715776/50310368-08f21d80-045f-11e9-8045-1253fb053308.png)

**Search**

Classic layout
![main-classic](https://user-images.githubusercontent.com/1715776/50310376-0e4f6800-045f-11e9-913d-c22cdfa35f61.png)

Simple layout
![main-table](https://user-images.githubusercontent.com/1715776/50310401-1ad3c080-045f-11e9-9646-eebc3b554bd7.png)

## Requirements

 1. PHP >= 5.3.0
 2. Sphinx Installed = v2.x either via distro or binary. Version 3.x is NOT currently supported
 3. Vanilla Version
    - 20181222 and above: all v2.5 versions
    - 20140114 and above: all v2.1x versions
    - 20131210 and below: v2.0.18.x
 4. Enable URL Rewriting inside your `config.php`: `$Configuration['Garden']['RewriteUrls'] = TRUE`

## Install
First try get it working on your local host before deploying. It is easiest to use your Linux distribution or Windows. Shared hosting will probably restrict sphinx from running properly on your host's servers, but you can try.

Using LAMPP or XAMPP? You must add `sql_sock` to all 3 of the indexes inside the conf file. For example, installing lampp into /opt/lampp requires this in your sphinx.conf: `sql_sock = /opt/lampp/var/mysql/mysql.sock`

### Ubuntu 16/18 

1. `sudo systemctl start sphinxsearch`
2. `git clone git@github.com:bauerjj/SphinxSearch.git`
3. See [Linux Desktop for Lampp](https://docs.joomla.org/Configuring_a_LAMPP_server_for_PHP_development/Linux_desktop) to solve file permissions. `sudo adduser youruser www-data` 
4. `mv ./SphinxSearch <your_webserver/htdocs/plugins/>`
5. Navigate to your Vanilla Forums admin panel and enable the plugin. If it doesn't show up, try deleting the cache at: `htdocs/cache/addon.php`
6. Launch the install wizard from within the plugin's settings view
7. Follow the install steps. You will need to copy and paste your default installed sphinx.conf into the installer. 
8. Paste your new configuration file into your original `sphinx.conf` file
9. Give the `cron` folder write permissions: `chmod a+x htdocs/plugins/SphinxSearch/cron` so you can generate the cron files
10. Index: `sudo /usr/bin/indexer --all --config /etc/sphinxsearch/sphinx.conf`
11. Start the searchd daemon (example: `sudo /usr/bin/searchd --config /etc/sphinx/sphinx.conf`)
12. Perform a search on your website 
13. Any warnings/errors can be seen in the logs: 
   - `sudo tail -f /var/log/sphinxsearch/searchd.log` 
   - `sudo tail -f /var/log/sphinxsearch/query.log`

See also: Here is a [Ubuntu 14 step-by-step](https://open.vanillaforums.com/discussion/30699/howto-install-sphinx-search-on-ubuntu-14-04#latest) from a Vanilla Forums user.


### General Linux 

 1. Download [Sphinx 2.3.2-beta](http://sphinxsearch.com/downloads/archive/) binaries and install it.
 2. Download the latest [SphinxSearchPlugin](https://github.com/bauerjj/SphinxSearch) from github. Older versions can also be found on [Vanilla Plugin Page](https://open.vanillaforums.com/addon/959/sphinxsearch)
 3. Extract the zip file to your webserver's plugin folder. For example, `htdocs/plugins/SphinxSearch`. The folder name is case sensitive.
 4. **Replace the default `sphinxapi.php` file** in the `SphinxSearch` plugin folder with the one from the downloaded archive in step 1. You don't need to do this if you are using v2.3-2
 5. Follow the rest of the Ubuntu guide above, substituting your own paths in


### Windows
TODO

### Common Install Errors

Please first see the [numerous questions](https://open.vanillaforums.com/addon/959/sphinxsearch) that have already been asked on the Vanilla Forums platform. Scroll down the page to see the related questions. Consider enabling debug on your development machine by adding `$Configuration['Debug'] = TRUE;` to your `config.php`. Check your searchd and query.log files as well.

`fsockopen(): unable to connect to localhost::xxxx ....`
First try to start searchd and then check the port again

`"Failed to open log/pid file" when trying to re-index`
You may need to stop searchd using: `/location/to/searchd/searchd --stop`. You can do it manually by killing all instances of searchd by using 'ps' in the command line. If that does not work, delete all files in your ../sphinx/var/log folder and restart

`"Failed to open log/pid file" in the `sphinx_cron.log``
The cron tasks are not running with the correct permissions. Either set the cron jobs to run as `sudo` or the same user as the one who initially started searchd

`"WARNING: no process found by PID xxxx. WARNING: indices NOT rotated".`
This is most likely caused by multiple instances of searchd running. This can happen if you start searchd and then either install a new instance of sphinx or disable the plugin. Solution is to kill al instances of searchd

```
WARNING: index 'vss_main': prealloc: failed to open file '/var/lib/sphinxsearch/data/vss_main.spa': 'Permission denied'; NOT SERVING
WARNING: index 'vss_main': prealloc: failed to open file '/var/lib/sphinxsearch/data/vss_main.sps': 'Permission denied'; NOT SERVING
```
Correct the permissions of these files. This error is not obvious and will usually fail silently meaning searchd and the indexer will operate fine, but you won't see any search results.

`Rewrite URLs not Enabled`
Add this line to your `config.php`: `$Configuration['Garden']['RewriteUrls'] = true;`. You also need to modify your rewrite engine. Here is an example `.htaccess` in the root of your htdocs
```
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php\?p=$1 [QSA,L]
```

## Contributing
Please fork the master branch, solve the issue, and then issue a pull request.

This plugin was built to encourage others to add to its functionality. The widgets are implemented in such a way to make a new addition relatively easy.

To learn how to do so, look at an existing widget. All of them extend the abstract class, *widgets*, which provides common generic routines used by all of the widgets. All of the settings related to sphinx and the sphinx client are passed as construct parameters. This is important!! Only one instance of the sphinx API should be used since only **1** query is made. The widgets add a query to main batch which is then run before each rendered view. An exception to this rule are things that need to be executed from a handler inside of Vanilla such as the PostSearch

The UI can be changed inside the `views` folder.


## TODO
There are multiple improvements that can be made. Here are a list of widgets that can be done, but I have lost track of time:

   *  Improve the ugliness of the search UI. 
   *  Add a nice javascript dropdown menu to the search box just like how the official vanilla forums has.
   *  'Did you Mean...' feature on the main search. Sphinx v3 has this feature.

## FAQ

### How does Sphinx work?
Sphinx indexes your discussion titles, body, and author names, making them easily searchable
It does not store your text, but rather uses a special data structure to optimize searching
Sphinx is as dedicated indexing/query engine, and therefore can do it a lot better, rather than MYSQL/MyISAM

### Will this work for me?
Depends...are you on a shared host? If so, this is probably not for you, but talk to your hosting provider about having a daemon run on your server.
If sphinx runs for a just a few seconds/minutes and is then shutdown mysteriously, chances are that your host is killing it. Talk to your hosting provider.

### What if sphinx is indexing and it shuts down searchd...now what?
Anytime sphinx is indexing, it will shut down all searches temporary (unless you have another instance of searchd setup).

### What is the indexer and searchd?
These are two separate entities that work together. Indexer indexes your database fields and searchd listens on your server for search requests to query the indexer

### What's the deal with the cron files?
Sphinx needs to reindex your database here and there to stay current. The 'Main' and 'Delta' index work together to achieve optimal results
You should index 'Main' once in a while, depending on the activity of your forum. Delta should be updated more frequent since it should only update much less than the Main index
Use the cron files to update sphinx during low peak times. Remember, re-index delta often, and main seldom. More info, see section 3.12 of the main sphinx documentation

### How do I get rid of some of the top searches/tags?
Add the words to your stoplist.txt found in the assets folder of this plugin and then re-index. Over time, you should see these disappear
Future versions may let you censor this easier, but for now be sure to enable the stopwords feature

## Changelog

### 20181222

- Update for Vanilla 2.5/6 and Sphinx 2.3.2-beta. Note, Sphinx 3 will NOT work with this version. Sphinx 3 is still a WIP as of beginning of 2019. Until Ubuntu and other major distros ship Sphinx3, I won't be supporting it. Please make a github issue if you want Sphinx 3 support since major API changes were made. 
- Move the documentation all into the github README
- General cleanup of non-working code

### 20170116

- Fixes security issue of exposing sphinx configuration into the analytics tool
- Fixes cron job generation

### 20140115

- Deleted non-working links in the control panel

### 20140114

- Support for v2.1b
- Changed the default search to "Extended" mode
- The quick search options now show syntax help
- Slightly changed the installer to be more user friendly. Cron tasks installs are optional
- Updated the installer instructions

### 20131210
- The indexer/searchd/conf paths are now optional during the install since only the auto generated cron files used those inputs

### 20131205
- Removed the complicated installer. Now all installs must be done before running the plugin
- Removed all non-plugin related configurations from the settings menu. User must edit the generated sphinx.conf file directly
- Fixed a bug where the number of related threads on the bottom of each discussion was using the limit as inserted in the
        settings page for the sidebar widget. Now the settings work as intended and operate independent of each LIMIT.
- fixed "sleek" to "sleak"

### 20130330

- Whenever the advanced search is expanded, the div will stay collapsed for subsequent searches until it is toggled
- Fixed a bug where the xx amount of search results were not being reconstructed back to their original ranking order from sphinx. This caused the results within each page to be mixed randomly!
- Fixed a bug where sometimes the results will say "xx results found" but no results actually shown. This is because the default page landing was NOT being set correctly to 
   1. This is repeatable when a previous search on a large page number is then followed by a search that returns a few results on a smaller pagination scale than the previously viewed one. No results will be shown since the GET query string tells sphinx to return the previous search's offset
- Instead of checking if sphinx is installed and ready, the plugin now forces the default search to ALWAYS be sphinx until the plugin is disabled. Any errors should now be spit out on any page that fetches a query from sphinx
- Added a message indicating that apache may not have the correct read/write permissions

### 20130214

- Fixed a HUGE bug that caused all sphinx searches to also perform a regular MYSQL "LIKE" search!
- Put a big reminder about enabling pretty URL's in the dashboard
- Added better debug messages during install wizard and reminders to turn on error reporting
- Added a check to enforce Pretty URL's for the time being
- Now sphinx escapes every search query. Check your charset
- Added default charset for English/Russian
- Added debug info to the main results page. Now Sphinx will spit out any errors in your face!
- Fixed issue where regular users could would not see the suggested threads when starting a new thread 
- Fixed queries with any numeric character references in them
- Added link to view stats cron in the install wizard
- Added icon image of the sphinx eye
- Added permissions check for related discussions o main/regular discussions view
- Fixed incorrect query string from '?q=' to '?Search=' in the Related threads box on main results page
- Added option for different charsets in sphinx.conf template file
- Added hbf as a live demo that is better than my site as well as link back to main plugin site to readme
- Verified read permission in viewfile @Gillingham
- Fixed example cron files that were not pointing to correct paths @Gillingham

### 20130105

- Fixed search results not respecting user permissions (added another attribute to sphinx to filter on)
- Updated release file to 2.0.6
- Relocated definitions file to make it easier to edit
- Deleted hard coded statements in config template that would override automatic settings set in plugin's settings page
- Added more locale definitions to hitbox widget
- Fixed incorrect query string in the results page. Any filtering done in the result page would only take affect for that single page! Now all pages are affected (fixed)
- Fixed pagination results which would sometimes render blank results page. Now only 'MaxMatches' amount of results will be displayed (default is 1000 docs)
- Fixed numerous spelling mistakes

### 20120912

- deleted old debug stuff that caused fatal error when auto completing
- fixed problem with php classes not included...now just include all files in root of plugin 

### 20120905

- Created temporary workaround that fixed non-Roman search phrases from being executed correctly 
- Fixed stats cron file location to its actual location
- Fixed the RelatedPost widget from adding a query when it should not be
- Added slight HTML edit to support traditional plugin/theme 

### 20120807

- Added debug table to control panel
- Fixed cron files to index at common times - also corrected file paths and comments
- Deleted "Reload Connections" button...it was useless

### 20120806

- Added mysql_sock to config
- Added mysql_db to config
- Added localhost entry to wizard
- Fixed FAQ link
- Added an update entry to FAQ

### 20120805

- Initial Release

