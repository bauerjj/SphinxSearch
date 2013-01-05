<style>
    td.Input{
        width: 150px;
    }
    .Filler{
        background-color: grey;
    }
    .astrix{
        color: red;
    }
    ol, ul {
        list-style: upper-roman;
        margin-left: 50px;
    }



</style>
<h1><?php echo T($this->Data['Title']) . ' - ' . $this->Data['PluginVersion']; ?></h1>
<div class="Info">
    <?php echo T($this->Data['PluginDescription']); ?>
</div>
<h3><?php echo 'Quick Links'; ?></h3>
<div class="Info">
    <ul>
        <li><?php echo Anchor('Back To Control Panel', 'plugin/sphinxsearch'); ?></li>
    </ul>
</div>
<h3>Settings</h3>
<br/>
<br/>
<?php
$Settings = $this->Data['Settings'];
//print_r($Settings); die;
echo $this->Form->Open();
echo $this->Form->Errors();
?>
<div class="FilterMenu">
    <?php
    $ToggleName = $Settings['Admin']->MainSearchEnable ? T('Disable Main Search') : T('Enable Main Search');
    echo "<div>" . Wrap(Anchor($ToggleName, 'plugin/sphinxsearch/toggle/' . Gdn::Session()->TransientKey() . '/?action=mainsearch', 'SmallButton')) . "</div>";
    ?>
</div>
<?php if ($Settings['Admin']->MainSearchEnable) : ?>
    <table class="AltRows">
        <thead>
            <tr>
                <th> Main Search - Settings</th>
                <th> Description </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitResultsPage'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of results per page', 'Plugin.SphinxSearch.LimitResultsPage'); ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->CheckBox('Plugin.SphinxSearch.MainHitBoxEnable', 'HitBox'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Enable the HitBox', 'Plugin.SphinxSearch.MainHitBoxEnable'); ?>
                </td>
            </tr>
        </tbody>
    </table>

<?php endif ?>



<br/>
<div class="FilterMenu">
    <?php
    $ToggleName = $Settings['Admin']->StatsEnable ? T('Disable Stats') : T('Enable Stats');
    echo "<div>" . Wrap(Anchor($ToggleName, 'plugin/sphinxsearch/toggle/' . Gdn::Session()->TransientKey() . '/?action=stats', 'SmallButton')) . "</div>";
    ?>
</div>
<?php if ($Settings['Admin']->StatsEnable) : ?>
    <table class="AltRows">
        <thead>
            <tr>
                <th> Related Searches - Settings</th>
                <th> Description </th>
                <th> View Format </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitRelatedSearches'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of related FULL searches on the main search page (0 to disable)', 'Plugin.SphinxSearch.LimitRelatedSearches'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('test', array('simple'), array('disabled' => 'disabled')) ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitTopKeywords'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of top SINGLE keywords on the main search page (0 to disable)', 'Plugin.SphinxSearch.LimitTopKeywords'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('test', array('simple'), array('disabled' => 'disabled')) ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitTopSearches'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of top FULL searches on the main search page (0 to disable)', 'Plugin.SphinxSearch.LimitTopSearches'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('test', array('simple'), array('disabled' => 'disabled')) ?>
                </td>
            </tr>

        </tbody>
    </table>
    <br/>
<?php endif ?>

<br/>
<div class="FilterMenu">
    <?php
    $ToggleName = $Settings['Admin']->RelatedEnable ? T('Disable Related') : T('Enable Related');
    echo "<div>" . Wrap(Anchor($ToggleName, 'plugin/sphinxsearch/toggle/' . Gdn::Session()->TransientKey() . '/?action=related', 'SmallButton')) . "</div>";
    ?>
</div>
<?php if ($Settings['Admin']->RelatedEnable) : ?>
    <table class="AltRows">
        <thead>
            <tr>
                <th> Related Threads - Settings</th>
                <th> Description </th>
                <th> View Format </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitRelatedThreadsSidebarDiscussion'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of related threads on the sidebar panel with every discussion  (0 to disable)', 'Plugin.SphinxSearch.LimitRelatedThreadsSidebarDiscussion'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('test', array('simple'), array('disabled' => 'disabled')) ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitRelatedThreadsMain'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of related threads on the main search sidebar (0 to disable)', 'Plugin.SphinxSearch.LimitRelatedThreadsMain'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('test', array('simple'), array('disabled' => 'disabled')) ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitRelatedThreadsPost'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of results that pop up when adding a new discussion (0 to disable)', 'Plugin.SphinxSearch.LimitRelatedThreadsPost'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('Plugin.SphinxSearch.RelatedThreadsPostFormat', array('simple' => 'simple', 'classic' => 'classic', 'sleek' => 'sleek', 'table' => 'table')) ?>
                </td>
            </tr>
            <tr>
                <td class="Input">
                    <?php echo $this->Form->Textbox('Plugin.SphinxSearch.LimitRelatedThreadsBottomDiscussion'); ?>
                </td>
                <td>
                    <?php echo $this->Form->Label('Number of related threads on the bottom of each discussion thread (0 to disable)', 'Plugin.SphinxSearch.LimitRelatedThreadsBottomDiscussion'); ?>
                </td>
                <td>
                    <?php echo $this->Form->DropDown('Plugin.SphinxSearch.RelatedThreadsBottomDiscussionFormat', array('simple' => 'simple', 'classic' => 'classic', 'sleek' => 'sleek', 'table' => 'table')) ?>
                </td>
            </tr>
        </tbody>
    </table>
    <br/>
<?php endif ?>

<table class="AltRows">
    <thead>
        <tr>
            <th> Searchd - Settings</th>
            <th> Description </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.MaxQueryTime'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Maximum time(ms) alloted for each query', 'Plugin.SphinxSearch.MaxQueryTime'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.RetriesCount'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Number of Retries', 'Plugin.SphinxSearch.RetriesCount'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.RetriesDelay'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Delay of retries (ms)', 'Plugin.SphinxSearch.RetriesDelay'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.ReadTimeout'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Network Read Timeout (s)', 'Plugin.SphinxSearch.ReadTimeout'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.ClientTimeout'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Client Read Timeout (s)', 'Plugin.SphinxSearch.ClientTimeout'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.MaxChildren'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Maximum amount of children to fork (or in other words, concurrent searches', 'Plugin.SphinxSearch.MaxChildren'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.MaxMatches'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Maximum amount of matches', 'Plugin.SphinxSearch.MaxMatches'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.ReadBuffer'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Read Buffer size', 'Plugin.SphinxSearch.ReadBuffer'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->DropDown('Plugin.SphinxSearch.Workers', array('none' => 'none', 'fork' => 'fork', 'prefork' => 'prefork', 'threads' => 'threads')); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Workers', 'Plugin.SphinxSearch.Workers'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.ThreadStack'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Thread Stack', 'Plugin.SphinxSearch.ThreadStack'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.ExpansionLimit'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Expansion Limit', 'Plugin.SphinxSearch.ExpansionLimit'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.PreforkRotationThrottle'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Prefork Rotation Throttle', 'Plugin.SphinxSearch.PreforkRotationThrottle'); ?>
            </td>
        </tr>

    </tbody>
</table>
<table class="AltRows">
    <thead>
        <tr>
            <th> Indexer - Settings</th>
            <th> Description </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.MemLimit'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Size of memory that sphinx will consume (keep the "M")', 'Plugin.SphinxSearch.MemLimit'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.MaxIOps'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Maximum I/O operations per second, for I/O throttling. indexer can cause bursts of intensive disk I/O during indexing, and it might desired to limit its disk activity (and keep something for other programs running on the same machine, such as searchd). I/O throttling helps to do that. It works by enforcing a minimum guaranteed delay between subsequent disk I/O operations performed by indexer. Modern SATA HDDs are able to perform up to 70-100+ I/O operations per second (thats mostly limited by disk heads seek time). Limiting indexing I/O to a fraction of that can help reduce search performance dedgradation caused by indexing. ', 'Plugin.SphinxSearch.MemIOps'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.MaxIOSize'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('I/O throttling related option. It limits maximum file I/O operation (read or write) size for all operations performed by indexer. A value of 0 means that no limit is imposed. Reads or writes that are bigger than the limit will be split in several smaller operations, and counted as several operation by max_iops setting. At the time of this writing, all I/O calls should be under 256 KB (default internal buffer size) anyway, so max_iosize values higher than 256 KB must not affect anything. ', 'Plugin.SphinxSearch.MaxIOSize'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.WriteBuffer'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Write buffer size, bytes. Write buffers are used to write both temporary and final index files when indexing. Larger buffers reduce the number of required disk writes. Memory for the buffers is allocated in addition to mem_limit. Note that several (currently up to 4) buffers for different files will be allocated, proportionally increasing the RAM usage. ', 'Plugin.SphinxSearch.WriteBuffer'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.MaxFileBuffer'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Maximum file field adaptive buffer size, bytes. File field buffer is used to load files referred to from sql_file_field columns. This buffer is adaptive, starting at 1 MB at first allocation, and growing in 2x steps until either file contents can be loaded, or maximum buffer size, specified by max_file_field_buffer directive, is reached', 'Plugin.SphinxSearch.MaxFileBuffer'); ?>
            </td>
        </tr>
    </tbody>
</table>
<table class="AltRows">
    <thead>
        <tr>
            <th> BuildExcerpts - Settings</th>
            <th> Description </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.BuildExcerptsAround'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Maximum snippet size, in symbols (0 to disable).', 'Plugin.SphinxSearch.BuildExcerptsAround'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.BuildExcerptsLimit'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Number of words to highlight around the target word (0 to disable)', 'Plugin.SphinxSearch.BuildExcerptsLimit'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Checkbox('Plugin.SphinxSearch.BuildExcerptsTitleEnable', 'Highlight titles Enable'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label("Highlight matching words in discussion titles", 'Plugin.SphinxSearch.BuildExcerptsTitleEnable'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Checkbox('Plugin.SphinxSearch.BuildExcerptsBodyEnable', 'Highlight body text Enable'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label("Highlight matching words in body text", 'Plugin.SphinxSearch.BuildExcerptsBodyEnable'); ?>
            </td>
        </tr>
    </tbody>
</table>

<br/>

<table class="AltRows">
    <thead>
        <tr>
            <th> Index - Settings</th>
            <th> Description </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="Input">
                <?php echo $this->Form->DropDown('Plugin.SphinxSearch.Morphology', array('none' => 'none', 'stem_en' => 'stem_en', 'stem_ru' => 'stem_ru', 'stem_enru' => 'stem_enru', 'stem_cz' => 'stem_cz', 'soundex' =>'soundex', 'metaphone' => 'metaphone')); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('preprocessors can be applied to the words being indexed to replace different forms of the same word with the base, normalized form. For instance, English stemmer will normalize both "dogs" and "dog" to "dog", making search results for both searches the same. ', 'Plugin.SphinxSearch.Morphology'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->DropDown('Plugin.SphinxSearch.Dict', array('crc' => 'crc', 'dict' => 'dict')); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Essentially, keywords and CRC dictionaries represent the two different trade-off substring searching decisions. You can choose to either sacrifice indexing time and index size in favor of top-speed worst-case searches (CRC dictionary), or only slightly impact indexing time but sacrifice worst-case searching time when the prefix expands into very many keywords (keywords dictionary). ', 'Plugin.SphinxSearch.Dict'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.MinStemmingLen'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Minimum word length at which to enable stemming. Stemmers are not perfect, and might sometimes produce undesired results. For instance, running "gps" keyword through Porter stemmer for English results in "gp", which is not really the intent. min_stemming_len feature lets you suppress stemming based on the source word length, ie. to avoid stemming too short words.', 'Plugin.SphinxSearch.MinStemmingLen'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Checkbox('Plugin.SphinxSearch.StopWordsEnable', 'StopWords Enable'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label("Stopwords are the words that will not be indexed. Typically you'd put most frequent words in the stopwords list because they do not add much value to search results but consume a lot of resources to process", 'Plugin.SphinxSearch.StopWordsEnable'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Checkbox('Plugin.SphinxSearch.WordFormsEnable', 'WordForms Enable'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Word forms are applied after tokenizing the incoming text by charset_table rules. They essentialy let you replace one word with another. Normally, that would be used to bring different word forms to a single normal form (eg. to normalize all the variants such as "walks", "walked", "walking" to the normal form "walk").', 'Plugin.SphinxSearch.WordFormsEnable'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.MinWordIndexLen'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label("Minimum indexed word length. Optional, default is 1 (index everything). Only those words that are not shorter than this minimum will be indexed. For instance, if min_word_len is 4, then 'the' won't be indexed, but 'they' will be.", 'Plugin.SphinxSearch.MinWordIndexLen'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.MinPrefixLen'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Minimum word prefix length to index. Prefix indexing allows to implement wildcard searching by "wordstart*" wildcards (refer to enable_star option for details on wildcard syntax). For instance, indexing a keyword "example" with min_prefix_len=3 will result in indexing "exa", "exam", "examp", "exampl" prefixes along with the word itself. Searches against such index for "exam" will match documents that contain "example" word, even if they do not contain "exam" on itself. However, indexing prefixes will make the index grow significantly (because of many more indexed keywords), and will degrade both indexing and searching times. ', 'Plugin.SphinxSearch.MinPrefixLen'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.MinInfixLen'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label("Minimum infix prefix length to index. Infix indexing allows to implement wildcard searching by 'start*', '*end', and '*middle*' wildcards (refer to enable_star option for details on wildcard syntax). When mininum infix length is set to a positive number, indexer will index all the possible keyword infixes (ie. substrings) in addition to the keywords themselves." . ' For instance, indexing a keyword "test" with min_infix_len=2 will result in indexing "te", "es", "st", "tes", "est" infixes along with the word itself. Searches against such index for "es" will match documents that contain "test" word, even if they do not contain "es" on itself. However, indexing infixes will make the index grow significantly (because of many more indexed keywords), and will degrade both indexing and searching times.', 'Plugin.SphinxSearch.MinInfixLen'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Checkbox('Plugin.SphinxSearch.StarEnable', 'Wildcard Enable'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('This feature enables "star-syntax", or wildcard syntax, when searching through indexes which were created with prefix or infix indexing enabled.', 'Plugin.SphinxSearch.StarEnable'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.NGramLen'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('N-grams provide basic CJK (Chinese, Japanese, Korean) support for unsegmented texts. The issue with CJK searching is that there could be no clear separators between the words. Ideally, the texts would be filtered through a special program called segmenter that would insert separators in proper locations. However, segmenters are slow and error prone, and its common to index contiguous groups of N characters, or n-grams, instead. ', 'Plugin.SphinxSearch.NGramLen'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Checkbox('Plugin.SphinxSearch.HtmlStripEnable', 'Strip HTML'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Whether to strip HTML markup from incoming full-text data. HTML entities get decoded and replaced with corresponding UTF-8 characters. Stripper supports both numeric forms (such as &#239;) and text forms (such as &oacute; or &nbsp;). All entities as specified by HTML4 standard are supported. ', 'Plugin.SphinxSearch.HtmlStripEnable'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Checkbox('Plugin.SphinxSearch.OnDiskDictEnable', 'On Disk Dict Enable'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Whether to keep the dictionary file (.spi) for this index on disk, or precache it in RAM. The dictionary (.spi) can be either kept on RAM or on disk. The default is to fully cache it in RAM. That improves performance, but might cause too much RAM pressure, especially if prefixes or infixes were used. Enabling ondisk_dict results in 1 additional disk IO per keyword per query, but reduces memory footprint. ', 'Plugin.SphinxSearch.OnDiskDictEnable'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Checkbox('Plugin.SphinxSearch.InPlaceEnable', 'Enable InPlace'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Whether to enable in-place index inversion. greatly reduces indexing disk footprint, at a cost of slightly slower indexing (it uses around 2x less disk, but yields around 90-95% the original performance). ', 'Plugin.SphinxSearch.InPlaceEnable'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Checkbox('Plugin.SphinxSearch.ExpandKeywordsEnable', 'Expand Keywords'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('Expand keywords with exact forms and/or stars when possible. running -> ( running | *running* | =running ). Expanded queries take naturally longer to complete, but can possibly improve the search quality, as the documents with exact form matches should be ranked generally higher than documents with stemmed or infix matches. ', 'Plugin.SphinxSearch.ExpandKeywordsEnable'); ?>
            </td>
        </tr>
        <tr>
            <td class="Input">
                <?php echo $this->Form->Textbox('Plugin.SphinxSearch.RTMemLimit'); ?>
            </td>
            <td>
                <?php echo $this->Form->Label('RT index keeps some data in memory (so-called RAM chunk) and also maintains a number of on-disk indexes (so-called disk chunks). This directive lets you control the RAM chunk size. Once theres too much data to keep in RAM, RT index will flush it to disk, activate a newly created disk chunk, and reset the RAM chunk. ', 'Plugin.SphinxSearch.RTMemLimit'); ?>
            </td>
        </tr>
    </tbody>
</table>



<span class="astrix">Any change to searchd or indexer require a new config file to be generated and reindex for the changes to take effect</span>

<br/>
<br/>


<?php echo $this->Form->Close('Save'); ?>

