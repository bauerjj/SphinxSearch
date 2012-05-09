<!--Change these to whatever you like to use for Jquery UI-->
<!--Themes are found here: http://jqueryui.com/themeroller/-->

<!--It is recommended to use google's hosted ones to save on load times-->
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.js"></script>
<link type="text/css" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/dark-hive/jquery-ui.css"/>

<!--end jquery UI includes-->
<script>
    $(document).ready(function() {

        //$("#example").multiselect(); //don't know why this won't load

        //Creates the outline when hovered
        $('.ui-button,.ui-menu-item').hover(
        function(){
            $(this).addClass("ui-state-hover");
        },
        function(){
            $(this).removeClass("ui-state-hover");
        }
    )

        $( "#SearchDate" ).datepicker(); //Jquery UI datepicker

        $('.ui-menu-item').click(function(){
            $("#SearchMatchButton .ui-button-text").text($(this).text());
            jQuery.fx.off = true; //disable animation for this
            $("#SearchMatchDropdown").toggle('showOrHide');
            jQuery.fx.off = false; //enable again
        });


        $("#rerun").button().click(function() {
        })
        .next()
        .button({
            text: true,
            icons: {
                primary: "ui-icon-triangle-1-s"
            }
        })
        .click(function() {
            jQuery.fx.off = true; //disable animation for this
            $("#SearchMatchDropdown").toggle('showOrHide');
            jQuery.fx.off = false; //enable again
        })
        .parent()
        .buttonset();


        //repopulate numview/replies and their sliders
        var numR = $('#SearchInputNumReplies').val();
        var numV = $('#SearchInputNumViews').val();
        $( "#SearchInputNumViews" ).val(0);
        $( "#SearchInputNumReplies" ).val(0);


        //Views
        $( "#slider-range-min-views" ).slider({
            range: "min",
            value: numV,
            min: 0,
            max: 2000,
            slide: function( event, ui ) {
                $( "#SearchNumViews" ).text( ui.value );
                $( "#SearchInputNumViews" ).val( ui.value );
            }
        });
        $( "#SearchNumViews" ).text( $( "#slider-range-min-views" ).slider( "value" ) );
        $( "#SearchInputNumViews" ).val( $( "#slider-range-min-replies" ).slider( "value" ) );

        //Replies
        $( "#slider-range-min-replies" ).slider({
            range: "min",
            value: numR,
            min: 0,
            max: 2000,
            slide: function( event, ui ) {
                $( "#SearchNumReplies" ).text( ui.value );
                $( "#SearchInputNumReplies" ).val( ui.value );
            }
        });
        $( "#SearchNumReplies" ).text( $( "#slider-range-min-replies" ).slider( "value" ) );
        $( "#SearchInputNumReplies" ).val( $( "#slider-range-min-replies" ).slider( "value" ) );

        $(function() {
            function split( val ) {
                return val.split( /,\s*/ );
            }
            function extractLast( term ) {
                return split( term ).pop();
            }

            $( "#SearchMember" )
            // don't navigate away from the field on tab when selecting an item
            .bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                    $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            })
            .autocomplete({
                source: function( request, response ) {
                    $.getJSON( "http://mcuhq.com/forums/plugin/autocompletemember", {
                        term: extractLast( request.term )
                    }, response );
                },
                search: function() {
                    // custom minLength
                    var term = extractLast( this.value );
                    if ( term.length < 2 ) {
                        return false;
                    }
                },
                focus: function() {
                    // prevent value inserted on focus
                    return false;
                },
                select: function( event, ui ) {
                    var terms = split( this.value );
                    // remove the current input
                    terms.pop();
                    // add the selected item
                    terms.push( ui.item.value );
                    // add placeholder to get the comma-and-space at the end
                    terms.push( "" );
                    this.value = terms.join( ", " );
                    return false;
                }
            });
        });


    });
</script>
<?php
echo $this->Form->Open(array('method' => 'get', 'action' => 'search/results'));
echo $this->Form->Errors();
?>
<!--<select id="example" name="example" multiple="multiple">
<option value="1">Option 1</option>
<option value="2">Option 2</option>
<option value="3">Option 3</option>
<option value="4">Option 4</option>
<option value="5">Option 5</option>
</select>-->
<div class="colmask holygrail">
    <div class="colmid">
        <div class="colleft">
            <div class="col1wrap">
                <div class="col1">
                    <!-- Column 1 start (center) -->
                    <div id="SearchMenu">
                        <dl>
                            <dt>
                            <div id="SearchTop">
                                <span class="SearchHelp">[ <?php echo Anchor('Help', 'help', array('class' => 'Popup')) ?> ]</span>
                                <div style="display: inline">
                                    <button id="rerun">Search</button>
                                    <!--                                    <button id="SearchMatchButton">Any</button>-->
                                </div>
                            </div>
                            <!--                            <div style="display: none;" id="SearchMatchDropdown">
                                                            <ul class="ui-menu ui-widget ui-widget-content ui-corner-all ui-dropdown">
                                                                <li class="ui-menu-item">
                                                                    <a class="ui-corner-all" tabindex="-1">All</a>
                                                                </li>
                                                                <li class="ui-menu-item">
                                                                    <a class="ui-corner-all" tabindex="-1">Boolean</a>
                                                                </li>

                                                            </ul>
                                                        </div>-->
                            </dt>
                            <dd>
                                <ul><li>
                                        <?php echo $this->Form->Textbox('q', array('id' => 'SearchInput')); ?>
                                    </li>
                                    <li><?php echo $this->Form->Checkbox('titles', 'Search titles only'); ?></li>
                                </ul>
                            </dd>

                        </dl>
                        <dl>
                            <dt>
                            <?php echo $this->Form->Label('Phrase Match: ', 'match'), ' '; ?>
                            </dt>
                            <dd>
                                <?php echo $this->Form->Dropdown('match', $this->Data['Match'], array('id' => 'match')) ?>
                            </dd>
                        </dl>
                        <dl>
                            <dt>
                            <?php echo $this->Form->Label('Search in Forums: ', 'forums'), ' '; ?>
                            </dt>
                            <dd>
                                <ul>
                                    <li>
                                        <?php echo SphinxSearchPlugin::CategoryDropDown('forums[]', array('Value' => GetValue('forums', $_GET))); ?>
                                    </li>
                                    <li>
                                        <?php echo $this->Form->Checkbox('child', 'Search child forums'); ?>
                                    </li>
                                </ul>
                            </dd>
                        </dl>
                        <dl>
                            <dt>
                            <?php echo $this->Form->Label('Newer Than: ', 'date'), ' '; ?>
                            </dt>
                            <dd>
                                <?php echo $this->Form->Textbox('date', array('id' => 'SearchDate')); ?>
                            </dd>
                        </dl>
                        <dl>
                            <dt>
                            <?php echo $this->Form->Label('Posted by Member: ', 'mem'), ' '; ?>
                            </dt>
                            <dd>
                                <?php echo $this->Form->Textbox('mem', array('id' => 'SearchMember')); ?>
                            </dd>
                        </dl>
                        <dl>
                            <dt>
                            <?php echo $this->Form->Label('Min Number of Replies:', 'SearchNumReplies'), ' '; ?>
                            <b><span id="SearchNumReplies"></span></b>
                            <?php echo $this->Form->Textbox('numR', array('id' => 'SearchInputNumReplies')); ?>
                            </dt>
                            <dd class="Slider">
                                <div id="slider-range-min-replies"></div>
                            </dd>
                        </dl>
                        <dl>
                            <dt>
                            <?php echo $this->Form->Label('Min Number of Views:', 'SearchNumViews'), ' '; ?>
                            <b><span id="SearchNumViews"></span></b>
                            <?php echo $this->Form->Textbox('numV', array('id' => 'SearchInputNumViews')); ?>
                            </dt>
                            <dd class="Slider">
                                <div id="slider-range-min-views"></div>
                            </dd>
                        </dl>
                        <dl>

                            <dt>
                            <?php echo $this->Form->Label('Order By:', 'or'), ' '; ?>
                            </dt>
                            <dd>
                                <table>
                                    <tr>
                                        <td class="col">
                                            <?php echo $this->Form->RadioList('or', array_slice($this->Data['Order'],0,2, TRUE), array('list' => TRUE, 'listclass' => 'SearchOrderLeft')) ?>
                                        </td>
                                        <td>
                                            <?php echo $this->Form->RadioList('or', array_slice($this->Data['Order'],2,4,TRUE), array('list' => TRUE, 'listclass' => 'SearchOrderRight')) ?>
                                        </td>
                                    </tr>
                                </table>
                            </dd>
                        </dl>
                        <dl>
                            <dt>
                            <?php echo $this->Form->Label('Containing Tags:', 'tag'), ' '; ?>
                            </dt>
                            <dd>
                                <?php echo $this->Form->Textbox('tag', array('id' => 'SearchTags')); ?>
                            </dd>
                        </dl>
                        <dl>
                            <dt>
                            <?php echo $this->Form->Label('Display Results as:', 'res'), ' '; ?>
                            </dt>
                            <dd>
                                <?php echo $this->Form->RadioList('res', $this->Data['ResultFormat'], array('list' => FALSE)) ?>
                            </dd>
                        </dl>
                    </div>
                    <!-- Column 1 end -->
                </div>
            </div>
            <div class="col2">
                <!-- Column 2 start (left) -->
                <h3>Latest Searches</h3>
                <ul>

                    <li>aewrw oeiru woieur pwoier u</li>
                    <li>aewrw oeiru woieur pwoier u</li>
                    <li>aewrw oeiru woieur pwoier u</li>
                    <li>aewrw oeiru woieur pwoier u</li>
                    <li>aewrw oeiru woieur pwoier u</li>
                </ul>
                <!-- Column 2 end -->
                <h3>Popular Searches</h3>
                <ul>

                    <li>aewrw oeiru woieur pwoier u</li>
                    <li>aewrw oeiru woieur pwoier u</li>
                    <li>aewrw oeiru woieur pwoier u</li>
                    <li>aewrw oeiru woieur pwoier u</li>
                    <li>aewrw oeiru woieur pwoier u</li>
                </ul>
            </div>
            <div class="col3 SearchHelp">
                <!-- Column 3 start (right) -->
                <h3>Search Help</h3>
                <br/>
                <ul>
                    <b>Fully Searchable Fields:</b>
                    <li>Thread title & body text</li>
                    <li>Comment body text</li>
                    <li>Author name</li>
                </ul>

                <br/>
                <ul>
                    <b>The following special operators can be used:</b>
                    <li><p>Operator OR: </p>
                        <span class="SearchExamples">hello | world</span>
                    </li>
                    <li><p>Operator NOT: </p>
                        <span class="SearchExamples">hello !world</span>
                    </li>
                    <li><p>Exact Phrase</p>
                        <span class="SearchExamples">"hello world"</span>
                    </li>
                    <li><p>Field Search </p>
                        <span class="SearchExamples">@title hello @commentbody world</span>
                    </li>
                    <li><p>Multiple-Field Search</p>
                        <span class="SearchExamples">@(title,commentbody) hello world</span>
                    </li>
                    <li><p>All-Field </p>
                        <span class="SearchExamples">@* hello</span>
                    </li>
                    <li><p>Quorum Matching</p>
                        <span class="SearchExamples">"the world is terrible"/3</span>
                    </li>
                    <li><p>Generalized Proximity</p>
                        <span class="SearchExamples">hello NEAR/3 world NEAR/4</span>
                    </li>
                    <li><p>Zone Limit</p>
                        <span class="SearchExamples">ZONE:(h3,h4) only in these titles</span>
                    </li>

                </ul>
                <!-- Column 3 end -->
            </div>
        </div>
    </div>
</div>
<?php echo $this->Form->Close(); ?>