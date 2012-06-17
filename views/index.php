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
</style>

<?php echo $this->Form->Errors(); ?>
<?php $Settings = $this->Data['Settings'] //Grab all sphinx related settings/status ?>

<div class="Help Aside">
    <?php
    echo '<h2>', T('Need More Help?'), '</h2>';
    echo '<ul>';
    echo '<li>', Anchor(T('Install FAQ'), 'plugin/sphinxfaq'), '</li>';
    echo '<li>', Anchor(T('Vanilla Forums Install Thread'), ''), '</li>';
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
    <ul>
        <li><?php echo Anchor('Install Wizard', 'plugin/sphinxsearch/installwizard'); ?></li>
        <li><?php echo Anchor('Settings', 'plugin/sphinxsearch/installwizard'); ?></li>
        <li><?php echo Anchor('Install FAQ', 'plugin/sphinxsearch/installwizard'); ?></li>
        <li><?php echo Anchor('Vanilla Plugin Website', 'plugin/sphinxsearch/installwizard'); ?></li>
    </ul>
</div>
<h3>Requirements</h3>
<ol>
    <li>Linux Only!</li>
    <li>PHP >= 5.3.0</li>
    <li>Shell Access</li>
    <li>Spawn a Daemon (searchd)</li>
    <li>Port Forwarding</li>
</ol>
<br/>
<h3>Control Panel</h3>
<br/>
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
            <td><?php if ($Settings['Status']->IndexerFound) Success('Installed'); else Fail('Not Installed'); ?></td>
            <td><?php if ($Settings['Status']->SearchdFound) Success('Installed'); else Fail('Not Installed'); ?></td>
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
                <td class="Desc"># of Docs:</td>
                <td><?php echo Gdn_Format::BigNumber($Settings['Status']->IndexerMainTotal) ?></td>
                <td><?php echo Gdn_Format::BigNumber($Settings['Status']->IndexerDeltaTotal) ?></td>
                <td><?php echo Gdn_Format::BigNumber($Settings['Status']->IndexerStatsTotal) ?></td>
            </tr>
            <tr>
                <td class="Desc">Last Index Time:</td>
                <td><?php echo Gdn_Format::FuzzyTime($Settings['Status']->IndexerMainLast) ?></td>
                <td><?php echo $Settings['Status']->IndexerDeltaLast == '---' ? $Settings['Status']->IndexerDeltaLast :  Gdn_Format::FuzzyTime($Settings['Status']->IndexerDeltaLast)?></td>
                <td><?php echo $Settings['Status']->IndexerStatsLast ?></td>
            </tr>
            <tr>
                <td class="Desc">Actions:  </td>
                <td>
                    <?php echo Wrap(Anchor('Index Main', 'plugin/sphinxsearch/service?Action=IndexMain', 'SmallButton')) ?>
                    <?php echo Wrap(Anchor('Reload Count', 'plugin/sphinxsearch/service?Action=ReloadMain', 'SmallButton')) ?>
                </td>
                <td>
                    <?php echo Wrap(Anchor('Index delta', 'plugin/sphinxsearch/service?Action=IndexDelta', 'SmallButton')) ?>
                    <?php echo Wrap(Anchor('Reload Count', 'plugin/sphinxsearch/serviceservice?Action=ReloadDelta', 'SmallButton')) ?>
                </td>
                <td>
                    <?php echo Wrap(Anchor('Index stats', 'plugin/sphinxsearch/service?Action=IndexStats', 'SmallButton')) ?>
                    <?php echo Wrap(Anchor('Reload Count', 'plugin/sphinxsearch/service?Action=ReloadStats', 'SmallButton')) ?>
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
                <td> <?php if ($Settings['Status']->SearchdStatus) Success('Running'); else Fail('Not Running'); ?> </td>
                <td><?php if ($Settings['Status']->SearchdPortStatus) Success($Settings['Install']->Port); else Fail($Settings['Install']->Port); ?></td>
                <td><?php echo Gdn_Format::BigNumber($Settings['Status']->SearchdConnections) ?></td>
            </tr>
            <tr>
                <td class="Desc">Actions: </td>
                <td><?php echo "<span>" . Wrap(Anchor('Start searchd', 'plugin/sphinxsearch/service/?Action=StartSearchd', 'SmallButton')) . "</span>"; ?>
                    <?php echo "<span>" . Wrap(Anchor('Stop searchd', 'plugin/sphinxsearch/service/?Action=StopSearchd', 'SmallButton')) . "</span>"; ?>
                </td>
                <td> <?php echo Wrap(Anchor('Check Port', 'plugin/sphinxsearch/service/?Action=CheckPort', 'SmallButton')) ?></td>
                <td><?php echo Wrap(Anchor('Reload', 'plugin/sphinxsearch/service/?Action=ReloadConnnections' . Gdn::Session()->TransientKey(), 'SmallButton')) ?></td>
            </tr>

        </tbody>
    </table>


</div>
<br/>
<br/>
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
<br/>
2012506
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