<?php
if (!defined('APPLICATION'))
    exit();
?>

<style type="text/css" media="screen">
    body{ background:#000;color:#fff;font-size:.9em; }
    .old{ background-color:#246499;}
    .new{ }
    .error{ background-color:#992E36;}

    #Content form ul{
        padding: 2px;
    }

    ol, ul {
        list-style: upper-roman;
        margin-left: 50px;
    }
    .Inner ul{
        list-style: none;
    }
    .Settings{
        list-style: none;
        margin-left: 0px;
    }
    .FootNote{
        font-style: italic;
        font-size: 11px;
        margin-left: 15px;
    }
    div.Info, .DismissMessage{
        line-height: 1.8;
    }
    #Content{
        color: black;
    }

    #Left{
        float: left;
        width: 320px; /*Width of left column*/
        margin-left: -100%;
    }
    #Right{
        margin-left: 320px; /*Set left margin to LeftColumnWidth*/
    }
    #RightWrapper{
        float: left;
        width: 100%;
    }
    #MainWrapper{
    }
    .Inner{
        margin: 10px;
    }
    #messages{
        color: #00FF00;
        background-color: black;
        border: 2px solid silver;
    }
    .Success{
        color: green;
    }
    .Fail{
        color: red;
    }
    .background{
        margin-left: 20px;
    }
</style>

<?php echo $this->Form->Errors(); ?>
<?php echo $this->Form->Open(array('method' => 'post')); ?>
<?php $Settings = $this->Data['Settings'] //Grab all sphinx related settings/status ?>

<div class="Help Aside">
    <?php
    echo '<h2>', T('Need More Help?'), '</h2>';
    echo '<ul>';
    echo '<li>', Anchor(T('FAQ'), 'plugin/sphinxsearch/faq'), '</li>';
    echo '<li>', Anchor(T('Offical Sphinx Documentation'), 'http://sphinxsearch.com/docs/current.html'), '</li>';
    echo '</ul>';
    ?>
</div>
<h1><?php echo T($this->Data['Title']) . ' - ' . $this->Data['PluginVersion']; ?></h1>
<div class="Info">
    <?php echo T($this->Data['PluginDescription']); ?>
</div>


<h3><?php echo 'Quick Links'; ?></h3>
<div class="Info">
    <ol>
        <li><?php echo Anchor('Install Wizard', 'plugin/sphinxsearch/installwizard'); ?></li>
        <li><?php echo Anchor('Settings', 'plugin/sphinxsearch/settings'); ?></li>
        <li><?php echo Anchor('FAQ', 'plugin/sphinxsearch/faq'); ?></li>
    </ol>
</div>
<h3>Control Panel</h3>
<br/>
<div id="ControlPanel">
    <br/>
    <table class="CPanel Searchd">
        <tbody>
            <tr>
                <th class="Desc">Searchd: </th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Status: </td>
                <td> <?php if ($Settings['Status']->SearchdRunning) Success('Running');
    else Fail('Not Running'); ?> </td>
            </tr>
        </tbody>
    </table>
    <br/>

</div>
<br/>

<h3>Changelog</h3>
20140115
<ol>
    <li>Deleted non-working links in the control panel</li>
</ol>
<br>
20140114
<ol>
    <li> Support for v2.1b</li>
    <li> Changed the default search to "Extended" mode</li>
    <li> The quick search options now show syntax help</li>
    <li> Slightly changed the installer to be more user friendly. Cron tasks installs are optional</li>
    <li> Updated the installer instructions</li>
</ol>
<br>
20131210
<ol>
    <li> The indexer/searchd/conf paths are now optional during the install since only the auto generated cron files used those inputs</li>
</ol>
<br>
20131205
<ol>
    <li> Removed the complicated installer. Now all installs must be done before running the plugin</li>
    <li> Removed all non-plugin related configurations from the settings menu. User must edit the generated sphinx.conf file directly</li>
    <li>  Fixed a bug where the number of related threads on the bottom of each discussion was using the limit as inserted in the
        settings page for the sidebar widget. Now the settings work as intended and operate independent of each LIMIT.</li>
    <li>fixed "sleek" to "sleak"</li>
</ol>
<br/>
20130330
<ol>
    <li> Whenever the advanced search is expanded, the div will stay collapsed for subsequent searches until it is toggled</li>
    <li> Fixed a bug where the xx amount of search results were not being reconstructed back to their original ranking order from sphinx. This caused the results within each page to be mixed randomly!</li>
    <li> Fixed a bug where sometimes the results will say "xx results found" but no results actually shown. This is because the default page landing was NOT being set correctly to 1. This is repeatable when a
        previous search on a large page number is then followed by a search that returns a few results on a smaller pagination scale than the previously viewed one. No results will be shown since the GET query string tells sphinx to return the previous search's offset</li>
    <li>Instead of checking if sphinx is installed and ready, the plugin now forces the default search to ALWAYS be sphinx until the plugin is disabled. Any errors should now be spit out on any page that fetches a query from sphinx</li>
    <li>Added a message indicating that apache may not have the correct read/write permissions</li>
</ol>
<br/>
20130214
<ol>
    <li>Fixed a HUGE bug that caused all sphinx searches to also perform a regular MYSQL "LIKE" search!</li>
    <li>Put a big reminder about enabling pretty URL's in the dasbhoard</li>
    <li>Added better debug messages during install wizard and reminders to turn on error reporting</li>
    <li>Added a check to enforce Pretty URL's for the time being</li>
    <li>Now sphinx escapes every search query. Check your charset</li>
    <li>Added default charset for English/Russian</li>
    <li>Added debug info to the main results page. Now Sphinx will spit out any errors in your face!</li>
    <li>Fixed issue where regular users could would not see the suggested threads when starting a new thread </li>
    <li>Fixed queries with any numeric character references in them</li>
    <li>Added link to view stats cron in the install wizard</li>
    <li>Added icon image of the sphinx eye</li>
    <li>Added permissions check for related discussions o main/regular discussions view</li>
    <li>Fixed incorect query string from '?q=' to '?Search=' in the Related threads box on main results page</li>
    <li>Added option for different charsets in sphinx.conf template file</li>
    <li>Added hbf as a live demo that is better than my site as well as link back to main plugin site to readme</li>
    <li>Verified read permission in viewfile @Gillingham</li>
    <li>Fixed example cron files that were not pointing to correct paths @Gillingham</li>
</ol>
<br/>
20130105
<ol>
    <li>Fixed search results not respecting user permissions (added another attribute to sphinx to filter on)</li>
    <li>Updated release file to 2.0.6</li>
    <li>Relocated definitions file to make it easier to edit</li>
    <li>Deleted hard coded statements in config template that would override automatic settings set in plugin's settings page</li>
    <li>Added more locale definitions to hitbox widget</li>
    <li>Fixed incorrect query string in the results page. Any filtering done in the result page would only take affect for that single page! Now all pages are affected (fixed)</li>
    <li>Fixed pagination results which would sometimes render blank results page. Now only 'MaxMatches' amount of results will be displayed (default is 1000 docs)</li>
    <li>Fixed numerous spelling mistakes</li>
</ol>
<br/>
20120912
<ol>
    <li>deleted old debug stuff that caused fatal error when auto completing</li>
    <li>fixed problem with php classes not included...now just include all files in root of plugin </li>
</ol>
<br/>
20120905
<ol>
    <li>Created temporary workaround that fixed non-Roman search phrases from being executed correctly </li>
    <li>Fixed stats cron file location to its actual location</li>
    <li>Fixed the RelatedPost widget from adding a query when it should not be</li>
    <li>Added slight HTML edit to support traditional plugin/theme </li>
</ol>
<br/>
20120807
<ol>
    <li>Added debug table to control panel</li>
    <li>Fixed cron files to index at common times - also corrected file paths and comments</li>
    <li>Deleted "Reload Connections" button...it was useless</li>
</ol>
<br/>
20120806
<ol>
    <li>Added mysql_sock to config</li>
    <li>Added mysql_db to config</li>
    <li>Added localhost entry to wizard</li>
    <li>Fixed FAQ link</li>
    <li>Added an update entry to FAQ</li>
</ol>
<br/>
20120805
<ol>
    <li>Initial Release</li>
</ol>
<br/>

<?php

function Success($Text) {
    echo '<span class="Success">' . $Text . '</span>';
}

function Fail($Text) {
    echo '<span class="Fail">' . $Text . '</span>';
}
?>