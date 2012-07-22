<?php

/**
 */
class RelatedDiscussionModule extends Gdn_Module {

    private $RelatedDiscussions = '';

    public function __construct($RelatedDiscussions) {
        $this->RelatedDiscussions = $RelatedDiscussions;
    }

    public function AssetTarget() {
        return 'Panel'; //Add to the custom made BottomPanel @see search.php where this is rendered
    }

    public function ToString() {
        return $this->RelatedDiscussions;
    }

}
