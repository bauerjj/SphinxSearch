<style>
    td.Input{
        width: 150px;
    }
    .Filler{
        background-color: grey;
    }
    .astrix{
        color: red;
    }
    ol, ul {
        list-style: upper-roman;
        margin-left: 50px;
    }



</style>
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
<h3>Settings</h3>
<br/>
<br/>
<?php
$Settings = $this->Data['Settings'];
//print_r($Settings); die;
echo $this->Form->Open();
echo $this->Form->Errors();
?>
<table class="AltRows">
    <thead>
        <tr>
            <th> Main Search - Settings</th>
            <th> Description </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearchLite.LimitResultsPage'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Number of results per page', 'Plugin.SphinxSearchLite.LimitResultsPage'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearchLite.MaxMatches'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Number of max matches before Sphinx quits! MUST ALSO CONFIGURE THIS IN SPHINX.CONF MANUALLY!', 'Plugin.SphinxSearchLite.MaxMatches'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearchLite.LimitMemberMatches'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Number of matching members to display on the results page', 'Plugin.SphinxSearchLite.LimitMemberMatches'); ?>
            </td>
        </tr>
    </tbody>
</table>




<br/>

<br/>

<?php echo $this->Form->Close('Save'); ?>

