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
        <div id="RelatedThreads" class="Box RelatedThreads">
            <h4 class="Header"><?php echo T('Related Threads') ?></h4>
            <?php echo WriteResults('Simple', $this->RelatedThreads, FALSE, TRUE); ?>
        </div>
        <?php
        $String .= ob_get_contents();
        @ob_end_clean();

        return $String;
    }

}