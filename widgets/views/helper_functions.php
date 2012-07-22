<?php
if (!defined('APPLICATION'))
    exit();

function WriteResults($Format, $Results, $WriteText = FALSE) {
    switch (strtolower($Format)) {
        case 'sleak':
            return WriteSleak($Results, $WriteText);
            break;
        case 'full':
            return WriteFull($Results, $WriteText);
            break;
        case 'table':
            return WriteTable($Results, $WriteText);
            break;
        case 'simple':
        default:
            return WriteSimple($Results, $WriteText);
            break;
    }
}

function WriteFull($Results, $WriteText) {
    if (sizeof($Results) == 0) //make sure there is something here
        return '';
    ob_start();
    ?>
    <div class="col1">
        <!-- Column 1 start (Center) -->
        <?php foreach ($Results as $Row): ?>
            <?php
            $Author->Photo = $Row->UserPhoto;
            $Author->Name = $Row->UserName
            ?>
            <div class="UserInfo">
                <div class="Contact">
                    <div class="UserNameContainer">
                        <?php echo UserPhoto($Author, array('LinkClass' => '', 'ImageClass' => 'ProfilePhotoSmall')) ?>
                        <div class="DiscussionInfo">
                            <h4 class="DiscussionTitle"><?php echo Anchor($Row->Title. Wrap(htmlspecialchars(SliceString($Row->DiscussionBody, SS_BODY_LIMIT)), 'span', array('class'=>'ToolTip')), 'discussion/' . $Row->DiscussionID . '/' . Gdn_Format::Url($Row->Title), FALSE, array('class'=>'HasToolTip')) ?></h4>
                            <span class="DiscussionAuthor"><?php
                echo 'Discussion in ' . Anchor($Row->CatName, 'categories/' . $Row->CatUrlCode) . ' started by ' . Anchor($Row->UserName, 'profile/' . $Row->UserID . '/' . $Row->UserName) . ' on ' .
                Anchor(Gdn_Format::Date($Row->DateInserted), 'discussion/' . $Row->DiscussionID . '/' . Gdn_Format::Url($Row->Title) . '/p1')
                        ?></span>
                        </div>
                    </div>
                </div>
                <div class="UserInfoExtra">
                    <dl class="userstats">
                        <dt>Replies:</dt>
                        <dd><?php echo Gdn_Format::BigNumber($Row->CountComments) ?></dd>
                        <dt>Views:</dt>
                        <dd class="Views"><?php echo Gdn_Format::BigNumber($Row->CountViews) ?></dd>
                    </dl>
                </div>
            </div>
            <div class="CommentBody">
                <?php echo SliceString($Row->Body, SS_BODY_LIMIT); ?>
            </div>
        <?php endforeach ?>
    </div>
    <?php
    $String = ob_get_contents();
    @ob_end_clean();

    return $String;
}

/**
 * returns the discussion title only!
 *
 * @param type $Results
 * @return type
 */
function WriteSimple($Results, $WriteText) {
    if (sizeof($Results) == 0)
        return '';
    ob_start();
    ?>
    <div class="col1">
        <div id="RelatedThreads">
            <?php foreach ($Results as $Row): ?>
                <h4 class="DiscussionTitle"><?php echo Anchor($Row->Title. Wrap(htmlspecialchars(SliceString($Row->DiscussionBody, SS_BODY_LIMIT)), 'span', array('class'=>'ToolTip')), 'discussion/' . $Row->DiscussionID . '/' . Gdn_Format::Url($Row->Title), FALSE, array('class'=>'HasToolTip')) ?></h4>
                <?php if ($WriteText) : ?>
                    <div class="CommentBody">
                        <?php echo SliceString($Row->Body, SS_BODY_LIMIT); ?>
                    </div>
                <?php endif ?>
            <?php endforeach ?>
            <div style="clear: both"></div>
        </div>
    </div>
    <?php
    $String = ob_get_contents();
    @ob_end_clean();

    return $String;
}

function WriteSleak($Results, $WriteText) {
    $String = '';
    $Total = sizeof($Results);
    if ($Total == 0) {
        return $String; //return an empty string if no results
    }
    $Cols = 2; //two columns
    $j = 0;
    $Count = ceil($Total / $Cols); //number of rows for the first column
    $Org = $Count;
    ob_start();
    ?>
    <div class="col1">
        <table id="RelatedThreadsTable">
            <tbody>
                <tr>
                    <?php for ($i = 0; $i < $Cols; $i++): ?>
                        <td>
                            <?php while ($Count--): ?>
                                <?php $Row = $Results[$j] ?>
                                <div class="UserInfo">
                                    <div class="Contact">
                                        <div class="UserNameContainer">
                                            <div class="DiscussionInfo">
                                                <h4 class="DiscussionTitle"><?php echo Anchor($Row->Title. Wrap(htmlspecialchars(SliceString($Row->DiscussionBody, SS_BODY_LIMIT)), 'span', array('class'=>'ToolTip')), 'discussion/' . $Row->DiscussionID . '/' . Gdn_Format::Url($Row->Title), FALSE, array('class' => 'HasToolTip')) ?></h4>
                                                <span class="DiscussionAuthor"><?php
                    echo 'Discussion in ' . Anchor($Row->CatName, 'categories/' . $Row->CatUrlCode) . ' started by ' . Anchor($Row->UserName, 'profile/' . $Row->UserID . '/' . $Row->UserName) . ' on ' .
                    Anchor(Gdn_Format::Date($Row->DateInserted), 'discussion/' . $Row->DiscussionID . '/' . Gdn_Format::Url($Row->Title) . '/p1')
                                ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="UserInfoExtra">
                                        <dl class="userstats">
                                            <dt>Replies:</dt>
                                            <dd><?php echo Gdn_Format::BigNumber($Row->CountComments) ?></dd>
                                            <dt>Views:</dt>
                                            <dd class="Views"><?php echo Gdn_Format::BigNumber($Row->CountViews) ?></dd>
                                        </dl>
                                    </div>
                                </div>
                                <?php if ($WriteText) : ?>
                                    <div class="CommentBody">
                                        <?php echo SliceString($Row->Body, SS_BODY_LIMIT); ?>
                                    </div>
                                <?php endif ?>
                                <?php $j++ ?>
                            <?php endwhile ?>
                        </td>
                        <?php
                        $Count = floor($Total / $Cols);
                        ?>
                    <?php endfor ?>
                </tr>
            </tbody>
        </table>
        <div style="clear: both"></div>
    </div>
    <?php
    $String = ob_get_contents();
    @ob_end_clean();
    return $String;
}

function WriteTable($Results, $WriteText) {
    $String = '';
    $Total = sizeof($Results);
    if ($Total == 0) {
        return $String; //return an empty string if no results
    }
    ob_start();
    ?>
    <div class="col1">
        <div id="RelatedThreads">
            <table id="RelatedThreadsTable">
                <tbody>
                    <tr>
                        <th class="Title">Discussion</th>
                        <th>Information</th>
                        <th>Replies</th>
                        <th>Views</th>
                        <th>Latest</th>
                    </tr>
                    <?php foreach ($Results as $Row): ?>
                        <tr>
                            <td class="Title">
                                <h4 class="DiscussionTitle"><?php echo Anchor($Row->Title. Wrap(htmlspecialchars(SliceString($Row->DiscussionBody, SS_BODY_LIMIT)), 'span', array('class'=>'ToolTip')), 'discussion/' . $Row->DiscussionID . '/' . Gdn_Format::Url($Row->Title), FALSE, array('class'=>'HasToolTip')) ?></h4>
                            </td>
                            <td>
                                <span class="DiscussionAuthor"><?php
                echo Anchor($Row->CatName, 'categories/' . $Row->CatUrlCode) . ' started by ' . Anchor($Row->UserName, 'profile/' . $Row->UserID . '/' . $Row->UserName) . ' on ' .
                Anchor(Gdn_Format::Date($Row->DateInserted), 'discussion/' . $Row->DiscussionID . '/' . Gdn_Format::Url($Row->Title) . '/p1')
                        ?></span>
                            </td>
                            <td>
                                <?php echo Gdn_Format::BigNumber($Row->CountComments) ?>
                            </td>
                            <td>

                                <?php echo Gdn_Format::BigNumber($Row->CountViews) ?>
                            </td>
                            <td>
                                <?php
                                echo Anchor($Row->LastUserName, 'profile/' . $Row->LastUserID . '/' . $Row->LastUserName) . ' on ' .
                                Anchor(Gdn_Format::Date($Row->DateLastComment), 'discussion/' . $Row->DiscussionID . '/' . Gdn_Format::Url($Row->Title) . '/' . $Row->LastCommentID . '#' . 'Comment_' . $Row->LastCommentID)
                                ?>
                            </td>
                        </tr>
                        <?php if ($WriteText) : ?>
                            <tr>
                                <td>
                        <div class="CommentBody">
                            <?php echo SliceString($Row->Body, SS_BODY_LIMIT); ?>
                        </div>
                                    </td>
                        </tr>
                    <?php endif ?>
                <?php endforeach ?>
                </tbody>
            </table>
            <div style="clear: both"></div>
        </div>
    </div>
    <?php
    $String = ob_get_contents();
    @ob_end_clean();
    return $String;
}