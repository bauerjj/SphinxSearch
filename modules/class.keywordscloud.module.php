<?php

/**
 * Displays the keywords hitbox on the bottom of the main search page
 */
class KeywordsCloudModule extends Gdn_Module {

    private $KeywordsArray = '';

    public function __construct($KeywordsArray) {
        $this->KeywordsArray = $KeywordsArray;
    }

    public function AssetTarget() {
        return 'Panel'; //Add to the custom made BottomPanel @see search.php where this is rendered
    }

    /**
     *
     * @param array $Words in order or most frequent first
     */
    private function WriteKeywordsCloud($Words) {
        $Total = sizeof($Words);
        $String = '';
        $Offset = 0; //into the CssClasses array
        $Count = 0; //loop iteration
        $Percentage = .20; //20% of words fit into 1 of 5 categories
        $Return = array();
        $CssClasses = array(
            'largest',
            'large',
            'medium',
            'small',
            'smallest',
        );
        if ($Total <= 0 || !is_array($Words))
            return $String;
        else {
            //split words into 5 catagories (smallest, small, medium, large, largest)
            //We want 20% of the words to fit into each catagory above

            $Limit = ceil($Percentage * $Total); //per category - don't use floor since that will not include all of the keywords then
            foreach ($Words as $Word) {
                if(key_exists($Offset, $CssClasses)) { //make sure the offset does not increment past 5
                    $CssClass = GetValue($Offset,$CssClasses);
                    $Return [] = Anchor($Word, 'search/results?Search=' . ($Word), $CssClass) . ' ';
                    if (++$Count == $Limit) {
                        $Count = 0;
                        $Offset++;
                    }
                }
            }
            shuffle($Return); //randomaize
            foreach ($Return as $Anchor)
                $String .= $Anchor;
            return $String;
        }
    }

    public function ToString() {
        ob_start();
        ?>
        <div id="Keywords" class="Box Keywords">
            <h4 class="Header"><?php echo T('Keyword Cloud') ?></h4>
            <ul class="PanelInfo PanelDiscussions">
                <li class="Item">
                    <?php echo $this->WriteKeywordsCloud($this->KeywordsArray) ?>
                </li>
            </ul>
        </div>
        <?php
        $String = ob_get_contents();
        @ob_end_clean();
        return $String;
    }

}