<?php
if (!defined('APPLICATION'))
    exit();
?>

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
<div class="Info">
    <h2>System Info:</h4>

    <?php
    echo '<ul>
            <li><strong>Application Version:</strong> ', APPLICATION_VERSION, '</li>
         <li><strong>PHP Version:</strong> ', PHP_VERSION, '</li>
             <li><strong>Operating System:</strong> ', PHP_OS, '</li>
             <li><strong>Distro:</strong>' . exec('cat /etc/issue') . '</li>
        ';

    if (array_key_exists('SERVER_SOFTWARE', $_SERVER))
        echo '<li><strong>Server Software:</strong> ', $_SERVER['SERVER_SOFTWARE'], "</li>\n";
    echo '</ul>';
    ?>
    <br/>
    <h2>Distro Install:</h4>
    It may be easier to install sphinx through your linux distro. Once installed, use the manual installation method inside of the install wizard. Here are some known solutions for distros:

    <dl>
        <dt><b>Arch Linux:</b></dt>
        <dd>Download the package found in the AUR <?php echo Anchor('HERE','http://aur.archlinux.org/packages.php?ID=17178') ?></dd>
    </dl>
</div>
<h3>Questions / Answers</h3>
<div class="Info">
    <span style="color: red">Be sure to read the main documentation <?php echo Anchor('Here:', 'http://sphinxsearch.com/docs/current.html'); ?></span>
    <ol>
        <li class="Q">Will this work for me?</li>
        <li>Depends...are you on a shared host? If so, this is probably not for you, but talk to your hosting provider about having a daemon run on your server.</li>
        <li>Windows support may come in later releases if there is enough demand. If the auto installer does not work for you, try installing using your distro's package manger
        and then telling the plugin where the requested files are.</li>
        <br/>
        <li class="Q">Can't find indexer at path: Not Detected</li>
        <li> You will encounter this at the control panel if sphinx is not properly installed. The control will not respond to anything until these paths are resolved by using the install wizard</li>
        <br/>
        <li class="Q">Alright, it is installed...now what?</li>
        <ul>
            <li>1. Start searchd on the control panel</li>
            <li>2. index 'Main' through the control panel </li>
            <li>3. index 'Delta' through the control panel </li>
            <li>4. index 'Stats' through the control panel </li>
            <li>5. Stop and then start searchd again</li>
            <li>6. Search for something on your forums through the usual means </li>
            <li>7. Setup a cron job to run the three cron files</li>
        </ul>
        <br/>
        <li class="Q">How to update?</li>
        <li>If you installed sphinx using the packaged tarball that came with the plugin, then you should FTP over everything in the plugin folder EXCEPT the install folder!</li>
        <li>The install folder is where sphinx is installed, so do not overwrite this. If you do, you must run the intire install wizard over again</li>
        <li>If the plugin is disabled, you must also go through the install wizard again. The compiled files will still be there, so it won't take so long</li>
        <li>After an updated, you should write the config file again in the control panel</li>
        <br/>
        <li class="Q">What if sphinx is indexing and it shuts down searchd...now what?</li>
        <li>Anytime sphinx is indexing, it will shut down all searches temporary (unless you have another instance of searchd setup). The default search will be in effect immidiatly until searchd is running again.
            This is done automatically for you</li>
        <br/>
        <li class="Q">What is the indexer and searchd?</li>
        <li>These are two seperate entities that work together. Indexer indexes your database fields and searchd listens on your server for search requests to query the indexer </li>
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
        <li>This is because you started sphinx from the Command line or some other means and now there are user permission problems...stop searchd through the same means you started it with</li>
        <br/>
        <li class="Q">fsockopen(): unable to connect to localhost::xxxx ....</li>
        <li>First try to start searchd and then check the port again</li>
        <br/>
        <li class="Q">"Failed to open log/pid file". </li>
        <li>You should kill all instances of searchd by using 'ps' in the command line.
            If that does not work, delete all files in your ../sphinx/var/log folder and reboot</li>
        <br/>
        <li class="Q">"WARNING: no process found by PID xxxx. WARNING: indices NOT rotated". </li>
        <li>This is most likely caused by multiple instances of searchd running. This can happen if you start searchd and then either install a new instance of sphinx or disable the plugin.
        Solution is to click the button 'Kill Searchd(d)' in the control panel and then start searchd and reindex</li>
        <br/>
        <li class="Q">My indexes are reindexed through my cron job, but the index time is incorrect</li>
        <li>Yea, I know...this is only updated if you index through the control panel</li>
    </ol>
</div>