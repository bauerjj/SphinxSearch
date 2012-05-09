<?php if (!defined('APPLICATION'))
    exit(); ?>
<style>
    .Finish{
        color: green;
        padding-left: 15px;
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
    echo $this->Form->Label('Host:', 'Plugin.SphinxSearch.Host');
    echo $this->Form->Textbox('Plugin.SphinxSearch.Host', array_merge($Disabled, array('value' => C('Plugin.SphinxSearch.Host'))));
    ?></li>
        <li><?php
        echo $this->Form->Label('Port', 'Plugin.SphinxSearch.Port');
        echo $this->Form->Textbox('Plugin.SphinxSearch.Port', array_merge($Disabled, array($Disabled, 'value' => C('Plugin.SphinxSearch.Port'))));
        ?></li>
    </ul>

<?php endif ?>
<?php if (C('Plugin.SphinxSearch.Connection')) : ?>
        <?php $Disabled = $this->Data['NextAction'] != 'Install' ? array('Disabled' => 'Disabled') : array(); ?>
    <?php $DisabledExisting = C('Plugin.SphinxSearch.Detected', FALSE) == FALSE? array('Disabled'=>'Disabled', 'Default'=>'NotDetected') : array(); ?>
    <?php $InstallColor = $DisabledExisting == array() ? 'green' : 'red'; ?>
    <h1>Step 2: </h1>
    <ul>
        <?php
        echo $this->Form->RadioList('Plugin.SphinxSearch.Detected', array('Detected' => '<span style="color: '.$InstallColor.'">Install using Existing System Binaries</span>'), $DisabledExisting);
        ?>
        <li><?php
        echo $this->Form->Label('Detected Indexer Path:', 'Plugin.SphinxSearch.IndexerPath');
        echo $this->Form->Textbox('Plugin.SphinxSearch.IndexerPath', array_merge($Disabled, $DisabledExisting,array('value' => C('Plugin.SphinxSearch.IndexerPath'))));
        ?></li>
        <li><?php
        echo $this->Form->Label('Detected Searchd Path', 'Plugin.SphinxSearch.SearchdPath');
        echo $this->Form->Textbox('Plugin.SphinxSearch.SearchdPath', array_merge($Disabled, $DisabledExisting,array('value' => C('Plugin.SphinxSearch.SearchdPath'))));
        ?></li>

        <li><br/>
        <?php
    echo $this->Form->RadioList('Plugin.SphinxSearch.Detected', array('Manual' => '* Manually locate paths'), array('Default'=>'NotDetected'));
    ?>
        </li>
        <li>
        <?php //never disable this option
        echo $this->Form->Label('Manual Indexer Path:', 'Plugin.SphinxSearch.ManualIndexerPath');
        echo $this->Form->Textbox('Plugin.SphinxSearch.ManualIndexerPath', array_merge($Disabled, array(),array('value' => C('Plugin.SphinxSearch.ManualIndexerPath'))));
        ?></li>
        <li><?php
        echo $this->Form->Label('Manual Searchd Path', 'Plugin.SphinxSearch.ManualSearchdPath');
        echo $this->Form->Textbox('Plugin.SphinxSearch.ManualSearchdPath', array_merge($Disabled, array(),array('value' => C('Plugin.SphinxSearch.ManualSearchdPath'))));
        ?></li>


        <li>
            <br/>
            </li>

    <?php
    echo $this->Form->RadioList('Plugin.SphinxSearch.Detected', array('NotDetected' => '<span style="color: green">** Install Prepackaged Sphinx</span>'), array('Default'=>'NotDetected'));
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


<?php if (C('Plugin.SphinxSearch.StartWizard', FALSE)) : //don't put this in if wizard not started'?>
    <input type="hidden" id="Form_NextAction" name="Configuration/NextAction" value="<?php echo $this->Data['NextAction'] ?>" />
    <?php if (!C('Plugin.SphinxSearch.Installed')) echo $this->Form->Close('Save and Continue'); else
        echo '<div class="Finish">' . Wrap(Anchor('Return to Settings', 'plugin/sphinxsearch', 'SmallButton')) . "</div>"; ?>
    <?php
 endif ?>
<br/>
<br/>