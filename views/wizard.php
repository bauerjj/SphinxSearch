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

    /*Override for the little astrix to line up properly*/
    form ul li span {
        display: inline !important;
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
echo $this->Form->Open(array('id' => 'Form'));
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
            echo $this->Form->Label('Configuration Prefix:' . "<span class='required' style='font-weight: bold; color: #B94A48'>*</span>", 'Plugin.SphinxSearch.Prefix'); ?> <?php
            echo $this->Form->Textbox('Plugin.SphinxSearch.Prefix', array_merge($Disabled, array('value' => $Settings['Install']->Prefix)));
            ?></li>
        <li><span style="font-style:italic">This is used in the sphinx config file to identify between this install and other configurations already present. You can
            typically just leave this as the default setting</span>
        </li>
        <li><?php
            echo $this->Form->Label('Port:' . "<span class='required' style='font-weight: bold; color: #B94A48'>*</span>", 'Plugin.SphinxSearch.Port');
            echo $this->Form->Textbox('Plugin.SphinxSearch.Port', array_merge($Disabled, array($Disabled, 'value' => $Settings['Install']->Port)));
            ?></li>
        <li><span style="font-style:italic">This is the port that sphinx (searchd daemon) listens on for active connections. 9312 is the default</span>
        </li>
        <li><?php
            echo $this->Form->Label('Host:' . "<span class='required' style='font-weight: bold; color: #B94A48'>*</span>", 'Plugin.SphinxSearch.Host');
            echo $this->Form->Textbox('Plugin.SphinxSearch.Host', array_merge($Disabled, array($Disabled, 'value' => $Settings['Install']->Host)));
            ?></li>
        <li><span style="font-style:italic">This is where sphinx (searchd daemon) is running from, NOT where your database necessarily is located</li>
        <li><span style="font-style:italic">Use "127.0.0.1" (without quotes) to force TCP/IP usage. Recommended to first try "localhost" (without quotes)</span>
        </li>
    </ul>
    <br/>
<?php endif ?>
<?php if ($Settings['Wizard']->Connection) : ?>
    <?php $Disabled = $this->Data['NextAction'] != 'Install' ? array('Disabled' => 'Disabled') : array(); ?>
    <?php $DisabledExisting = $Settings['Wizard']->AutoDetected == FALSE ? array('Disabled' => 'Disabled', 'Default' => 'NotDetected') : array(); ?>
    <?php $InstallColor = $DisabledExisting == array() ? 'green' : 'red'; ?>
    <h1>Step 2: </h1>
            <div class="Inner">
                <ul>
                    <li>
                    <?php
                        echo $this->Form->Label('Full Text of existing contents of sphinx.conf '. "<span class='required' style='font-weight: bold; color: #B94A48'>*</span>", 'Plugin.SphinxSearch.ConfText');
                        echo $this->Form->Textbox('Plugin.SphinxSearch.ConfText', array_merge($Disabled, array(), array('value' => $Settings['Install']->ConfText, 'Multiline' => true)));
                        ?></li>
                    <li><span style="font-style:italic">Paste your default sphinx configuration file contents here</li>

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
    <?php if ($Settings['Wizard']->Config) : ?>
    <?php $Disabled = $this->Data['NextAction'] != 'Config' ? array('Disabled' => 'Disabled') : array(); ?>
        <div style="clear: both"></div>
        <h1>Sphinx Config Generation Finish</h1>

        <br/>
            <span class="Finish"><?php echo T('Congratulations! The Sphinx configuration file has been generated successfully!') ?></span>
            <br/>
            <ul><li>
                <?php
                    echo $this->Form->Label('Full Text of NEW sphinx.conf - Copy this into your existing sphinx.conf after making a local copy of original', 'OutputText');
                    echo $this->Form->Textbox('OutputText', array('value' => $Settings['Install']->ConfText, 'Multiline' => true));
                        ?>
                </li>
                <li>Sphinx is now ready to be run. Save your new sphinx.conf and then start the indexer. When indexing is finished, start searchd. Now search!  </li>
                <li>Example linux commands to index and start sphinx daemon (your indexer/searchd paths may differ): </li>
                <li>Start indexing: /user/bin/indexer --all --config /etc/sphinx/sphinx.conf</li>
                <li>Start searchd: /usr/bin/searchd --config /etc/sphinx/sphinx.conf</li>
            </ul>
            <br/>
            <div style="clear: both"></div>
        <h1>Step 3 (Optional)</h1>
        This step writes 3 cron files that will automatically invoke the sphinx indexer at a specific time. This step requires knowledge of your indexer and config path.
            <ul>
             <li>
                        <?php
                        //never disable this option
                        echo $this->Form->Label('Indexer Path:', 'Plugin.SphinxSearch.IndexerPath');
                        echo $this->Form->Textbox('Plugin.SphinxSearch.IndexerPath', array_merge($Disabled, array(), array('value' => $Settings['Install']->IndexerPath)));
                        ?></li>
            <li><span style="font-style:italic">The location of sphinx's indexer (ex: /usr/bin/indexer).</li>
                    <li><?php
                        echo $this->Form->Label('Conf Path:', 'Plugin.SphinxSearch.ConfPath');
                        echo $this->Form->Textbox('Plugin.SphinxSearch.ConfPath', array_merge($Disabled, array(), array('value' => $Settings['Install']->ConfPath)));
                        ?></li>
                    <li><span style="font-style:italic">The location of sphinx's config file (ex: /etc/sphinx/sphinx.conf).</li>

            <li><span>Click the button below to write the config file and CRON tasks</span></li>
        </ul>
        <br/>
    <?php endif ?>

    <?php if ($Settings['Wizard']->Installed) : ?>
        <h3>Cron File Generation Finish</h3>
        <div class="Data">
            <br/>
            <span class="Finish"><?php echo T('Congratulations! The Cron configuration files have been generated successfully!') ?></span>
            <br/>
            <ul>
                <li>The cron files can be viewed here: <b><?php echo PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'cron'?></b></li>
                <li><span style="font-style:italic">Note, the cron files may be invalid depending on your inputs to step 3. Redefine them inside the file itself</li>
            </ul>
        </div>
    <?php endif ?>

    <?php if ($Settings['Wizard']->StartWizard && $Settings['Wizard']->Config) : //don't put this in if wizard not started' ?>
        <input type="hidden" id="Form_NextAction" name="Configuration/NextAction" value="<?php echo $this->Data['NextAction'] ?>" />
        <?php
        if (!$Settings['Wizard']->Installed)
            echo $this->Form->Close('Save and Continue');
        else
            echo '<div class="Finish">' . Wrap(Anchor('Return to Control Panel', 'plugin/sphinxsearch', 'SmallButton')) . "</div>";
        ?>
    <?php endif
    ?>
    <br/>