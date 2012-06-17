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
$Results = $this->Data['Main']; //main search results
$RelatedThreads = $this->Data['Related_Main']; //related threads on the sidebare
$GETString = explode('&', $_SERVER['QUERY_STRING']); //use this to providea link back to search
if(isset($Results['total_found'])):

if (!($Results['total_found'] == 0)): //make sure there is something here
    ?>
    <div class="colmask leftmenu">
        <div class="colright">
            <div class="col1wrap">
                <?php echo WriteFull($Results); ?>
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
    <?php foreach ($Results['words'] as $Word => $WordArray): ?>
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
                <?php echo WriteSimple($RelatedThreads); ?>

            </div>
        </div>
    </div>
<?php else: ?>
    [<span id="SearchAgain"> <?php echo Anchor('Search Again', 'search/?' . substr($_SERVER['QUERY_STRING'], strlen($GETString[0]) + 1)) ?></span>] No records found
<?php endif ?>
<?php endif ?>


