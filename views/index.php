<?php if (!defined('APPLICATION'))
    exit(); ?>

<style>

ol, ul {
    list-style: upper-roman;
    margin-left: 50px;
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


</style>

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
<h1><?php echo T($this->Data['Title']). ' - '.$this->Data['PluginVersion']; ?></h1>
<div class="Info">
   <?php echo T($this->Data['PluginDescription']); ?>
</div>
<h3><?php echo 'Quick Links'; ?></h3>
<div class="Info">
    <ol>
        <li><?php echo Anchor('Install Wizard', 'plugin/sphinxsearch/installwizard'); ?></li>
        <li><?php echo Anchor('Statistics', 'plugin/sphinxsearch/installwizard'); ?></li>
        <li><?php echo Anchor('Install FAQ', 'plugin/sphinxsearch/installwizard'); ?></li>
        <li><?php echo Anchor('Vanilla Plugin Website', 'plugin/sphinxsearch/installwizard'); ?></li>
    </ol>
</div>
<h3>Known Issues</h3>
<ol>
    <li>Linux Only!</li>
</ol>
<br/>
<h3>Settings</h3>
<br/>
<div class="Info">
<?php //echo "<div>".Wrap(Anchor($ToggleName, 'plugin/flagging/toggle/'.Gdn::Session()->TransientKey(), 'SmallButton'))."</div>"; ?>
<?php echo "<span>".Wrap(Anchor('Index ALL', 'plugin/flagging/toggle/'.Gdn::Session()->TransientKey(), 'SmallButton EnableAddon'))."</span>"; ?>
<?php echo "<span>".Wrap(Anchor('Index main', 'plugin/flagging/toggle/'.Gdn::Session()->TransientKey(), 'SmallButton'))."</span>"; ?>
<?php echo "<span>".Wrap(Anchor('Index delta', 'plugin/flagging/toggle/'.Gdn::Session()->TransientKey(), 'SmallButton'))."</span>"; ?>
<?php echo "<span>".Wrap(Anchor('Index stats', 'plugin/flagging/toggle/'.Gdn::Session()->TransientKey(), 'SmallButton'))."</span>"; ?>
        <br/>
        <ul class="Settings">
        <li class="FootNote">Indexing will temporarily stop sphinx</li>
        <li class="FootNote">Indexing `main` may take a long time</li>
        </ul>
    <br/>
<?php echo "<span>".Wrap(Anchor('Start searchd', 'plugin/flagging/toggle/'.Gdn::Session()->TransientKey(), 'SmallButton'))."</span>"; ?>
<?php echo "<span>".Wrap(Anchor('Stop searchd', 'plugin/flagging/toggle/'.Gdn::Session()->TransientKey(), 'SmallButton'))."</span>"; ?>
</div>

<?php
echo $this->Form->Open();
echo $this->Form->Errors();
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

<h3>Changelog</h3>
<br/>
2012506
<ol>
    <li>Initial Release</li>
</ol>
<br/>
