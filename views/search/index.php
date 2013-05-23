<?php if (!defined('APPLICATION')) exit(); ?>
<div class="Tabs SearchTabs WithPanel"> <!-- WithPanel is used in the traditional plugin/theme ONLY!! -->
    <?php
    $or = GetIncomingValue('or') == '' ? 'Relevance' : GetIncomingValue('mem'); //default order to Relevance

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
                        <?php echo $this->Form->Label(T('Order By:'), 'or'), ' '; ?>
                        </dt>
                        <dd>
                            <table id="OrderBy" style="width: 100%">
                                <tbody>
                                    <tr>
                                        <td>
                                            <?php echo $this->Form->RadioList('or', array_slice($this->Settings['SearchOptions']->Order, 0, 2, TRUE), array('list' => TRUE, 'listclass' => 'SearchOrderLeft', 'default' => $this->Settings['SearchOptions']->Order['Relevance'])) ?>
                                        </td>
                                        <td>
                                            <?php echo $this->Form->RadioList('or', array_slice($this->Settings['SearchOptions']->Order, 2, 4, TRUE), array('list' => TRUE, 'listclass' => 'SearchOrderRight')) ?>

                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </dd>
                        <dt>
                        <?php echo $this->Form->Label(T('User:'), 'user'), ' '; ?>
                        </dt>
                        <dd>
                            <?php echo $this->Form->Input('user','text', array('id' => 'UserInput', 'class'=> 'UserInput')); ?>
                        </dd>
                    </dl>
                </td>
                <td>
                    <dl>
                        <dt>
                        <?php echo $this->Form->Label(T('Search in Forums:'), 'forums'), ' '; ?>
                        </dt>
                        <dd>
                            <ul>
                                <li>
                                    <?php echo SphinxSearchLitePlugin::CategoryDropDown('forums[]', array('Value' => ArrayValue('forums', $_GET) == '' ? array(0) : ArrayValue('forums', $_GET))); ?>
                                </li>
                                <li>
                                    <?php echo $this->Form->Checkbox('child', 'Search child forums'); ?>
                                </li>
                            </ul>
                        </dd>
                    </dl>
                </td>
            </tr>
        </tbody>
    </table>



</div>
<?php
echo $this->Form->Close();

$ViewLocation = PATH_PLUGINS . DS . 'SphinxSearchLite' . DS . 'views' . DS . 'search' . DS . 'results.php';
include($ViewLocation); //load the main results view page

