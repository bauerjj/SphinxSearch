<?php
if (!defined('APPLICATION'))
    exit();

function WriteResults($Format, $Results, $WriteText = FALSE, $Module = FALSE) {
    if ($Module)
        $CssClass = 'PanelInfo PanelDiscussions'; //for the panel
    else
        $CssClass = 'DataList'; //regular content
    switch (strtolower($Format)) {
        case 'sleek':
            return WriteSleek($Results, $WriteText, $CssClass);
            break;
        case 'table':
            return WriteTable($Results, $WriteText, $CssClass);
            break;
        case 'simple':
            return WriteSimple($Results, $WriteText, $CssClass);
            break;
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
                        <h4 class="Title"><?php echo Anchor($Row->Title . Wrap(htmlspecialchars(SliceString($Row->DiscussionBody, SS_PREVIEW_BODY_LIMIT)), 'span', array('class' => 'ToolTip')), $TitleURL, FALSE, array('class' => 'HasToolTip')) ?></h4>
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

/**
 * returns the discussion title only!
 *
 * @param type $Results
 * @return type
 */
function WriteSimple($Results, $WriteText, $CssClass) {
    if (sizeof($Results) == 0)
        return '';
    ob_start();
    ?>
    <div class="SphinxSearch">
        <ul class="<?php echo $CssClass ?>">
            <?php foreach ($Results as $Row): ?>
                <?php
                $Row->CountCommentWatch = $Row->CountComments; //for discussion link
                $TitleURL = $Row->IsComment ? 'discussion/comment/' . $Row->CommentID . '/#Comment_' . $Row->CommentID : DiscussionLink($Row, FALSE); //if the comment is from the orignal discussion poster, simply link to the front page of that
                ?>
                <li class="Item">
                    <div class="ItemContent">
                        <?php echo Anchor($Row->Title . Wrap(htmlspecialchars(SliceString(Gdn_Format::Text($Row->DiscussionBody), SS_PREVIEW_BODY_LIMIT)), 'span', array('class' => 'ToolTip')), $TitleURL, FALSE, array('class' => 'HasToolTip Title')) ?>
                        <?php if ($WriteText) : ?>
                            <div class="Message Excerpt">
                                <?php echo nl2br(SliceString($Row->Body, SS_BODY_LIMIT)); ?>
                            </div>
                        <?php endif ?>
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

function WriteSleek($Results, $WriteText, $CssClass) {
    $String = '';
    $Total = sizeof($Results);
    if ($Total == 0) {
        return $String; //return an empty string if no results
    }
    $Cols = 1; //two columns
    $j = 0;
    $Count = ceil($Total / $Cols); //number of rows for the first column
    $Org = $Count;
    ob_start();
    ?>
    <div class="SphinxSearch Sleek">
        <ul class="<?php echo $CssClass ?>">
            <?php for ($i = 0; $i < $Cols; $i++): ?>
                <?php while ($Count--): ?>
                    <?php
                    $Row = $Results[$j];
                    $Author->Photo = $Row->UserPhoto;
                    $Author->Name = $Row->UserName;
                    $Author->UserID = $Row->UserID;

                    $Row->CountCommentWatch = $Row->CountComments; //for discussion link
                    $TitleURL = $Row->IsComment ? 'discussion/comment/' . $Row->CommentID . '/#Comment_' . $Row->CommentID : DiscussionLink($Row, FALSE); //if the comment is from the orignal discussion poster, simply link to the front page of that
                    ?>
                    <li class="Item">
                        <div class="ItemContent">
                            <div class="UserInfo">
                                <div class="Contact">
                                    <div class="UserNameContainer">
                                        <?php echo UserPhoto($Author, array('ImageClass' => 'ProfilePhotoSmall')) ?>
                                        <div class="DiscussionInfo">
                                            <h4 class="Title"><?php echo Anchor($Row->Title . Wrap(htmlspecialchars(SliceString($Row->DiscussionBody, SS_PREVIEW_BODY_LIMIT)), 'span', array('class' => 'ToolTip')), $TitleURL, FALSE, array('class' => 'HasToolTip')) ?></h4>
                                            <div class="Meta">
                                                <span class="MItem"><?php echo Anchor($Row->CatName, 'categories/' . $Row->CatUrlCode) ?></span>
                                                <span class="MItem"><?php echo UserAnchor($Author) ?></span>
                                                <span class="MItem"><?php echo Anchor(Gdn_Format::Date($Row->DateInserted), $TitleURL) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="StatsBlock">
                                    <dl class="Stats">
                                        <dt>Replies:</dt>
                                        <dd><?php echo Gdn_Format::BigNumber($Row->CountComments) ?></dd>
                                        <dt>Views:</dt>
                                        <dd class="Views"><?php echo Gdn_Format::BigNumber($Row->CountViews) ?></dd>
                                    </dl>
                                </div>
                            </div>
                            <?php if ($WriteText) : ?>
                                <div class="Message Excerpt">
                                    <?php echo nl2br(SliceString($Row->Body, SS_BODY_LIMIT)); ?>
                                </div>
                            <?php endif ?>
                            <?php $j++ ?>
                        </div>
                    </li>
                <?php endwhile ?>
                <?php
                $Count = floor($Total / $Cols);
                ?>
            <?php endfor ?>
        </ul>
        <div style="clear: both"></div>
    </div>
    <?php
    $String = ob_get_contents();
    @ob_end_clean();
    return $String;
}

function WriteTable($Results, $WriteText, $CssClass) {
    $String = '';
    $Total = sizeof($Results);
    if ($Total == 0) {
        return $String; //return an empty string if no results
    }
    $Count = 0; //for toggling the message div if text
    ob_start();
    ?>
    <div class="SphinxSearch Table">
        <table>
            <thead>
                <tr>
                    <th class="Title">Discussion</th>
                    <th class="Starter">Starter</th>
                    <th class="Forum">Forum</th>
                    <th class="InfoCount">R / V</th>
                    <th class="Latest">Latest</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($Results as $Row): ?>
                    <?php
                    $ID = 'T_' . $Count++; //unique identifer
                    $OAuthor->Name = $Row->UserName;
                    $OAuthor->UserID = $Row->UserID; //original author
                    $LAuthor->Name = $Row->LastUserName;
                    $LAuthor->UserID = $Row->LastUserID; //latest author
                    $Row->CountCommentWatch = $Row->CountComments; //for discussion link
                    $TitleURL = $Row->IsComment ? 'discussion/comment/' . $Row->CommentID . '/#Comment_' . $Row->CommentID : DiscussionLink($Row, FALSE); //if the comment is from the orignal discussion poster, simply link to the front page of that
                    ?>

                    <tr <?php echo Alternate() ?>>
                        <td class="Title">
                            <?php if ($WriteText): ?>
                                <span id="<?php echo $ID ?>" class="PlusImage Toggle"></span>
                            <?php endif ?>
                            <span class="Title"><?php echo Anchor($Row->Title . Wrap(htmlspecialchars(SliceString($Row->DiscussionBody, SS_PREVIEW_BODY_LIMIT)), 'span', array('class' => 'ToolTip')), $TitleURL, FALSE, array('class' => 'HasToolTip')) ?></span>
                        </td>
                        <td class="Starter">
                            <?php echo UserAnchor($OAuthor) ?>
                            <?php Anchor(Gdn_Format::Date($Row->DateInserted), $TitleURL) ?>
                        </td>
                        <td class="Forum">
                            <?php echo Anchor(Gdn_Format::Text($Row->CatName), 'categories/' . $Row->CatUrlCode) ?>
                        </td>
                        <td class="InfoCount">
                            <?php echo Gdn_Format::BigNumber($Row->CountComments) ?>
                            /
                            <?php echo Gdn_Format::BigNumber($Row->CountViews) ?>
                        </td>
                        <td class="Latest">
                            <?php
                            echo UserAnchor($LAuthor) . ' on ';
                            echo Anchor(Gdn_Format::Date($Row->DateInserted), DiscussionLink($Row, $Extended = TRUE));
                            ?>
                        </td>
                    </tr>
                    <?php if ($WriteText) : ?>
                    <tr id="<?php echo $ID . 'T' ?>" style="display: none" class="ExpandText">
                        <td  colspan="5"> <!-- Need this since this column will expand the width of the table !-->
                        <div class="Message Excerpt">
                            <?php echo nl2br(SliceString($Row->Body, SS_BODY_LIMIT)); ?>
                        </div>
                        </td>
                    </tr>

                <?php endif ?>
            <?php endforeach ?>
            </tbody>
        </table>
        <div style="clear: both"></div>
    </div>
    <?php
    $String = ob_get_contents();
    @ob_end_clean();
    return $String;
}