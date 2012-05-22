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
    echo '<li>', Anchor(T('Install FAQ'), 'plugin/sphinxsearch/sphinxfaq'), '</li>';
    echo '<li>', Anchor(T('Vanilla Forums Install Thread'), ''), '</li>';
    echo '<li>', Anchor(T('Offical Sphinx Documentation'), 'http://sphinxsearch.com/docs/current.html'), '</li>';
    echo '</ul>';
    ?>
</div>

<h1><?php echo $this->Data('Title') . ' - Install Wizard'; ?></h1>

<?php
echo $this->Form->Open();
echo $this->Form->Errors();
?>
<br/>
<div class="FilterMenu">
    <?php echo Anchor('Back To Main Settings', 'plugin/sphinxsearch'); ?>
    <br/>
    <br/>
    <?php
    echo $this->Data['NextAction'];
    $ToggleName = C('Plugin.SphinxSearch.StartWizard') == TRUE ? T('Close Wizard') : T('Start Wizard');
    echo "<div>" . Wrap(Anchor($ToggleName, 'plugin/sphinxsearch/installwizard/' . Gdn::Session()->TransientKey() . '?action=ToggleWizard', 'SmallButton')) . "</div>";
    ?>
</div>
<?php if (C('Plugin.SphinxSearch.StartWizard', FALSE)) : ?>
    <?php $Disabled = $this->Data['NextAction'] != 'Detection' ? array('Disabled' => 'Disabled') : array(); ?>
    <h1>Step 1: </h1>
    <ul>
        <li><?php
    echo $this->Form->Label('Configuration Prefix:', 'Plugin.SphinxSearch.Prefix');
    echo $this->Form->Textbox('Plugin.SphinxSearch.Prefix', array_merge($Disabled, array('value' => C('Plugin.SphinxSearch.Prefix'))));
    ?></li>
        <li><?php
        echo $this->Form->Label('Port', 'Plugin.SphinxSearch.Port');
        echo $this->Form->Textbox('Plugin.SphinxSearch.Port', array_merge($Disabled, array($Disabled, 'value' => C('Plugin.SphinxSearch.Port'))));
    ?></li>
    </ul>

<?php endif ?>
<?php if (C('Plugin.SphinxSearch.Connection')) : ?>
    <?php $Disabled = $this->Data['NextAction'] != 'Install' ? array('Disabled' => 'Disabled') : array(); ?>
    <?php $DisabledExisting = C('Plugin.SphinxSearch.Detected', FALSE) == FALSE ? array('Disabled' => 'Disabled', 'Default' => 'NotDetected') : array(); ?>
    <?php $InstallColor = $DisabledExisting == array() ? 'green' : 'red'; ?>
    <h1>Step 2: </h1>
    <div id="MainWrapper">
        <div id="RightWrapper">
            <div id="Right">
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
                    <?php
                    echo $this->Form->RadioList('Plugin.SphinxSearch.Detected', array('Detected' => '<span style="color: ' . $InstallColor . '">Install using Existing System Binaries</span>'), $DisabledExisting);
                    ?>
                    <li><?php
                echo $this->Form->Label('Detected Indexer Path:', 'Plugin.SphinxSearch.IndexerPath');
                echo $this->Form->Textbox('Plugin.SphinxSearch.IndexerPath', array_merge($Disabled, $DisabledExisting, array('value' => C('Plugin.SphinxSearch.IndexerPath'))));
                    ?></li>
                    <li><?php
                    echo $this->Form->Label('Detected Searchd Path', 'Plugin.SphinxSearch.SearchdPath');
                    echo $this->Form->Textbox('Plugin.SphinxSearch.SearchdPath', array_merge($Disabled, $DisabledExisting, array('value' => C('Plugin.SphinxSearch.SearchdPath'))));
                    ?></li>
                    <li><?php
                    echo $this->Form->Label('Detected Conf Path', 'Plugin.SphinxSearch.ConfPath');
                    echo $this->Form->Textbox('Plugin.SphinxSearch.ConfPath', array_merge($Disabled, array(), array('value' => C('Plugin.SphinxSearch.ConfPath'))));
                        ?></li>

                    <li><br/>
                        <?php
                        echo $this->Form->RadioList('Plugin.SphinxSearch.Detected', array('Manual' => '* Manually locate paths'), array('Default' => 'NotDetected'));
                        ?>
                    </li>
                    <li>
                        <?php
                        //never disable this option
                        echo $this->Form->Label('Manual Indexer Path:', 'Plugin.SphinxSearch.ManualIndexerPath');
                        echo $this->Form->Textbox('Plugin.SphinxSearch.ManualIndexerPath', array_merge($Disabled, array(), array('value' => C('Plugin.SphinxSearch.ManualIndexerPath'))));
                        ?></li>
                    <li><?php
                    echo $this->Form->Label('Manual Searchd Path', 'Plugin.SphinxSearch.ManualSearchdPath');
                    echo $this->Form->Textbox('Plugin.SphinxSearch.ManualSearchdPath', array_merge($Disabled, array(), array('value' => C('Plugin.SphinxSearch.ManualSearchdPath'))));
                        ?></li>
                    <li><?php
                    echo $this->Form->Label('Manual Conf Path', 'Plugin.SphinxSearch.ManualConfPath');
                    echo $this->Form->Textbox('Plugin.SphinxSearch.ManualConfPath', array_merge($Disabled, array(), array('value' => C('Plugin.SphinxSearch.ManualConfPath'))));
                        ?></li>


                    <li>
                        <br/>
                    </li>

                    <?php
                    echo $this->Form->RadioList('Plugin.SphinxSearch.Detected', array('NotDetected' => '<span style="color: green">** Install Prepackaged Sphinx</span>'), array('Default' => 'NotDetected'));
                    ?>

                    <li><?php
                echo $this->Form->Label(T('Install Path'), 'Plugin.SphinxSearch.InstallPath');
                echo $this->Form->Textbox('Plugin.SphinxSearch.InstallPath', array_merge($Disabled, array('value' => C('Plugin.SphinxSearch.InstallPath'))));
                    ?>
                        <span style="font-style:italic">Without the trailing slash</span>
                    </li>
                    <li><br/>
                        <span style="font-style:italic">* Example: /usr/bin/sphinx/searchd</span>
                        <span style="font-style:italic;">** This could take upwards of 20 minutes to install depending on system specs (mostly RAM limitations)</span></li>
                </ul>

            <?php endif ?>
            <br/>
            <?php if (C('Plugin.SphinxSearch.Installed')) : ?>
                <h3>FINISH</h3>
                <div class="Data">
                    <br/>
                    <span class="Finish"><?php echo T('Congraduations  Sphinx has been installed successfully!') ?></span>
                    <br/>
                    <br/>
                </div>
            <?php endif ?>

            <?php if (C('Plugin.SphinxSearch.StartWizard', FALSE)) : //don't put this in if wizard not started' ?>
                <input type="hidden" id="Form_NextAction" name="Configuration/NextAction" value="<?php echo $this->Data['NextAction'] ?>" />
                <?php
                if (!C('Plugin.SphinxSearch.Installed'))
                    echo $this->Form->Close('Save and Continue'); else
                    echo '<div class="Finish">' . Wrap(Anchor('Return to Settings', 'plugin/sphinxsearch', 'SmallButton')) . "</div>";
                ?>
            <?php endif
            ?>
            <br/>
            <br/>
        </div>
    </div>
</div>