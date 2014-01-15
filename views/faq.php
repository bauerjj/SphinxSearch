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
        <br/>
        <li class="Q">Alright, it is installed...now what?</li>
        <ul>
            <li>1. Start searchd through the command line</li>
            <li>2. index 'Main' through the command line </li>
            <li>3. index 'Delta' through the command line </li>
            <li>4. index 'Stats' through the command line </li>
            <li>5. Go through the install wizard in this plugin. Simply insert paths and your sphinx.conf.dist file </li>
            <li>6. Search for something on your forums through the usual means </li>
            <li>7. Setup a cron job to run the three cron files</li>
        </ul>
        <br/>
        <li class="Q">How to update?</li>
        <li>Delte the whole plugin folder and then move over the new one. Run through the install wizard once more</li>
        <br/>
        <li class="Q">What if sphinx is indexing?</li>
        <li>Anytime sphinx is indexing with the --rotate switch command, sphinx search will be left in-tact. If not, an error msg will be displayed on the search page</li>
        <br/>
        <li class="Q">What is the indexer and searchd?</li>
        <li>These are two separate entities that work together. Indexer indexes your database fields and searchd listens on your server for search requests to query the indexer </li>
        <br/>
        <li class="Q">How does Sphinx work?</li>
        <li>Sphinx indexes your discussion titles, body, and author names, making them easily searchable</li>
        <li>It does not store your text, but rather uses a special data structure to optimize searching </li>
        <li>Sphinx is as dedicated indexing/query engine, and therefore can do it a lot better, rather than MYSQL/MyISAM</li>
        <br/>
        <li class="Q">What's the deal with the cron files?</li>
        <li> Sphinx needs to reindex your database here and there to stay current. The 'Main' and 'Delta' index work together to achieve optimal results</li>
        <li>You should index 'Main' once in a while, depending on the activity of your forum. Delta should be updated more frequent since it should only update much less than the Main index</li>
        <li>Use the cron files to update sphinx during low peak times. Remember, reindex delta often, and main seldom. More info, see section 3.12 of the main sphinx documenation</li>
        <br/>
        <li class="Q">fsockopen(): unable to connect to localhost::xxxx ....</li>
        <li>First try to start searchd and then check the port again</li>
        <br/>
        <li class="Q">"Failed to open log/pid file" when trying to reindex </li>
        <li>You may need to stop searchd using: `/location/to/searchd/searchd --stop`. You can do it manually by killing all instances of searchd by using 'ps' in the command line.
            If that does not work, delete all files in your ../sphinx/var/log folder and reboot</li>
        <br/>
        <li class="Q">"Failed to open log/pid file" in the `sphinx_cron.log` </li>
        <li>The cron tasks are not running with the correct permissions. Either set the cron jobs to run as `sudo` or the same user as the one who initially started searchd </li>
        <br/>
        <li class="Q">"WARNING: no process found by PID xxxx. WARNING: indices NOT rotated". </li>
        <li>This is most likely caused by multiple instances of searchd running. This can happen if you start searchd and then either install a new instance of sphinx or disable the plugin.
        Solution is to kill al instances of searchd</li>
    </ol>
</div>