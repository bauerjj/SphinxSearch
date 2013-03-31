<?php
if (!defined('APPLICATION'))
    exit();
?>

<script type="text/javascript" charset="utf-8">
    function addmsg(type, msg){
        $("#messages").html(
        "<div class='msg Inner "+ type +"'>"+ msg +"</div>"
    );
    }

    function updatestatus(status){
        $("#Status").html(
        "<div class='StatusMsg'>Status: "+ status +"</div>"
    );
    }

    function waitForMsg(){
        var WebRoot = $("#WebRoot").val();
        $.ajax({
            dataType: "json",
            type: "GET",
            url: WebRoot+"/plugin/sphinxsearch/ServicePoll",

            async: true, /* If set to non-async, browser shows page as "Loading.."*/
            cache: false,
            timeout:50000, /* Timeout in ms */

            success: function(data){ /* called when request to barge.php completes */
                addmsg("new", data.Terminal); /* Add response to a .msg div (with the "new" class)*/
                updatestatus(data.Status);
                setTimeout(
                'waitForMsg()', /* Request next message */
                1000 /* ..after 1 seconds */
            );
            },
            error: function(XMLHttpRequest, textStatus, errorThrown){
                addmsg("error", textStatus + " (" + errorThrown + ")");
                setTimeout(
                'waitForMsg()', /* Try again after.. */
                "15000"); /* milliseconds (15seconds) */
            }
        });
    };

    $(document).ready(function(){
        waitForMsg(); /* Start the inital request */
    });
</script>

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
<h3>Requirements</h3>
<div class="Info">
    <ol>
        <li>Linux Only!</li>
        <li>PHP >= 5.3.0</li>
        <li>Shell Access</li>
        <li>Spawn a Daemon (searchd)</li>
        <li>Port Forwarding</li>
    </ol>
</div>
<h3>Control Panel</h3>
<br/>
<?php echo $this->Form->Label('Run in background: ', 'Background', array('class' => 'background')); ?>
<?php echo $this->Form->RadioList('Background', array(TRUE => 'True', FALSE => 'False'), array('list' => FALSE, 'default' => 'False')) ?>
<ul class="Settings">
    <li class="FootNote">This lets all of the index commands to run in the background. Useful for long operations and identifying warnings</li>
    <li class="FootNote">If running in background, terminal output is presented below in the black box</li>
</ul>
<table class="CPanel Overall">
    <tbody>
        <tr>
            <th class="Desc">General: </th>
            <th>Indexer</th>
            <th>Searchd</th>
            <th>Config</th>
            <th>Uptime</th>
            <th>Total Queries</th>
            <th>Maxed Out</th>


        </tr>
        <tr>
            <td class="Desc">Status: </td>
            <td><?php if ($Settings['Status']->IndexerFound) Success('Found'); else Fail('Not Installed'); ?></td>
            <td><?php if ($Settings['Status']->SearchdFound) Success('Found'); else Fail('Not Installed'); ?></td>
            <td><?php if ($Settings['Status']->ConfFound) Success('Found'); else Fail('Not Found'); ?></td>
            <td><?php echo Gdn_Format::Seconds($Settings['Status']->Uptime) ?></td>
            <td><?php echo Gdn_Format::BigNumber($Settings['Status']->TotalQueries) ?></td>
            <td><?php echo Gdn_Format::BigNumber($Settings['Status']->MaxedOut) ?></td>
        </tr>

    </tbody>
</table>
<br/>

<div id="ControlPanel">
    <table class="CPanel Index">
        <tbody>
            <tr>
                <th class="Desc">Indexer:</th>
                <th>Main</th>
                <th>Delta</th>
                <th>Stats</th>
            </tr>
            <tr>
                <td class="Desc">Count of Docs:</td>
                <td>
                    <?php echo Gdn_Format::BigNumber($Settings['Status']->IndexerMainTotal) ?>
                    <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Reload Main')); ?>
                </td>
                <td>
                    <?php echo Gdn_Format::BigNumber($Settings['Status']->IndexerDeltaTotal) ?>
                    <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Reload Delta')); ?>
                </td>
                <td>
                    <?php echo Gdn_Format::BigNumber($Settings['Status']->IndexerStatsTotal) ?>
                    <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Reload Stats')); ?>
                </td>
            </tr>
            <tr>
                <td class="Desc">Last Index Time:</td>
                <td><?php echo $Settings['Status']->IndexerMainLast == '---' ? '----' : Gdn_Format::FuzzyTime($Settings['Status']->IndexerMainLast) ?></td>
                <td><?php echo $Settings['Status']->IndexerDeltaLast == '---' ? '----' : Gdn_Format::FuzzyTime($Settings['Status']->IndexerDeltaLast) ?></td>
                <td><?php echo $Settings['Status']->IndexerStatsLast == '---' ? '----' : Gdn_Format::FuzzyTime($Settings['Status']->IndexerStatsLast) ?></td>
            </tr>
        <td class="Desc">cron Files: </td>
        <td><?php echo Anchor(T('cron file'), 'plugin/sphinxsearch/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=maincron', array('target' => '_blank')) ?>
            <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Write Main Cron')); ?>

        </td>
        <td><?php echo Anchor(T('cron file'), 'plugin/sphinxsearch/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=deltacron', array('target' => '_blank')) ?>
            <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Write Delta Cron')); ?>

        </td>
        <td><?php echo Anchor(T('cron file'), 'plugin/sphinxsearch/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=statscron', array('target' => '_blank')) ?>
            <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Write Stats Cron')); ?></td>
        </tr>
        <tr>
            <td class="Desc">Actions:  </td>
            <td>
                <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Index Main')); ?>
            </td>
            <td>
                <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Index Delta')); ?>
            </td>
            <td>
                <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Index Stats')); ?>
            </td>
        </tr>

        </tbody>
    </table>
    <ul class="Settings">
        <li class="FootNote">Indexing will temporarily stop sphinx</li>
        <li class="FootNote">Indexing `main` may take a long time</li>
    </ul>
    <br/>
    <table class="CPanel Searchd">
        <tbody>
            <tr>
                <th class="Desc">Searchd: </th>
                <th>Status</th>
                <th>Port</th>
                <th>Connections</th>
            </tr>
            <tr>
                <td>Status: </td>
                <td> <?php if ($Settings['Status']->SearchdRunning) Success('Running'); else Fail('Not Running'); ?> </td>
                <td><?php if ($Settings['Status']->SearchdPortStatus) Success($Settings['Install']->Port); else Fail($Settings['Install']->Port); ?></td>
                <td><?php echo Gdn_Format::BigNumber($Settings['Status']->SearchdConnections) ?></td>
            </tr>
            <tr>
                <td class="Desc">Actions: </td>
                <td> <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Start Searchd')); ?>
                    <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Stop Searchd')); ?>
                <td> <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Check Port')); ?></td>
                <td>----</td>
            </tr>

        </tbody>
    </table>
    <br/>
    <table class="CPanel Searchd">
        <tbody>
            <tr>
                <th class="Desc">Config: </th>
                <th>Configuration </th>
            </tr>
            <tr>
                <td>Actions:</td>
                <td> <?php echo Anchor('config file', 'plugin/sphinxsearch/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=conf', array('target' => '_blank')); ?>
                    <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Write Config')); ?>
                </td>
            </tr>
        </tbody>
    </table>
    <br/>
    <br/>
    <table class="CPanel Searchd">
        <tbody>
            <tr>
                <th class="Desc">Debug: </th>
                <th>Kill </th>
                <th>Manual Override</th>
                <th>Search Log</th>
                <th>Query Log</th>
                <th>Cron Log</th>

            </tr>
            <tr>
                <td>Actions:</td>
                <td>
                    <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Kill Searchd(s)')); ?>
                </td>
                <td>
                    <?php if ($Settings['Status']->EnableSphinxSearch) Success('Sphinx Enabled'); else Fail('Sphinx Disabled'); ?>
                    <?php echo $this->Form->Button('Action', array('class' => 'SmallButton', 'value' => 'Toggle Sphinx Search')); ?>
                </td>
                <td>
                    <?php echo Anchor(T('search log'), 'plugin/sphinxsearch/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=searchlog', array('target' => '_blank')) ?>
                </td>
                 <td>
                    <?php echo Anchor(T('query log'), 'plugin/sphinxsearch/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=querylog', array('target' => '_blank')) ?>
                </td>
                 <td>
                    <?php echo Anchor(T('cron log'), 'plugin/sphinxsearch/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=cronlog', array('target' => '_blank')) ?>
                </td>
            </tr>
        </tbody>
    </table>
    <ul class="Settings">
<li class="FootNote">Kill Searchd is useful for when multiple instances of searchd are running - common sideffect of this is error: WARNING: no process found by PID xxxx </li>
<li class="FootNote">Manual override will either enable sphinx over the default (if searchd is running), or disable sphinx and let the default run (regardless of the state of searchd)</li>
</ul>

</div>
<br/>
<div id="Status">Status: Idle
</div>
<div id="messages">
    <div class="msg">
        Command Line Output
        <br/>
        =====Ready=====
    </div>
</div>
<br/>

<h3>Changelog</h3>

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

<?php echo $this->Form->Close() ?>

<?php

function Success($Text) {
    echo '<span class="Success">' . $Text . '</span>';
}

function Fail($Text) {
    echo '<span class="Fail">' . $Text . '</span>';
}
?>