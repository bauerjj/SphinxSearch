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
        <div id="TopSearches" class="Box TopSearches">
            <h4 class="Header"><?php echo T('Top Searches') ?></h4>
            <ul class="PanelInfo PanelDiscussions">
                <?php foreach ($this->TopSearches as $Row): ?>
                <li class="Item">
                    <?php $QueryString = 'search/results?Search=' . str_replace(' ', '+', $Row->keywords); ?>
                    <?php echo Anchor($Row->keywords, $QueryString, array('class'=>'Title')) ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
        <?php
        $String .= ob_get_contents();
        @ob_end_clean();

        return $String;
    }

}