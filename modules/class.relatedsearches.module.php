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
        <div id="RelatedSearches" class="Box RelatedSearches">
            <h4 class="Header"><?php echo T('Related Searches') ?></h4>
            <ul class="PanelInfo PanelDiscussions">
                <?php foreach ($this->RelatedSearches as $Row): ?>
                <li class="Item">
                    <?php $QueryString = 'search/results?Search=' . str_replace(' ', '+', $Row->keywords); ?>
                    <?php echo Anchor($Row->keywords, $QueryString, '', array('class'=>'Title')) ?>
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