<?php
$Result = $this->Data['Result'];
$Inputs = $this->Data['Inputs'];
$RelatedThreads = $this->Data['RelatedThreads'];
//print_r($Result);die;
//print_r($Inputs); die;
//print_r($RelatedThreads); die;
$this->SphinxSearchModel = new SphinxSearchModel();
$GETString = explode('&',$_SERVER['QUERY_STRING']) ; //use this to providea link back to search
if (!($Result['NumRows'] == 0)):
    ?>
    <div class="colmask leftmenu">
        <div class="colright">
            <div class="col1wrap">
                <div class="col1">
                    <!-- Column 1 start (Center) -->
                    <div id="SearchSummary">
                        [<span id="SearchAgain"> <?php echo Anchor('Search Again','search/?'.substr($_SERVER['QUERY_STRING'],strlen($GETString[0])+1) ) ?></span>]
                        <span id="SearchDetail">Search Query: </span>
                        <span id="SearchQuery"><?php echo $Inputs['Query'] ?></span>
                        <span id="Time"><?php echo $Result['NumRows'] . Plural($Result['NumRows'],' result',' results').' in ' . $Result['Time'] . 's' ?></span>
                        <span class="Pager"></span>
                    </div>
                    <?php foreach ($Result['Records'] as $Row): ?>
                    <?php $Author->Photo = $Row->userphoto; $Author->Name = $Row->username ?>
                        <div class="UserInfo">
                            <div class="Contact">
                                <div class="UserNameContainer">
                                    <?php echo UserPhoto($Author, array('LinkClass'=>'','ImageClass'=>'ProfilePhotoSmall')) ?>
                                    <div class="DiscussionInfo">
                                        <h4 class="DiscussionTitle"><?php echo Anchor($Row->discussionnameattr, 'discussion/' . $Row->discussionid . '/' . Gdn_Format::Url($Row->discussionnameattr)) ?></h4>
                                         <span class="DiscussionAuthor"><?php
                                         echo 'Discussion in '.Anchor($Row->catname,'categories/'.$Row->caturlcode) .' started by '.Anchor($Row->username,'profile/'.$Row->userid.'/'.$Row->username) .' on '.
                                         Anchor(Gdn_Format::Date($Row->discussiondateinserted), 'discussion/' . $Row->discussionid . '/' . Gdn_Format::Url($Row->discussionnameattr).'/p1') ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="UserInfoExtra">
                                <dl class="userstats">
                                    <dt>Replies:</dt>
                                    <dd><?php echo Gdn_Format::BigNumber($Row->discussioncountcomments) ?></dd>
                                    <dt>Views:</dt>
                                    <dd class="Views"><?php echo Gdn_Format::BigNumber($Row->discussioncountviews) ?></dd>
                                </dl>
                            </div>
                        </div>
                    <div class="CommentBody">
                         <?php echo $Row->commentbody ?>
                    </div>
                    <?php endforeach ?>
                </div>
            </div>
            <div class="col2">
                <table id="HitBox">
                    <thead>
                        <tr>
                            <th class="Word">
                                Word
                            </th>
                            <th class="Docs">
                                Docs
                            </th>
                            <th class="Hits">
                                Hits
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Result['Words'] as $Word => $WordArray): ?>
                        <tr>
                                <td class="Word">
                                    <?php echo $Word ?>
                                </td>
                                <td class="Docs">
                                    <?php echo Gdn_Format::BigNumber($WordArray['docs']) ?>
                                </td>
                                <td class="Hits">
                                    <?php echo Gdn_Format::BigNumber($WordArray['hits']) ?>
                                </td>
                        </tr>
                         <?php endforeach ?>
                    </tbody>
                </table>
                <h3 id="ReleatedSearches">Related Threads</h3>
                <?php if ($RelatedThreads['NumRows'] != 0) : ?>
                    <?php foreach ($RelatedThreads['Records'] as $Row) : ?>
                <div class="RelatedThread"><?php echo Anchor($Row->{SPHINX_FIELD_STR_DISCUSSIONNAME},'wser') ?></div>
                    <?php endforeach ?>
                <?php endif ?>
            </div>
        </div>
    </div>
<?php else: ?>
No records found
<?php endif ?>
