<?php if (!defined('APPLICATION'))
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
        "<div class='StatusMsg'>"+ status +"</div>"
    );
    }

    function waitForMsg(){
        var WebRoot = $("#WebRoot").val();
        $.ajax({
            dataType: "json",
            type: "GET",
            url: WebRoot+"/plugin/sphinxsearch/test",

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
    .Info .FootNote{
        font-style: italic;
        font-size: 11px;
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
</style>

<?php  echo $this->Form->Errors(); ?>


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
        <li><?php echo Anchor('Statistics', 'plugin/sphinxsearch/installwizard'); ?></li>
        <li><?php echo Anchor('Install FAQ', 'plugin/sphinxsearch/installwizard'); ?></li>
        <li><?php echo Anchor('Vanilla Plugin Website', 'plugin/sphinxsearch/installwizard'); ?></li>
    </ul>
</div>
<h3>Known Issues</h3>
<ol>
    <li>Linux Only!</li>
</ol>
<br/>
<h3>Settings</h3>
<br/>
<div id="MainWrapper">
        <div id="RightWrapper">
            <div id="Right">
                <div id="Status">
                </div>
                    <div id="messages">
                        <div class="msg">
                            Command Line Output
                            <br/>
                            =====Ready=====
                        </div>
                    </div>
            </div>
        </div>
    <div id="Left">
            <div class="Inner">
<div class="Info">
    <ul>
    <?php //echo "<div>".Wrap(Anchor($ToggleName, 'plugin/flagging/toggle/'.Gdn::Session()->TransientKey(), 'SmallButton'))."</div>"; ?>
    <?php echo "<li>" . Wrap(Anchor('Index ALL', 'plugin/sphinxsearch/service/' . Gdn::Session()->TransientKey(), 'SmallButton')) . "</li>"; ?>
    <?php echo "<li>" . Wrap(Anchor('Index main', 'plugin/sphinxsearch/service/' . Gdn::Session()->TransientKey(), 'SmallButton')) . "</li>"; ?>
    <?php echo "<li>" . Wrap(Anchor('Index delta', 'plugin/sphinxsearch/' . Gdn::Session()->TransientKey(), 'SmallButton')) . "</li>"; ?>
<?php echo "<li>" . Wrap(Anchor('Index stats', 'plugin/sphinxsearch/' . Gdn::Session()->TransientKey(), 'SmallButton')) . "</li>"; ?>
    </ul>
    <br/>
    <ul class="Settings">
        <li class="FootNote">Indexing will temporarily stop sphinx</li>
        <li class="FootNote">Indexing `main` may take a long time</li>
    </ul>
    <br/>
    <?php echo "<span>" . Wrap(Anchor('Start searchd', 'plugin/flagging/toggle/' . Gdn::Session()->TransientKey(), 'SmallButton')) . "</span>"; ?>
<?php echo "<span>" . Wrap(Anchor('Stop searchd', 'plugin/flagging/toggle/' . Gdn::Session()->TransientKey(), 'SmallButton')) . "</span>"; ?>
</div>

<?php
echo $this->Form->Open();
?>
<ul>
    <li><?php
echo $this->Form->Label('Search Timeout:', 'Plugin.SphinxSearch.Timeout');
echo $this->Form->Textbox('Plugin.SphinxSearch.Timeout');
?></li>
    <li><?php
echo $this->Form->Label('# of Retries:', 'Plugin.SphinxSearch.RetriesCount');
echo $this->Form->Textbox('Plugin.SphinxSearch.RetriesCount');
?></li>
    <li><?php
echo $this->Form->Label('Delay of retries (ms):', 'Plugin.SphinxSearch.RetriesDelay');
echo $this->Form->Textbox('Plugin.Plugin.SphinxSearch.RetriesDelay');
?></li>
    <li><?php
echo $this->Form->Label('Minimum # of characters to index a word:', 'Plugin.SphinxSearch.MinWordIndexLen');
echo $this->Form->Textbox('Plugin.SphinxSearch.MinWordIndexLen');
?></li>
</ul>

<br/>

<ul>
    <li><?php
echo $this->Form->Label('Search Timeout:', 'Plugin.SphinxSearch.Timeout');
echo $this->Form->Textbox('Plugin.SphinxSearch.Timeout');
?></li>
    <li><?php
echo $this->Form->Label('# of Retries:', 'Plugin.SphinxSearch.RetriesCount');
echo $this->Form->Textbox('Plugin.SphinxSearch.RetriesCount');
?></li>
    <li><?php
echo $this->Form->Label('Delay of retries (ms):', 'Plugin.SphinxSearch.RetriesDelay');
echo $this->Form->Textbox('Plugin.Plugin.SphinxSearch.RetriesDelay');
?></li>
    <li><?php
echo $this->Form->Label('Minimum # of characters to index a word:', 'Plugin.SphinxSearch.MinWordIndexLen');
echo $this->Form->Textbox('Plugin.SphinxSearch.MinWordIndexLen');
?></li>
</ul>

<?php echo $this->Form->Close('Save and Continue'); ?>
            </div>
        </div>
    <div style="clear: both"></div>
<h3>Changelog</h3>
<br/>
2012506
<ol>
    <li>Initial Release</li>
</ol>
<br/>
   </div>
