<?php if (!defined('APPLICATION'))
    exit(); ?>
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
<h1><?php echo $this->Data('Title') . ' - Install Wizard'; ?></h1>
<br/>
<div class="FilterMenu">
    <?php echo Anchor('Back To Main Settings', 'plugin/sphinxsearch'); ?>
    </div>

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
<ul>
    <li>You get a "Fatal Error in PHP.define(); Constant NOW already defined - Answer: Grab the latest vanilla version</li>
    <li>Total Queries does not mean 'Total Searches'(i.e 12 queries != 12 individual searches on your site)</li>
    <li></li>
</ul>
