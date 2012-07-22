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
        return 'BottomPanel'; //Add to the custom made BottomPanel @see search.php where this is rendered
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
            //We want 25% of the words to fit into each catagory above

            $Limit = floor($Percentage * $Total); //per category
            foreach ($Words as $Word) {
                $CssClass = $CssClasses[$Offset];
                $Return []= Anchor($Word, 'search/results?q=' . ($Word), $CssClass).' ';
                if (++$Count == $Limit) {
                    $Count = 0;
                    $Offset++;
                }
            }
            shuffle($Return); //randomaize
            foreach($Return as $Anchor)
                $String .= $Anchor;
            return $String;
        }
    }

    public function ToString() {
        $String = '<div id="BottomPanel">';
        $String .= '<h3 class="Header">Tag Cloud</h3>';
        $String .= $this->WriteKeywordsCloud($this->KeywordsArray);
        return $String . '</div>';
    }

}