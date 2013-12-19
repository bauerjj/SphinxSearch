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
        <li><?php echo Anchor('Back To Control Panel', 'plugin/sphinxsearch'); ?></li>
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
<div class="FilterMenu">
    <?php
    $ToggleName = $Settings['Admin']->MainSearchEnable ? T('Disable Main Search') : T('Enable Main Search');
    echo "<div>" . Wrap(Anchor($ToggleName, 'plugin/sphinxsearch/toggle/' . Gdn::Session()->TransientKey() . '/?action=mainsearch', 'SmallButton')) . "</div>";
    ?>
</div>
<?php if ($Settings['Admin']->MainSearchEnable) : ?>
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
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitResultsPage'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of results per page', 'Plugin.SphinxSearch.LimitResultsPage'); ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.MaxMatches'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of max matches before Sphinx quits! MUST ALSO CONFIGURE THIS IN SPHINX.CONF MANUALLY!', 'Plugin.SphinxSearch.MaxMatches'); ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.MaxQueryTime'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Maximum time alloted for each search query (in milliseconds). 0 means forever', 'Plugin.SphinxSearch.MaxQueryTime'); ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->CheckBox('Plugin.SphinxSearch.MainHitBoxEnable', 'HitBox'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Enable the HitBox', 'Plugin.SphinxSearch.MainHitBoxEnable'); ?>
                </td>
            </tr>
        </tbody>
    </table>

<?php endif ?>



<br/>
<div class="FilterMenu">
    <?php
    $ToggleName = $Settings['Admin']->StatsEnable ? T('Disable Stats') : T('Enable Stats');
    echo "<div>" . Wrap(Anchor($ToggleName, 'plugin/sphinxsearch/toggle/' . Gdn::Session()->TransientKey() . '/?action=stats', 'SmallButton')) . "</div>";
    ?>
</div>
<?php if ($Settings['Admin']->StatsEnable) : ?>
    <table class="AltRows">
        <thead>
            <tr>
                <th> Related Searches - Settings</th>
                <th> Description </th>
                <th> View Format </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitRelatedSearches'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of related FULL searches on the main search page (0 to disable)', 'Plugin.SphinxSearch.LimitRelatedSearches'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('test', array('simple'), array('disabled' => 'disabled')) ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitTopKeywords'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of top SINGLE keywords on the main search page (0 to disable)', 'Plugin.SphinxSearch.LimitTopKeywords'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('test', array('simple'), array('disabled' => 'disabled')) ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitTopSearches'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of top FULL searches on the main search page (0 to disable)', 'Plugin.SphinxSearch.LimitTopSearches'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('test', array('simple'), array('disabled' => 'disabled')) ?>
                </td>
            </tr>

        </tbody>
    </table>
    <br/>
<?php endif ?>

<br/>
<div class="FilterMenu">
    <?php
    $ToggleName = $Settings['Admin']->RelatedEnable ? T('Disable Related') : T('Enable Related');
    echo "<div>" . Wrap(Anchor($ToggleName, 'plugin/sphinxsearch/toggle/' . Gdn::Session()->TransientKey() . '/?action=related', 'SmallButton')) . "</div>";
    ?>
</div>
<?php if ($Settings['Admin']->RelatedEnable) : ?>
    <table class="AltRows">
        <thead>
            <tr>
                <th> Related Threads - Settings</th>
                <th> Description </th>
                <th> View Format </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitRelatedThreadsSidebarDiscussion'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of related threads on the sidebar panel with every discussion  (0 to disable)', 'Plugin.SphinxSearch.LimitRelatedThreadsSidebarDiscussion'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('test', array('simple'), array('disabled' => 'disabled')) ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitRelatedThreadsMain'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of related threads on the main search sidebar (0 to disable)', 'Plugin.SphinxSearch.LimitRelatedThreadsMain'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('test', array('simple'), array('disabled' => 'disabled')) ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitRelatedThreadsPost'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of results that pop up when adding a new discussion (0 to disable)', 'Plugin.SphinxSearch.LimitRelatedThreadsPost'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('Plugin.SphinxSearch.RelatedThreadsPostFormat', array('simple' => 'simple', 'classic' => 'classic', 'sleek' => 'sleek', 'table' => 'table')) ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitRelatedThreadsBottomDiscussion'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of related threads on the bottom of each discussion thread (0 to disable)', 'Plugin.SphinxSearch.LimitRelatedThreadsBottomDiscussion'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('Plugin.SphinxSearch.RelatedThreadsBottomDiscussionFormat', array('simple' => 'simple', 'classic' => 'classic', 'sleek' => 'sleek', 'table' => 'table')) ?>
                </td>
            </tr>
        </tbody>
    </table>
    <br/>
<?php endif ?>


<br/>


<br/>
<br/>


<?php echo $this->Form->Close('Save'); ?>

