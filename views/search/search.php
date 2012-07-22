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

        var availableTags = $("#TagsInput").val().split(',');

        $(function() {
            var WebRoot = $("#WebRoot").val() + "/";

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
                    $.getJSON( WebRoot + "plugin/sphinxsearch/autocompletemember", {
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

            $( "#tags" )
            // don't navigate away from the field on tab when selecting an item
            .bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                    $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            })
            .autocomplete({
                minLength: 0,
                source: function( request, response ) {
                    // delegate back to autocomplete, but extract the last term
                    response( $.ui.autocomplete.filter(
                    availableTags, extractLast( request.term ) ) );
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
<?php //print_r($this->Assets); die; ?>
<?php echo $this->RenderAsset('LeftPanel'); //make the left panel?>
<?php
echo $this->Form->Open(array('method' => 'get', 'action' => ''));
echo $this->Form->Errors();

$this->Settings = $this->Data['Settings'];
?>
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
                    <?php echo $this->Form->Textbox('Search', array('id' => 'SearchInput')); ?>
                </li>
                <li>
                    <?php echo $this->Form->Checkbox('titles', 'Search titles only'); ?>
                    <?php echo $this->Form->Checkbox('WithReplies', 'Threads with replies only'); ?>
                </li>
            </ul>
        </dd>

    </dl>
    <dl>
        <dt>
        <?php echo $this->Form->Label('Phrase Match: ', 'match'), ' '; ?>
        </dt>
        <dd>
            <?php echo $this->Form->Dropdown('match', $this->Settings['SearchOptions']->Match, array('id' => 'match')) ?>
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
        <?php echo $this->Form->Label('Containing Tags:', 'tag'), ' '; ?>
        </dt>
        <dd>
            <?php echo $this->Form->Textbox('tag', array('id' => 'tags')); ?>
        </dd>
    </dl>
    <dl>
        <dt>
        <?php echo $this->Form->Label('Newer Than: ', 'date'), ' '; ?>
        </dt>
        <dd>
            <?php echo $this->Form->RadioList('date', $this->Settings['SearchOptions']->Time, array('list' => FALSE, 'listclass' => 'SearchOrderLeft', 'default' => $this->Settings['SearchOptions']->Time['All'])) ?>
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
                        <?php echo $this->Form->RadioList('or', array_slice($this->Settings['SearchOptions']->Order, 0, 2, TRUE), array('list' => TRUE, 'listclass' => 'SearchOrderLeft', 'default' => $this->Settings['SearchOptions']->Order['Relevance'])) ?>
                    </td>
                    <td>
                        <?php echo $this->Form->RadioList('or', array_slice($this->Settings['SearchOptions']->Order, 2, 4, TRUE), array('list' => TRUE, 'listclass' => 'SearchOrderRight')) ?>
                    </td>
                </tr>
            </table>
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
        <?php echo $this->Form->Label('Display Results as:', 'res'), ' '; ?>
        </dt>
        <dd>
            <?php echo $this->Form->RadioList('res', $this->Settings['SearchOptions']->ResultFormat, array('list' => FALSE, 'default' => $this->Settings['SearchOptions']->ResultFormat['Full'])) ?>
        </dd>
    </dl>
</div>
<?php echo $this->Form->Hidden('pg', array('value' => 'p1')); //add this here so it direts to first page and the pager picks this up?>
<?php echo $this->Form->Close(); ?>
<?php echo $this->RenderAsset('BottomPanel'); //make the left panel?>
<?php
echo $this->Form->Hidden('TagsInput', array('id' => 'TagsInput', 'value' => $this->Data['Tags'])); //add tags?>