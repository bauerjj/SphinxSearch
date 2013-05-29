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
    echo '<li>', Anchor(T('FAQ'), 'plugin/sphinxsearchlite/faq'), '</li>';
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
        <li><?php echo Anchor('Install Wizard', 'plugin/sphinxsearchlite/installwizard'); ?></li>
        <li><?php echo Anchor('Settings', 'plugin/sphinxsearchlite/settings'); ?></li>
        <li><?php echo Anchor('FAQ', 'plugin/sphinxsearchlite/faq'); ?></li>
    </ol>
</div>
<h3>Requirements</h3>
<div class="Info">
    <ol>
        <li>Manual install via distro, yourself, or installer (windows)</li>
        <li>PHP >= 5.3.0</li>
    </ol>
</div>
<h3>Control Panel</h3>
<br/>
<div id="ControlPanel">
    <table class="CPanel Index">
        <tbody>
            <tr>
                <th class="Desc">Indexer:</th>
                <th>Main</th>
                <th>Delta</th>
            </tr>
        <td class="Desc">cron Files: </td>
        <td><?php echo Anchor(T('cron file'), 'plugin/sphinxsearchlite/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=maincron', array('target' => '_blank')) ?>

        </td>
        <td><?php echo Anchor(T('cron file'), 'plugin/sphinxsearchlite/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=deltacron', array('target' => '_blank')) ?>

        </td>
        </tr>
        </tbody>
    </table>
    <br/>
    <table class="CPanel Searchd">
        <tbody>
            <tr>
                <th class="Desc">Searchd: </th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Status: </td>
                <td> <?php if ($Settings['Status']->SearchdRunning) Success('Running'); else Fail('Not Running'); ?> </td>
            </tr>
        </tbody>
    </table>
    <br/>
    <table class="CPanel Searchd">
        <tbody>
            <tr>
                <th class="Desc">Config: </th>
                <th>Configuration </th>
                <th>Search Log</th>
                <th>Query Log</th>
                <th>Cron Log</th>
            </tr>
            <tr>
                <td>Status:</td>
                <td> <?php echo Anchor('config file', 'plugin/sphinxsearchlite/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=conf', array('target' => '_blank')); ?>
                </td>
                <td>
                    <?php echo Anchor(T('search log'), 'plugin/sphinxsearchlite/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=searchlog', array('target' => '_blank')) ?>
                </td>
                 <td>
                    <?php echo Anchor(T('query log'), 'plugin/sphinxsearchlite/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=querylog', array('target' => '_blank')) ?>
                </td>
                 <td>
                    <?php echo Anchor(T('cron log'), 'plugin/sphinxsearchlite/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=cronlog', array('target' => '_blank')) ?>
                </td>
            </tr>
        </tbody>
    </table>
    <br/>
    <br/>

</div>
<br/>

<h3>Changelog</h3>
20130529
<ol>
    <li>Install wizard requires you to paste your stock sphinx.conf into a text box along with the paths of indexer and searchd (but not validate their existence).<br/>
        An automatically generated sphinx.conf will then be printed out for you to copy over the original (be sure to backup). <br/>
        This should fix any read/write permission problems that would typically produce errors like "Not Detected" for files that actually did exist.
    </li>

</ol>
<br/>
20130523
<ol>
    <li>Added member search box to advanced drop-down menu. Be sure to enable star syntax in your sphinx.conf to search<br/>
    for all posts from a specific member. Supports members with spaces in their username. Only one user is searched against.
    </li>

</ol>
<br/>
20130301
<ol>
    <li>Initial</li>
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