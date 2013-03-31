<script>
    $(document).ready(function() {

        //this toggles the plus/minus image and expands a div to show the text if viewing table layout
        $('.Toggle').click(function(){
            var ID = $(this).attr('id');


            if($(this).hasClass('PlusImage')){
                $(this).removeClass("PlusImage");
                $(this).addClass("MinusImage");



                $('#'+ID+'T').toggle('fast');
            }
            else{
                $(this).addClass("PlusImage");
                $(this).removeClass("MinusImage");

                $('#'+ID+'T').toggle('fast');
            }

        });
        var MoreOptions = $("#More").val();
        var LessOptions = $("#Less").val();
        // For the more advanced options button
        $('#Options').click(function(){
            if($(this).attr('value') == MoreOptions){
                $(this).attr('value',LessOptions);
                $('#Form_expand').attr('value', 'yes'); // Next search will leave this div expanded
                $('#MoreOptions').toggle('fast');
            }
            else{
                $(this).attr('value',MoreOptions);
                $('#Form_expand').attr('value', 'no'); // Next search will leave this div hidden
                $('#MoreOptions').toggle('fast');
            }

        });



    });

</script>
<?php
/**
 * Grab the results according to the widgets returned array index, 'name'
 *
 * All attributes are defined in 'class.sphinxconstants.php'. DO NOT CHANGE THESE!
 *
 * These are used in the sphinx.conf which is what gets indexed and is final.
 * You should try to use the constansts instead of the actual name in case of updates
 * of the configuration file
 *
 */
?>
<div id="SphinxResults" class="WithPanel">
    <?php
//print_r($this->Data); die;
    $Settings = GetValue('Settings', $this->Data);
    $Results = GetValue('MainSearch', $this->Data); //main search results
    $GETString = GetValue('GETString', $this->Data);

    $Total = $Results['total'] > $this->Settings['Admin']->MaxMatches ? 'Top '.$this->Settings['Admin']->MaxMatches : $Results['total'];
    if (isset($_GET['res']))
        $Format = $_GET['res']; //get result display format
    if (isset($Results['total_found'])):

        if (!($Results['total_found'] == 0)): //make sure there is something here
            ?>
            <div id="TitleBar">
                <?php echo T('Search Results for Query') ?>: <span id="SearchQuery"><?php echo $Results['query'] ?></span>
                <!--                Change button text based on GET string if the div is expanded on default-->
                <?php echo $this->Form->Button('Options', array('value' => GetValue('expand', $_GET) == 'yes' ? T('Less Options') : T('More Options'), 'id' => 'Options')); ?>
            </div>
            <div id="NavBar">
                <span id="SearchAgain"><?php echo Anchor('Search Again :: Adv Search', $GETString, FALSE, FALSE, TRUE) ?></span>
                <span id="Time"><?php echo sprintf(T('%s %s in %s'), $Total, Plural($Results['total'], T('result'), T('results')), $Results['time'] . 's') ?></span>
                <?php echo str_replace('=p', '=', $this->Pager->ToString('more')); //get rid of the character 'p' in p1,p2,p3 etc ?>
            </div>
            <?php echo WriteResults($Format, $Results['matches'], TRUE); ?>
            <?php echo str_replace('=p', '=', $this->Pager->ToString('more')); //get rid of the character 'p' in p1,p2,p3 etc ?>

        <?php else: ?>
            <span id="SearchAgain"> <?php echo Anchor(T('Search Again :: Adv Search'), $GETString) ?></span>
            <?php echo $this->Form->Button('Options', array('value' => 'More Options', 'id' => 'Options')); ?>
            <?php echo '<p class="NoResults">', sprintf(T('No results for %s.', 'No results for <b>%s</b>.'), htmlspecialchars($this->SearchTerm)), '</p>'; ?>

        <?php endif ?>
    <?php else: ?>
        <span id="SearchAgain"> <?php echo Anchor(T('Search Again :: Adv Search'), $GETString) ?></span>
        <?php echo $this->Form->Button('Options', array('value' => 'More Options', 'id' => 'Options')); ?>
        <?php echo '<p class="NoResults">', sprintf(T('No results for %s.', 'No results for <b>%s</b>.'), htmlspecialchars($this->SearchTerm)), '</p>'; ?>
    <?php endif ?>
</div>



