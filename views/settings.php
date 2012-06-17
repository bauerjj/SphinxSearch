
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

