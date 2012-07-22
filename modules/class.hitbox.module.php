<?php

/**
 *
 */
class HitBoxModule extends Gdn_Module {

    private $Words = array();

    public function __construct($Words) {
        $this->Words = $Words;
    }

    public function AssetTarget() {
        return 'Panel';
    }

    public function ToString() {
        $String = '';
        if (sizeof($this->Words) == 0) {
            return $String; //return an empty string if no results
        }
        ob_start();
        ?>
        <h3 class="Header">HitBox</h3>
        <table id="HitBox">
            <thead>
                <tr>
                    <th class="Word">
                        Word
                    </th>
                    <th class="Docs">
                        Docs
                    </th>
                    <th class="Hits">
                        Hits
                    </th>
                </tr>
            </thead>
            <tbody>
        <?php foreach ($this->Words as $Word => $WordArray): ?>
                    <tr>
                        <td class="Word">
            <?php echo $Word ?>
                        </td>
                        <td class="Docs">
            <?php echo Gdn_Format::BigNumber($WordArray['docs']) ?>
                        </td>
                        <td class="Hits">
            <?php echo Gdn_Format::BigNumber($WordArray['hits']) ?>
                        </td>
                    </tr>
        <?php endforeach ?>
            </tbody>
        </table>
        <?php
        $String .= ob_get_contents();
        @ob_end_clean();

        return $String;
    }

}
