<?php if (!defined('APPLICATION')) exit(); ?>
<div class="Tabs SearchTabs WithPanel"> <!-- WithPanel is used in the traditional plugin/theme ONLY!! -->
    <?php
     $or = GetIncomingValue('or') == '' ? 'Relevance' : GetIncomingValue('mem') ; //default order to Relevance
     $match = GetIncomingValue('match') == '' ? 'Extended' : GetIncomingValue('match') ; //default match to 'Extended'
     $res = GetIncomingValue('res') == '' ? 'Classic' : GetIncomingValue('res') ; //default result format to 'Classic'
     $date = GetIncomingValue('date') == '' ? 'All' : GetIncomingValue('date') ; //default time duration to 'All'
     $titles = GetIncomingValue('titles') == '' ? 0 : GetIncomingValue('titles') ; //default titles checkbox to 'Not Checked'
     $replies = GetIncomingValue('WithReplies') == '' ? 0 : GetIncomingValue('WithReplies') ; //default replies checkbox to 'Not Checked'

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
    $this->Form->Hidden('pg', array('value' => 1)), // Always default to page 1
    $this->Form->Hidden('match', array('value' => $match)),
    $this->Form->Hidden('res', array('value' => $res)),
    $this->Form->Hidden('date', array('value' => $date)),
    $this->Form->Hidden('titles', array('value' => $titles)),
    $this->Form->Hidden('replies', array('value' => $replies))
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
                        <?php echo 'Title Search'//echo $this->Form->Label(T('Newer Than:'), 'date'), ' '; ?>
                        </dt>
                        <dd>
                            <span class ="Examples"><?php echo '@title beer'//echo $this->Form->RadioList('date', $this->Settings['SearchOptions']->Time, array('list' => FALSE, 'listclass' => 'SearchOrderLeft', 'default' => $this->Settings['SearchOptions']->Time['All'])) ?></span>
                            <span class="Desc">Word 'beer' in title</span>
                        </dd>
                    </dl>
                </td>

                <td>
                    <dl>
                        <dt>
                        <?php echo 'User Search' //echo $this->Form->Label(T('Title Filter'), 'tiltes'); ?>
                        </dt>
                        <dd>
                            <ul>
                                <li>
                                    <span class ="Examples"><?php echo '@user admin'//echo $this->Form->Checkbox('titles', T('Titles only')); ?></span>
                                    <span class="Desc">Only posts by Admin</span><?php //echo $this->Form->Checkbox('WithReplies', T('Threads with replies only')); ?>
                                </li>
                            </ul>
                        </dd>
                    </dl>
                </td>
                <td>
                    <dl>
                        <dt>
                        <?php echo 'Text Search' //echo $this->Form->Label(T('Title Filter'), 'tiltes'); ?>
                        </dt>
                        <dd>
                            <ul>
                                <li>
                                    <span class ="Examples"><?php echo '@body beer'?></span>
                                    <span class = "Desc">Word 'beer' in the text</span>
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
                        <?php echo 'Phrase Search'//echo $this->Form->Label(T('Newer Than:'), 'date'), ' '; ?>
                        </dt>
                        <dd>
                            <span class ="Examples"><?php echo "\"Hello World\""//echo $this->Form->RadioList('date', $this->Settings['SearchOptions']->Time, array('list' => FALSE, 'listclass' => 'SearchOrderLeft', 'default' => $this->Settings['SearchOptions']->Time['All'])) ?></span>
                            <span class="Desc">Exact phrase match</span>
                        </dd>
                    </dl>
                </td>

                <td>
                    <dl>
                        <dt>
                        <?php echo 'Exclude Terms' //echo $this->Form->Label(T('Title Filter'), 'tiltes'); ?>
                        </dt>
                        <dd>
                            <ul>
                                <li>
                                    <span class ="Examples"><?php echo 'car -red'//echo $this->Form->Checkbox('titles', T('Titles only')); ?></span>
                                    <span class="Desc">Search cars that are not red</span><?php //echo $this->Form->Checkbox('WithReplies', T('Threads with replies only')); ?>
                                </li>
                            </ul>
                        </dd>
                    </dl>
                </td>
                <td>
                    <dl>
                        <dt>
                        <?php echo "\"Or\" Searches" //echo $this->Form->Label(T('Title Filter'), 'tiltes'); ?>
                        </dt>
                        <dd>
                            <ul>
                                <li>
                                    <span class ="Examples"><?php echo 'honda | bmw'?></span>
                                    <span class = "Desc">'honda' or 'bmw' results</span>
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
                        <?php echo 'Multi-Field'//echo $this->Form->Label(T('Newer Than:'), 'date'), ' '; ?>
                        </dt>
                        <dd>
                            <span class ="Examples"><?php echo "@(title,body) hello"//echo $this->Form->RadioList('date', $this->Settings['SearchOptions']->Time, array('list' => FALSE, 'listclass' => 'SearchOrderLeft', 'default' => $this->Settings['SearchOptions']->Time['All'])) ?></span>
                            <span class="Desc">Both contain 'hello'</span>
                        </dd>
                    </dl>
                </td>

                <td>
                    <dl>
                        <dt>
                        <?php echo 'WildCard' //echo $this->Form->Label(T('Title Filter'), 'tiltes'); ?>
                        </dt>
                        <dd>
                            <ul>
                                <li>
                                    <span class ="Examples"><?php echo 'hond* civ*'//echo $this->Form->Checkbox('titles', T('Titles only')); ?></span>
                                    <span class="Desc">Match all with an astrix</span><?php //echo $this->Form->Checkbox('WithReplies', T('Threads with replies only')); ?>
                                </li>
                            </ul>
                        </dd>
                    </dl>
                </td>
                <td>
                    <dl>
                        <dt>
                        <?php echo 'Combination' //echo $this->Form->Label(T('Title Filter'), 'tiltes'); ?>
                        </dt>
                        <dd>
                            <ul>
                                <li>
                                    <span class ="Examples"><?php echo '@title bmw @user admin'//echo $this->Form->Checkbox('titles', T('Titles only')); ?></span>
                                    <span class="Desc"></span><?php //echo $this->Form->Checkbox('WithReplies', T('Threads with replies only')); ?>
                                </li>
                            </ul>
                        </dd>
                    </dl>
                </td>
            </tr>

        </tbody>
    </table>

    See more <?php echo Anchor('supported syntax examples','http://sphinxsearch.com/docs/archives/1.10/extended-syntax.html'); ?>

</div>
<?php
echo $this->Form->Close();

$ViewLocation = PATH_PLUGINS . DS . 'SphinxSearch' . DS . 'views' . DS . 'search' . DS . 'results.php';
include($ViewLocation); //load the main results view page

