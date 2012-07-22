<?php

/**
 */
class TopSearchesModule extends Gdn_Module {

    private $TopSearches = '';

    public function __construct($TopSearches) {
        $this->TopSearches = $TopSearches;
    }

    public function AssetTarget() {
        return 'Panel'; //Add to the custom made BottomPanel @see search.php where this is rendered
    }

    public function ToString() {
        $String = '';
        if (sizeof($this->TopSearches) == 0) {
            return $String; //return an empty string if no results
        }
        ob_start();
        ?>
        <h3 class="Header">Top Searches</h3>
        <div class="Search_Container">
            <?php foreach ($this->TopSearches as $Row): ?>
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