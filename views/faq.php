<?php if (!defined('APPLICATION'))
    exit(); ?>

<style>

    .Q{
        font-size: large;
    }





</style>
<div class="Help Aside">
    <?php
    echo '<h2>', T('Need More Help?'), '</h2>';
    echo '<ul>';
    echo '<li>', Anchor(T('Install FAQ'), 'plugin/sphinxsearch/sphinxfaq'), '</li>';
    echo '<li>', Anchor(T('Vanilla Forums Install Thread'), ''), '</li>';
    echo '<li>', Anchor(T('Offical Sphinx Documentation'), 'http://sphinxsearch.com/docs/current.html'), '</li>';
    echo '</ul>';
    ?>
</div>
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
<h3>FAQ</h3>
<br/>
<br/>

<?php
echo '<ul>
            <li><strong>Application Version:</strong> ',APPLICATION_VERSION,'</li>
         <li><strong>PHP Version:</strong> ',PHP_VERSION,'</li>
             <li><strong>Operating System:</strong> ',PHP_OS,'</li>
             <li><strong>Distro:</strong>'. exec('cat /etc/issue') .'</li>
        ';

         if (array_key_exists('SERVER_SOFTWARE', $_SERVER))
            echo '<li><strong>Server Software:</strong> ',$_SERVER['SERVER_SOFTWARE'],"</li>\n";
         echo '</ul>';
?>
<br/>
<h3>Questions / Answers</h3>
<span style="color: red">Be sure to read the main documentation <?php echo Anchor('Here:','http://sphinxsearch.com/docs/current.html'); ?></span>
<ol>
    <li class="Q">Will this work for me?</li>
    <li>Depends...are you on a shared host? If so, this is probably not for you, but talk to your hosting provider about having a daemon run on your server</li>
    <li>Windows support may come in later releases if there is enough demand</li>
    <br/>
    <li class="Q">How does Sphinx work?</li>
    <li>Sphinx indexes your discussion titles, body, and author names, making them easily searchable</li>
    <li>It does not store your text, but rather uses a special data structure to optimize searching </li>
    <li>Sphinx is as dedicated indexing/query engine, and therefore can do it a lot better, rather than MYSQL/MyISAM</li>
    <br/>
    <li class="Q">Run in Background?</li>
    <li>This lets all of the index and install commands to run in the background. The progress is then printed onto your screen (black terminal look-a-alike).
    The benefit of this is that you can see the progress in real time. A benefit of NOT running in background is that you can spot errors easier, although your browser will
    be waiting for each task to complete and it will appear that the website has frozen. This is not the case...let it finish.</li>
    <br/>
    <li class="Q">What's the deal with the cron files?</li>
    <li> Sphinx needs to reindex your database here and there to stay current. The 'Main' and 'Delta' index work together to achieve optimal results</li>
    <li>You should index 'Main' once in a while, depending on the activity of your forum. Delta should be updated more frequent since it should only update much less than the Main index</li>
    <li>Use the cron files to update sphinx during low peak times. Remember, reindex delta often, and main seldom. More info, see section 3.12 of the main sphinx documenation</li>
    <br/>
    <li class="Q">How do I get rid of some of the top searches/tags?</li>
    <li>Add the words to your stoplist.txt found in the assests folder of this plugin and then reindex. Over time, you should see these dissappear</li>
    <li>Future versions may let you censor this easier, but for now be sure to enable the stopwords feature</li>
    <br/>
    <li class="Q">Error xxxx Permission denied</li>
     <li>This error mostly occurs when NOT using pre packaged sphinx installer. You have to give sphinx read/write permission to all log and data temp files by using CHMOD </li>
     <br/>
    <li class="Q">Control Panel says 10 queries were made, but I only made 1 search?</li>
    <li>Total Queries does not mean 'Total Searches'(i.e 12 queries != 12 individual searches on your site. </li>
    <li>For each search, there are other numerous searches being processed such as related threads and top searches</li>
<br/>
<li class="Q">You get a "stop: kill() on pid xxxx failed: Operation not permitted Sphinx" </li>

<li>This is because you started sphinx from the Command line or some other means</li>
<br/>
    <li class="Q">You get the message along the lines of "Failed to open log/pid file". </li>
    <li>You should kill all instances of searchd by using 'ps' in the command line.
    If that does not work, delete all files in your ../sphinx/var/log folder and reboot</li>
    <br/>
    <li class="Q">My indexes are reindexed through my cron job, but the index time is incorrect</li>
    <li>Yea, I know...this is only updated if you index through the control panel</li>
</ol>
