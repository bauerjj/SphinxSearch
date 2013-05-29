<?php
if (!defined('APPLICATION'))
    exit();
?>
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
    echo '<li>', Anchor(T('Install FAQ'), 'plugin/sphinxsearchlite/sphinxsearch/faq'), '</li>';
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
        <li><?php echo Anchor('Back To Control Panel', 'plugin/sphinxsearchlite'); ?></li>
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
    echo '<div id="ToggleWizard">' . Wrap(Anchor($ToggleName, 'plugin/sphinxsearchlite/installwizard/' . Gdn::Session()->TransientKey() . '?action=ToggleWizard', 'SmallButton')) . "</div>";
    echo '<br/>Current Action: <b>' . $this->Data['NextAction'] . '</b>';
    ?>
</div>
<?php if ($Settings['Wizard']->StartWizard) : ?>
    <?php $Disabled = $this->Data['NextAction'] != 'Detection' ? array('Disabled' => 'Disabled') : array(); ?>
    <h1>Step 1: </h1>
    <ul>
        <li><?php
    echo $this->Form->Label('Configuration Prefix:', 'Plugin.SphinxSearchLite.Prefix');
    echo $this->Form->Textbox('Plugin.SphinxSearchLite.Prefix', array_merge($Disabled, array('value' => $Settings['Install']->Prefix)));
    ?></li>
        <li><?php
        echo $this->Form->Label('Port', 'Plugin.SphinxSearchLite.Port');
        echo $this->Form->Textbox('Plugin.SphinxSearchLite.Port', array_merge($Disabled, array($Disabled, 'value' => $Settings['Install']->Port)));
    ?></li>
        <li><?php
        echo $this->Form->Label('* Host', 'Plugin.SphinxSearchLite.Host');
        echo $this->Form->Textbox('Plugin.SphinxSearchLite.Host', array_merge($Disabled, array($Disabled, 'value' => $Settings['Install']->Host)));
    ?></li>
        <li><span style="font-style:italic">* Use "127.0.0.1" (without quotes) to force TCP/IP usage. Recommended to first try "localhost" (without quotes)</span>
        </li>
        <li><span style="font-style:italic">* This is where searchd is running from, NOT where your database necessarily is located</li>
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
                <div id="Status">
                </div>
                <div id="messages">
                </div>
            </div>
        </div>

        <div id="Left">
            <div class="Inner">
                <ul>
                    <li>
                        <?php
                        //never disable this option
                        echo $this->Form->Label('Indexer Path:', 'Plugin.SphinxSearchLite.IndexerPath');
                        echo $this->Form->Textbox('Plugin.SphinxSearchLite.IndexerPath', array_merge($Disabled, array(), array('value' => $Settings['Install']->IndexerPath)));
                        ?></li>
                    <li><?php
                    echo $this->Form->Label('Searchd Path', 'Plugin.SphinxSearchLite.SearchdPath');
                    echo $this->Form->Textbox('Plugin.SphinxSearchLite.SearchdPath', array_merge($Disabled, array(), array('value' => $Settings['Install']->SearchdPath)));
                        ?></li>
                    <li><?php
                    echo $this->Form->Label('sphinx.conf Path', 'Plugin.SphinxSearchLite.ConfPath');
                    echo $this->Form->Textbox('Plugin.SphinxSearchLite.ConfPath', array_merge($Disabled, array(), array('value' => $Settings['Install']->ConfPath)));
                        ?></li>
                    <li><?php
                    echo $this->Form->Label('Full Text of existing contents of sphinx.conf', 'Plugin.SphinxSearchLite.ConfText');
                    echo $this->Form->Textbox('Plugin.SphinxSearchLite.ConfText', array_merge($Disabled, array(), array('value' => $Settings['Install']->ConfText, 'Multiline' => true)));
                        ?></li>
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
                <li><?php
                    echo $this->Form->Label('Full Text of NEW sphinx.conf - Copy this into your existing sphinx.conf after making a local copy of original', 'OutputText');
                    echo $this->Form->Textbox('OutputText', array('value' => $Settings['Install']->ConfText, 'Multiline' => true));
                        ?></li>
                <li><?php echo Anchor('View my custom main cron file', 'plugin/sphinxsearchlite/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=maincron', array('target'=>'_blank')); ?></li>
                <li><?php echo Anchor('View my custom delta cron file', 'plugin/sphinxsearchlite/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=deltacron', array('target'=>'_blank')); ?></li>
                <li><?php echo Anchor('View my custom stats cron file', 'plugin/sphinxsearchlite/viewfile/' . Gdn::Session()->TransientKey() . '?action=viewfile&file=statscron', array('target'=>'_blank')); ?></li>

            </ul>
        </div>
    <?php endif ?>

    <?php if ($Settings['Wizard']->StartWizard && $Settings['Wizard']->Config) : //don't put this in if wizard not started' ?>
        <input type="hidden" id="Form_NextAction" name="Configuration/NextAction" value="<?php echo $this->Data['NextAction'] ?>" />
        <?php
        if (!$Settings['Wizard']->Installed)
            echo $this->Form->Close('Save and Continue'); else
            echo '<div class="Finish">' . Wrap(Anchor('Return to Control Panel', 'plugin/sphinxsearchlite', 'SmallButton')) . "</div>";
        ?>
    <?php endif
    ?>
    <br/>