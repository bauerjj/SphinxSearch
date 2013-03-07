<?php

/**
 */
class MemberModule extends Gdn_Module {

    private $Output = '';

    public function __construct($Output) {
        $this->Output = $Output;
    }

    public function AssetTarget() {
        return 'Panel'; //Add to the custom made BottomPanel @see search.php where this is rendered
    }

    public function ToString() {
        return $this->Output;
    }

}
