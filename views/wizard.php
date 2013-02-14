<?php
if (!defined('APPLICATION'))
    exit();
?>
<script type="text/javascript" charset="utf-8">

    var once = false;

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

    $('#ToggleWizard').click(function(){
        once = false; //restart wizard toggle bit
    });

    function waitForMsg(){
        var WebRoot = $("#WebRoot").val();
        $.ajax({
            dataType: "json",
            type: "GET",
            url: WebRoot+"/plugin/sphinxsearch/InstallPoll",

            async: true, /* If set to non-async, browser shows page as "Loading.."*/
            cache: false,
            timeout:50000, /* Timeout in ms */

            success: function(data){ /* called when request to barge.php completes */
                if(data.Terminal == 'reload' && once != true){
                    once = true; //don't enter here again
                    window.setTimeout(function(){addmsg("new", 'Advancing in 5')},0);
                    window.setTimeout(function(){addmsg("new", 'Advancing in 4')},2000);
                    window.setTimeout(function(){addmsg("new", 'Advancing in 3')},4000);
                    window.setTimeout(function(){addmsg("new", 'Advancing in 2')},6000);
                    window.setTimeout(function(){addmsg("new", 'Advancing in 1')},8000);
                    window.setTimeout(function(){
                        $.ajax({
                            type: 'POST',
                            dataType: 'html',
                            url: WebRoot + "/plugin/sphinxsearch/installwizard",
                            data: "NextAction="+"test"
                            //success: function(msg){alert(msg);}
                        });
                    }, 8000);
                    window.setTimeout(function(){window.location.replace(WebRoot + "/plugin/sphinxsearch/installwizard")},9000);


                }
                else if(once != true)
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

<style>
    .Finish{
        color: green;
        padding-left: 15px;
    }

    #Left{
        float: left;
        width: 320px; /*Width of left column*/
        margin-left: -100%;
    }
    #Right{
        margin-left: 320px; /*Set left margin to LeftColumnWidth*/
    }
    #messages{
        color: #00FF00;
        background-color: black;
        border: 2px solid silver;
    }
    #Status{
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
    .TermWarning{
        color: red;
    }

</style>
<div class="Help Aside">
    <?php
    echo '<h2>', T('Need More Help?'), '</h2>';
    echo '<ul>';
    echo '<li>', Anchor(T('Install FAQ'), 'plugin/sphinxsearch/sphinxsearch/faq'), '</li>';
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
        <li><?php echo Anchor('Back To Control Panel', 'plugin/sphinxsearch'); ?></li>
    </ul>
</div>
<h3>Install Wizard</h3>
<br/>
<br/>
<?php
echo $this->Form->Open(array('id'=>'Form'));
echo $this->Form->Errors();
$Settings = $this->Data('Settings');
?>
<br/>
<div class="FilterMenu">
    <?php
    $ToggleName = $Settings['Wizard']->StartWizard == TRUE ? T('Restart Wizard') : T('Start Wizard');
    echo '<div id="ToggleWizard">' . Wrap(Anchor($ToggleName, 'plugin/sphinxsearch/installwizard/' . Gdn::Session()->TransientKey() . '?action=ToggleWizard', 'SmallButton')) . "</div>";
    echo '<br/>Current Action: <b>' . $this->Data['NextAction'] . '</b>';
    ?>
</div>
<?php if ($Settings['Wizard']->StartWizard) : ?>
    <?php $Disabled = $this->Data['NextAction'] != 'Detection' ? array('Disabled' => 'Disabled') : array(); ?>
    <h1>Step 1: </h1>
    <ul>
        <li><?php
    echo $this->Form->Label('Configuration Prefix:', 'Plugin.SphinxSearch.Prefix');
    echo $this->Form->Textbox('Plugin.SphinxSearch.Prefix', array_merge($Disabled, array('value' => $Settings['Install']->Prefix)));
    ?></li>
        <li><?php
        echo $this->Form->Label('Port', 'Plugin.SphinxSearch.Port');
        echo $this->Form->Textbox('Plugin.SphinxSearch.Port', array_merge($Disabled, array($Disabled, 'value' => $Settings['Install']->Port)));
    ?></li>
        <li><?php
        echo $this->Form->Label('* Host', 'Plugin.SphinxSearch.Host');
        echo $this->Form->Textbox('Plugin.SphinxSearch.Host', array_merge($Disabled, array($Disabled, 'value' => $Settings['Install']->Host)));
    ?></li>
        <li><span style="font-style:italic">* Use "127.0.0.1" (without quotes) to force TCP/IP usage. Recommended to first try "localhost" (without quotes)</span>
        </li>
    </ul>
    <br/>
<?php endif ?>
<?php if ($Settings['Wizard']->Connection) : ?>
    <?php $Disabled = $this->Data['NextAction'] != 'Install' ? array('Disabled' => 'Disabled') : array(); ?>
    <?php $DisabledExisting = $Settings['Wizard']->AutoDetected == FALSE ? array('Disabled' => 'Disabled', 'Default' => 'NotDetected') : array(); ?>
    <?php $InstallColor = $DisabledExisting == array() ? 'green' : 'red'; ?>
    <h1>Step 2: </h1>
    <div id="MainWrapper">
        <div id="RightWrapper">
            <div id="Right">
                <div class="Info">
                    <?php echo $this->Form->Label('Run in background: ', 'Background'); ?>
                    <?php echo $this->Form->RadioList('Background', array(TRUE => 'True', FALSE => 'False'), array('list' => FALSE, 'default' => 'False')) ?>
                    <ul class="Settings">
                        <li class="FootNote">This lets all of the install commands to run in the background. Useful for long operations.If running in background, terminal output is presented below in the black box.</li>
                        <li class="FootNote">Wizard will proceed automatically when finished when running in background!! Do NOT proceed manually!</li>
                    </ul>
                </div>

                <div id="Status">
                </div>
                <div id="messages">
                    <div class="msg">
                        Command Line Output
                    </div>
                </div>
            </div>
        </div>

        <div id="Left">
            <div class="Inner">
                <ul>
                    <li>
                        <?php
                        // JJB added 2.12.2013 - this avoids the radiobutton from switching in later steps after already selecting method
                        if (C('Plugin.SphinxSearch.ManualDetected') == true)
                            $default = 'Manual';
                        else
                            $default = 'NotDetected';
                        echo $this->Form->RadioList('Plugin.SphinxSearch.Detected', array('Manual' => '* Use existing binaries'), array_merge($Disabled, array('Default' => $default)));
                        ?>
                    </li>
                    <li>
                        <?php
                        //never disable this option
                        echo $this->Form->Label('Manual Indexer Path:', 'Plugin.SphinxSearch.ManualIndexerPath');
                        echo $this->Form->Textbox('Plugin.SphinxSearch.ManualIndexerPath', array_merge($Disabled, array(), array('value' => $Settings['Install']->ManualIndexerPath)));
                        ?></li>
                    <li><?php
                    echo $this->Form->Label('Manual Searchd Path', 'Plugin.SphinxSearch.ManualSearchdPath');
                    echo $this->Form->Textbox('Plugin.SphinxSearch.ManualSearchdPath', array_merge($Disabled, array(), array('value' => $Settings['Install']->ManualSearchdPath)));
                        ?></li>
                    <li><?php
                    echo $this->Form->Label('Manual Conf Path', 'Plugin.SphinxSearch.ManualConfPath');
                    echo $this->Form->Textbox('Plugin.SphinxSearch.ManualConfPath', array_merge($Disabled, array(), array('value' => $Settings['Install']->ManualConfPath)));
                        ?></li>


                    <li>
                        <br/>
                    </li>

                    <?php
                    echo $this->Form->RadioList('Plugin.SphinxSearch.Detected', array('NotDetected' => '<span style="color: green">** Install Prepackaged Sphinx</span>'), array_merge($Disabled, array('Default' => $default)));
                    ?>

                    <li><?php
                echo $this->Form->Label(T('Install Path'), 'Plugin.SphinxSearch.InstallPath');
                echo $this->Form->Textbox('Plugin.SphinxSearch.InstallPath', array_merge($Disabled, array('value' => $Settings['Install']->InstallPath)));
                    ?>
                        <span style="font-style:italic">Without the trailing slash</span>
                    </li>
                    <li><br/>
                        <span style="font-style:italic">* Example: /usr/bin/sphinx/searchd</span>
                        <span style="font-style:italic;">** This could take upwards of 20 minutes to install depending on system specs (mostly RAM limitations)</span></li>
                </ul>

            <?php endif ?>
            <br/>
            <?php if ($Settings['Wizard']->StartWizard && !$Settings['Wizard']->Config) : //don't put this in if wizard not started' ?>
                <input type="hidden" id="Form_NextAction" name="Configuration/NextAction" value="<?php echo $this->Data['NextAction'] ?>" />
                <?php
                echo $this->Form->Close('Save and Continue');
                ?>
            <?php endif ?>
        </div>
    </div>
    <?php if ($Settings['Wizard']->Config) : ?>
        <div style="clear: both"></div>
        <h1>Step 3: </h1>
        <br/>
        <ul>
            <li><span>Click the button below to write the config file and CRON tasks</span></li>
        </ul>
        <br/>
    <?php endif ?>

    <?php if ($Settings['Wizard']->Installed) : ?>
        <h3>FINISH</h3>
        <div class="Data">
            <br/>
            <span class="Finish"><?php echo T('Congraduations  Sphinx has been installed successfully!') ?></span>
            <br/>
            <ul>
                <li><?php echo Anchor('*View my custom Sphinx.conf file', 'plugin/sphinxsearch/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=conf', array('target'=>'_blank')); ?></li>
                <li><?php echo Anchor('View my custom main cron file', 'plugin/sphinxsearch/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=maincron', array('target'=>'_blank')); ?></li>
                <li><?php echo Anchor('View my custom delta cron file', 'plugin/sphinxsearch/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=deltacron', array('target'=>'_blank')); ?></li>
                <li><?php echo Anchor('View my custom stats cron file', 'plugin/sphinxsearch/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=statscron', array('target'=>'_blank')); ?></li>

                <li><span style="font-style:italic">* Contains your database username/password</span></li>
            </ul>
        </div>
    <?php endif ?>

    <?php if ($Settings['Wizard']->StartWizard && $Settings['Wizard']->Config) : //don't put this in if wizard not started' ?>
        <input type="hidden" id="Form_NextAction" name="Configuration/NextAction" value="<?php echo $this->Data['NextAction'] ?>" />
        <?php
        if (!$Settings['Wizard']->Installed)
            echo $this->Form->Close('Save and Continue'); else
            echo '<div class="Finish">' . Wrap(Anchor('Return to Control Panel', 'plugin/sphinxsearch', 'SmallButton')) . "</div>";
        ?>
    <?php endif
    ?>
    <br/>