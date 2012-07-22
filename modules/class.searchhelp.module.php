<?php

/**
 * Search help text for the main search page
 *
 */
class SearchHelpModule extends Gdn_Module {

    private $String = '';

    public function AssetTarget() {
        return 'LeftPanel'; //Add to the custom made LeftPanel @see search.php where this is rendered
    }

    public function ToString() {
        ob_start();
        ?>
        <div id="LeftPanel">
            <!-- Column 3 start (right) -->
            <h3 class="Header">Search Help</h3>
            <b>Fully Searchable Fields:</b>
            <ol>
                <li>Thread Title (@title) </li>
                <li>Comment Body Text (@body)</li>
                <li>Author Name (@user)</li>
            </ol>

            <b>Special operators can be used:</b>
            <ul>
                <li><span>Operator OR: </span>
                    <p class="SearchExamples">hello | world</p>
                </li>
                <li><span>Operator NOT: </span>
                    <p class="SearchExamples">hello !world</p>
                </li>
                <li><span>Exact Phrase</span>
                    <p class="SearchExamples">"hello world"</p>
                </li>
                <li><span>Field Search </span>
                    <p class="SearchExamples">@title hello @body world</p>
                </li>
                <li><span>Multiple-Field Search</span>
                    <p class="SearchExamples">@(title,body) hello world</p>
                </li>
                <li><span>All-Field </span>
                    <p class="SearchExamples">@* hello</p>
                </li>
                <li><span>Quorum Matching</span>
                    <p class="SearchExamples">"the world is terrible"/3</p>
                </li>
                <li><span>Generalized Proximity</span>
                    <p class="SearchExamples">hello NEAR/3 world NEAR/4</p>
                </li>
                <li><span>Zone Limit</span>
                    <p class="SearchExamples">ZONE:(h3,h4) only in these titles</p>
                </li>

            </ul>
        </div>

        <?php
        $String = ob_get_contents();
        @ob_end_clean();
        return $String;
    }

}