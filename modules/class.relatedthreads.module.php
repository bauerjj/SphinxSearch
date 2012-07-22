<?php

/**
 */
class RelatedThreadsModule extends Gdn_Module {

    private $RelatedThreads = '';

    public function __construct($RelatedThreads) {
        $this->RelatedThreads = $RelatedThreads;
    }

    public function AssetTarget() {
        return 'Panel';
    }

    public function ToString() {
        $String = '';
        if (sizeof($this->RelatedThreads) == 0) {
            return $String; //return an empty string if no results
        }
        ob_start();
        ?>
        <h3 class="Header">Related Threads</h3>
        <?php echo WriteResults('Simple',$this->RelatedThreads); ?>
        <?php
        $String .= ob_get_contents();
        @ob_end_clean();

        return $String;
    }

}