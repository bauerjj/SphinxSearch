<?php if (!defined('APPLICATION')) exit(); ?>
<div class="Tabs SearchTabs WithPanel"> <!-- WithPanel is used in the traditional plugin/theme ONLY!! -->
    <?php
     $or = GetIncomingValue('or') == '' ? 'Relevance' : GetIncomingValue('mem') ; //default order to Relevance

    echo $this->Form->Open(array('action' => '', 'method' => 'get')),
    $this->Form->TextBox('Search'),
    $this->Form->Button('Search', array('Name' => '')),
    $this->Form->Errors(),

    //the following reflect the same inputs as the main adv search landing page that
    //are not covered in the small dropdown on the main results page here

    $this->Form->Hidden('expand', array('value' => GetIncomingValue('expand'))), // Whether or not to expand advanced page or not
    $this->Form->Hidden('child', array('value' => GetIncomingValue('child'))),
    $this->Form->Hidden('forums', array('value' => GetIncomingValue('forums'))),
    $this->Form->Hidden('or', array('value' => $or)),
    $this->Form->Hidden('mem', array('value' => GetIncomingValue('mem'))),
    $this->Form->Hidden('tag', array('value' => GetIncomingValue('tag'))),
    $this->Form->Hidden('pg', array('value' => 1)) // Always default to page 1
    ;
    $Display = GetValue('expand', $_GET) == 'yes' ? '' : 'display:none';
    ?>
</div>
<?php $this->Settings = GetValue('Settings', $this->Data); ?>
<div id="MoreOptions" class="SphinxSearch MessageForm" style= <?php echo "$Display" ?>>
    <table>
        <tbody>
            <tr>
                <td>
                    <dl>
                        <dt>
                        <?php echo $this->Form->Label(T('Newer Than:'), 'date'), ' '; ?>
                        </dt>
                        <dd>
                            <?php echo $this->Form->RadioList('date', $this->Settings['SearchOptions']->Time, array('list' => FALSE, 'listclass' => 'SearchOrderLeft', 'default' => $this->Settings['SearchOptions']->Time['All'])) ?>
                        </dd>
                    </dl>
                </td>

                <td>
                    <dl>
                        <dt>
                        <?php echo $this->Form->Label(T('Title Filter'), 'tiltes'); ?>
                        </dt>
                        <dd>
                            <ul>
                                <li>
                                    <?php echo $this->Form->Checkbox('titles', T('Titles only')); ?>
                                    <?php echo $this->Form->Checkbox('WithReplies', T('Threads with replies only')); ?>
                                </li>
                            </ul>
                        </dd>
                    </dl>
                </td>
            </tr>
            <tr>
                <td>
                    <dl>
                        <dt>
                        <?php echo $this->Form->Label(T('Display Results as:'), 'res'), ' '; ?>
                        </dt>
                        <dd>
                            <?php echo $this->Form->RadioList('res', $this->Settings['SearchOptions']->ResultFormat, array('list' => FALSE, 'default' => $this->Settings['SearchOptions']->ResultFormat['Classic'])) ?>
                        </dd>
                    </dl>
                </td>
                <td>
                    <dl>
                        <dt>
                        <?php echo $this->Form->Label(T('Phrase Match:'), 'match'), ' '; ?>
                        </dt>
                        <dd>
                            <?php echo $this->Form->Dropdown('match', $this->Settings['SearchOptions']->Match, array('id' => 'match')) ?>
                        </dd>
                    </dl>
                </td>
            </tr>
        </tbody>
    </table>



</div>
<?php
echo $this->Form->Close();

$ViewLocation = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'views' . DS . 'search' . DS . 'results.php';
include($ViewLocation); //load the main results view page

