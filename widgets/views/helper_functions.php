<?php
if (!defined('APPLICATION'))
    exit();

function WriteResults($Format, $Results, $WriteText = FALSE, $Module = FALSE) {
    if ($Module)
        $CssClass = 'PanelInfo PanelDiscussions'; //for the panel
    else
        $CssClass = 'DataList'; //regular content
    switch (strtolower($Format)) {
        case 'classic':
            default:
            return WriteClassic($Results, $WriteText, $CssClass);
            break;
    }
}

function WriteClassic($Results, $WriteText, $CssClass) {
    if (sizeof($Results) == 0) //make sure there is something here
        return '';

    ob_start();
    ?>
    <div class="SphinxSearch">
        <ul class="DataList">
            <?php foreach ($Results as $Row): ?>
                <?php
                $Author->Photo = $Row->UserPhoto;
                $Author->Name = $Row->UserName;
                $Author->UserID = $Row->UserID;
                $TitleURL = $Row->IsComment ? 'discussion/comment/' . $Row->CommentID . '/#Comment_' . $Row->CommentID : DiscussionLink($Row, FALSE); //if the comment is from the orignal discussion poster, simply link to the front page of that
                ?>
                <li class="Item">
                    <div class="ItemContent">
                        <h4 class="Title"><?php echo Anchor($Row->Title, $TitleURL, FALSE, array('class' => 'HasToolTip')) ?></h4>
                        <?php if ($WriteText) : ?>
                            <div class="Message Excerpt">
                                <?php //echo nl2br(SliceString($Row->Body, SS_BODY_LIMIT));  This seemed to make MARKDOWN posts look correct - JJB?>
                                 <?php echo SliceString($Row->Body, SS_BODY_LIMIT); ?>
                            </div>
                        <?php endif ?>
                        <div class="Meta">
                            <span class="MItem"><?php echo UserPhoto($Author, array('LinkClass' => '', 'ImageClass' => 'ProfilePhotoSmall PhotoWrap')) ?></span>
                            <span class="MItem"><?php echo Anchor($Row->UserName, UserUrl($Author)); ?></span>
                            <span class="MItem"><?php echo Anchor(Gdn_Format::Date($Row->DateInserted), 'discussion/' . $Row->DiscussionID . '/' . Gdn_Format::Url($Row->Title) . '/p1') ?></span>
                            <span class="MItem"><?php echo Anchor($Row->CatName, 'discussion/' . $Row->DiscussionID . '/' . Gdn_Format::Url($Row->Title) . '/p1') ?></span>
                        </div>
                    </div>
                </li>

            <?php endforeach ?>
        </ul>
    </div>
    <?php
    $String = ob_get_contents();
    @ob_end_clean();

    return $String;
}
