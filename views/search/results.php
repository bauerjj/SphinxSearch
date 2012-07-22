
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
//print_r($this->Data); die;
$Settings = $this->Data['Settings'];
$Results = $this->Data['MainSearch']; //main search results
$GETString = $this->Data['GETString'];
$Format = 'Simple';
if (isset($_GET['res']))
    $Format = $_GET['res']; //get result display format
if (isset($Results['total_found'])):

    if (!($Results['total_found'] == 0)): //make sure there is something here
        ?>
        <div class="colmask">
            <div id="TitleBar">
                <h1>Search Results for Query: <span id="SearchQuery"><?php echo $Results['query'] ?></span></h1>
            </div>
            <div id="NavBar">
                <span id="SearchAgain"><?php echo Anchor('Search Again', $GETString, FALSE, FALSE, TRUE) ?></span>
                <span id="Time"><?php echo $Results['total_found'] . Plural($Results['total_found'], ' result', ' results') . ' in ' . $Results['time'] . 's' ?></span>
                <?php echo str_replace('=p', '=', $this->Pager->ToString('more')); //get rid of the character 'p' in p1,p2,p3 etc ?>
            </div>
            <?php echo WriteResults($Format, $Results['matches'], TRUE); ?>
            <?php echo str_replace('=p', '=', $this->Pager->ToString('more')); //get rid of the character 'p' in p1,p2,p3 etc ?>

        </div>
    <?php else: ?>
        [<span id="SearchAgain"> <?php echo Anchor('Search Again', $GETString) ?></span>] No records found
    <?php endif ?>
<?php else: ?>
    [<span id="SearchAgain"> <?php echo Anchor('Search Again', $GETString) ?></span>] No records found
<?php endif ?>



