SphinxSearchLite
============

####A light-weight plugin for the Sphinx Search engine derived from the full-featured SphinxSearch plugin


[Offical Plugin Page](http://vanillaforums.org/addon/sphinxsearchlite-plugin)


This is "Lite" version of the full-featured plugin found here: https://github.com/mcuhq/SphinxSearchPlugin

The development was sponsored by [50sQuiff](http://vanillaforums.org/profile/40383/50sQuiff)

This is drop-in replacement of the typical MYSQL search which is the default Vanilla one.

The primary differences from the regular plugin are as follows:

 - No built-in installer
    - Should now work for both windows/linux...simply install from source or distro on linux or binary if using windows
    - Must input locations of existing searchd/indexer and sphinx.conf file paths
 - All previous widgets are stripped
    - Added a simple member widget which displays usernames similar to the input query
 - Removed the advanced search landing page
 - Search method defaults to [extended syntax](http://sphinxsearch.com/docs/current.html#extended-syntax)
 - Simplified the results page and advanced options drop down

##Donate
If you have found this plugin extremely useful, feel free to [donate here](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=kapotchy%40gmail%2ecom&lc=US&item_name=Sphinx%20Search&no_note=0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHostedGuest)

