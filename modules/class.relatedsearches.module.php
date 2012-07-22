<?php

/**
 */
class RelatedSearchesModule extends Gdn_Module {

    private $RelatedSearches = '';

    public function __construct($RelatedSearches) {
        $this->RelatedSearches = $RelatedSearches;
    }

    public function AssetTarget() {
        return 'Panel'; //Add to the custom made BottomPanel @see search.php where this is rendered
    }

    public function ToString() {
        $String = '';
        if (sizeof($this->RelatedSearches) == 0) {
            return $String; //return an empty string if no results
        }
        ob_start();
        ?>
        <h3 class="Header">Related Searches</h3>
        <div class="Search_Container">
            <?php foreach ($this->RelatedSearches as $Row): ?>
            <?php $QueryString = 'search/results?q=' . str_replace(' ', '+', $Row->keywords); ?>
                <h4 class="DiscussionTitle"><?php echo Anchor($Row->keywords, $QueryString) ?></h4>
            <?php endforeach ?>
        </div>
        <?php
        $String .= ob_get_contents();
        @ob_end_clean();

        return $String;
    }

}