<?php
if (!defined('APPLICATION'))
    exit();

function WriteFull($Results) {
    $Result = $Results; //main search results
    if (isset($Result['total_found'])):
        if (!($Result['total_found'] == 0)): //make sure there is something here
            ob_start();
            ?>
                        <div class="col1">
                            <!-- Column 1 start (Center) -->
                            <div id="SearchSummary">
                                <span id="SearchDetail">Search Query: </span>
                                <span id="SearchQuery"><?php echo $Result['query'] ?></span>
                                <span id="Time"><?php echo $Result['total_found'] . Plural($Result['total_found'], ' result', ' results') . ' in ' . $Result['time'] . 's' ?></span>
                                <span class="Pager"></span>
                            </div>
                            <?php foreach ($Result['matches'] as $Row): ?>
                                <?php $Author->Photo = $Row->{SPHINX_ATTR_STR_USERPHOTO};
                                $Author->Name = $Row->{SPHINX_FIELD_STR_USERNAME}
                                ?>
                                <div class="UserInfo">
                                    <div class="Contact">
                                        <div class="UserNameContainer">
                <?php echo UserPhoto($Author, array('LinkClass' => '', 'ImageClass' => 'ProfilePhotoSmall')) ?>
                                            <div class="DiscussionInfo">
                                                <h4 class="DiscussionTitle"><?php echo Anchor($Row->{SPHINX_ATTR_STR_DISCUSSIONNAME}, 'discussion/' . $Row->{SPHINX_ATTR_UINT_DISCUSSIONID} . '/' . Gdn_Format::Url($Row->{SPHINX_ATTR_STR_DISCUSSIONNAME})) ?></h4>
                                                <span class="DiscussionAuthor"><?php
                echo 'Discussion in ' . Anchor($Row->{SPHINX_ATTR_STR_CATNAME}, 'categories/' . $Row->{SPHINX_ATTR_STR_CATULCODE}) . ' started by ' . Anchor($Row->{SPHINX_FIELD_STR_USERNAME}, 'profile/' . $Row->{SPHINX_ATTR_UINT_USERID} . '/' . $Row->{SPHINX_FIELD_STR_USERNAME}) . ' on ' .
                Anchor(Gdn_Format::Date($Row->{SPHINX_ATTR_TSTAMP_DISCUSSIONDATEINSERTED}), 'discussion/' . $Row->{SPHINX_ATTR_UINT_DISCUSSIONID} . '/' . Gdn_Format::Url($Row->{SPHINX_ATTR_STR_DISCUSSIONNAME}) . '/p1')
                ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="UserInfoExtra">
                                        <dl class="userstats">
                                            <dt>Replies:</dt>
                                            <dd><?php echo Gdn_Format::BigNumber($Row->{SPHINX_ATTR_UINT_DISCUSSIONCOMENTS}) ?></dd>
                                            <dt>Views:</dt>
                                            <dd class="Views"><?php echo Gdn_Format::BigNumber($Row->{SPHINX_ATTR_UINT_DISCUSSIONVIEWS}) ?></dd>
                                        </dl>
                                    </div>
                                </div>
                                <div class="CommentBody">
                                <?php echo $Row->{SPHINX_FIELD_STR_COMMENTBODY} ?>
                                </div>
            <?php endforeach ?>
                        </div>
        <?php endif ?>
    <?php endif ?>
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
function WriteSimple($Results){
ob_start();
        ?>
        <div id="Search_Container">
            <div id="RelatedThreads">
                <?php foreach ($Results['matches'] as $Row): ?>
                    <h4 class="DiscussionTitle"><?php echo Anchor($Row->{SPHINX_ATTR_STR_DISCUSSIONNAME}, 'discussion/' . $Row->{SPHINX_ATTR_UINT_DISCUSSIONID} . '/' . Gdn_Format::Url($Row->{SPHINX_ATTR_STR_DISCUSSIONNAME})) ?></h4>
                <?php endforeach ?>
                <div style="clear: both"></div>
            </div>
        </div>
        <?php
        $String = ob_get_contents();
        @ob_end_clean();

        return $String;
}

function WriteSleak($Results) {
    $String = '';
    $Total = $Results['total_found'];
    if ($Total == 0) {
        return $String; //return an empty string if no results
    }
    $Cols = 2; //two columns
    $j = 0;
    $Count = ceil($Total / $Cols); //number of rows for the first column
    $Org = $Count;
    ob_start();
    ?>
    <div id="RelatedThreads">
        <table id="RelatedThreadsTable">
            <tbody>
                <tr>
                    <?php for ($i = 0; $i < $Cols; $i++): ?>
                        <td>
                            <?php while ($Count--): ?>
                                <?php $Row = $Results['matches'][$j] ?>
                                <div class="UserInfo">
                                    <div class="Contact">
                                        <div class="UserNameContainer">
                                            <div class="DiscussionInfo">
                                                <h4 class="DiscussionTitle"><?php echo Anchor($Row->{SPHINX_ATTR_STR_DISCUSSIONNAME}, 'discussion/' . $Row->{SPHINX_ATTR_UINT_DISCUSSIONID} . '/' . Gdn_Format::Url($Row->{SPHINX_ATTR_STR_DISCUSSIONNAME})) ?></h4>
                                                <span class="DiscussionAuthor"><?php
                    echo 'Discussion in ' . Anchor($Row->{SPHINX_ATTR_STR_CATNAME}, 'categories/' . $Row->{SPHINX_ATTR_STR_CATULCODE}) . ' started by ' . Anchor($Row->{SPHINX_FIELD_STR_USERNAME}, 'profile/' . $Row->{SPHINX_ATTR_UINT_USERID} . '/' . $Row->{SPHINX_FIELD_STR_USERNAME}) . ' on ' .
                    Anchor(Gdn_Format::Date($Row->{SPHINX_ATTR_TSTAMP_DISCUSSIONDATEINSERTED}), 'discussion/' . $Row->{SPHINX_ATTR_UINT_DISCUSSIONID} . '/' . Gdn_Format::Url($Row->{SPHINX_ATTR_STR_DISCUSSIONNAME}) . '/p1')
                                ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="UserInfoExtra">
                                        <dl class="userstats">
                                            <dt>Replies:</dt>
                                            <dd><?php echo Gdn_Format::BigNumber($Row->{SPHINX_ATTR_UINT_DISCUSSIONCOMENTS}) ?></dd>
                                            <dt>Views:</dt>
                                            <dd class="Views"><?php echo Gdn_Format::BigNumber($Row->{SPHINX_ATTR_UINT_DISCUSSIONVIEWS}) ?></dd>
                                        </dl>
                                    </div>
                                </div>
                            <?php $j++ ?>
                            <?php endwhile ?>
                        </td>
                        <?php
                        $Count = floor($Total / $Cols) ;

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

function WriteTable($Results) {
    $String = '';
    if($Results['error'] != FALSE)
        return FALSE;
    $Total = $Results['total_found'];
    if ($Total == 0) {
        return $String; //return an empty string if no results
    }
    ob_start();
    ?>
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
                    <?php foreach($Results['matches'] as $Row): ?>
                        <tr>
                            <td class="Title">
                                <h4 class="DiscussionTitle"><?php echo Anchor($Row->{SPHINX_ATTR_STR_DISCUSSIONNAME}, 'discussion/' . $Row->{SPHINX_ATTR_UINT_DISCUSSIONID} . '/' . Gdn_Format::Url($Row->{SPHINX_ATTR_STR_DISCUSSIONNAME})) ?></h4>
                            </td>
                            <td>
                                <span class="DiscussionAuthor"><?php
                echo Anchor($Row->{SPHINX_ATTR_STR_CATNAME}, 'categories/' . $Row->{SPHINX_ATTR_STR_CATULCODE}) . ' started by ' . Anchor($Row->{SPHINX_FIELD_STR_USERNAME}, 'profile/' . $Row->{SPHINX_ATTR_UINT_USERID} . '/' . $Row->{SPHINX_FIELD_STR_USERNAME}) . ' on ' .
                Anchor(Gdn_Format::Date($Row->{SPHINX_ATTR_TSTAMP_DISCUSSIONDATEINSERTED}), 'discussion/' . $Row->{SPHINX_ATTR_UINT_DISCUSSIONID} . '/' . Gdn_Format::Url($Row->{SPHINX_ATTR_STR_DISCUSSIONNAME}) . '/p1')
                            ?></span>
                            </td>
                            <td>
                                <?php echo Gdn_Format::BigNumber($Row->{SPHINX_ATTR_UINT_DISCUSSIONCOMENTS}) ?>
                            </td>
                            <td>

                                <?php echo Gdn_Format::BigNumber($Row->{SPHINX_ATTR_UINT_DISCUSSIONVIEWS}) ?>
                            </td>
                            <td>
                               <?php echo Anchor($Row->{SPHINX_ATTR_STR_LASTCOMMENTUSERNAME}, 'profile/' . $Row->{SPHINX_ATTR_UINT_LASTCOMMENTUSERID} . '/' . $Row->{SPHINX_ATTR_STR_LASTCOMMENTUSERNAME}) . ' on ' .
                Anchor(Gdn_Format::Date($Row->{SPHINX_ATTR_STAMP_DATELASTCOMMENT}), 'discussion/' . $Row->{SPHINX_ATTR_UINT_DISCUSSIONID} . '/' . Gdn_Format::Url($Row->{SPHINX_ATTR_STR_DISCUSSIONNAME}) . '/'.$Row->{SPHINX_ATTR_UINT_DISCUSSIONLASTCOMMENTID}.'#'.'Comment_'.$Row->{SPHINX_ATTR_UINT_DISCUSSIONLASTCOMMENTID}) ?>
                            </td>
                        </tr>
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