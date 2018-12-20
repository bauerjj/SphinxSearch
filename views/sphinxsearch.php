<?php
if (!defined('APPLICATION'))
    exit();
?>

<style type="text/css" media="screen">
    body{ font-size:.9em; }
    .old{ background-color:#246499;}
    .new{ }
    .error{ background-color:#992E36;}

    #Content form ul{
        padding: 2px;
    }

    ol, ul {
        list-style: upper-roman;
        margin-left: 50px;
    }
    .Inner ul{
        list-style: none;
    }
    .Settings{
        list-style: none;
        margin-left: 0px;
    }
    .FootNote{
        font-style: italic;
        font-size: 11px;
        margin-left: 15px;
    }
    div.Info, .DismissMessage{
        line-height: 1.8;
    }
    #Content{
        color: black;
    }

    #Left{
        float: left;
        width: 320px; /*Width of left column*/
        margin-left: -100%;
    }
    #Right{
        margin-left: 320px; /*Set left margin to LeftColumnWidth*/
    }
    #RightWrapper{
        float: left;
        width: 100%;
    }
    #MainWrapper{
    }
    .Inner{
        margin: 10px;
    }
    #messages{
        color: #00FF00;
        background-color: black;
        border: 2px solid silver;
    }
    .Success{
        color: green;
    }
    .Fail{
        color: red;
    }
    .background{
        margin-left: 20px;
    }
</style>

<?php echo $this->Form->Errors(); ?>
<?php echo $this->Form->Open(array('method' => 'post')); ?>
<?php $Settings = $this->Data['Settings'] //Grab all sphinx related settings/status ?>

<div class="Help Aside">
    <?php
    echo '<h2>', T('Need More Help?'), '</h2>';
    echo '<ul>';
    echo '<li>', Anchor(T('Github'), 'https://github.com/bauerjj/SphinxSearch'), '</li>';
    echo '<li>', Anchor(T('Offical Sphinx Documentation'), 'http://sphinxsearch.com/docs/current.html'), '</li>';
    echo '</ul>';
    ?>
</div>
<h1><?php echo T($this->Data['Title']) ?></h1>
<div class="Info">
    <?php echo T($this->Data['PluginDescription']); ?>
</div>


<h3><?php echo 'Quick Links'; ?></h3>
<div class="Info">
    <ol>
        <li><?php echo Anchor('Install Wizard', 'plugin/sphinxsearch/installwizard'); ?></li>
        <li><?php echo Anchor('Settings', 'plugin/sphinxsearch/settings'); ?></li>
    </ol>
</div>
<br/>
<div class="Infoo">
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
</div>
<div id="ControlPanel">
    <br/>
    <table class="CPanel Searchd">
        <tbody>
            <tr>
                <th class="Desc">Sphinx Daemon </th>
                <th></th>
            </tr>
            <tr>
                <td>Status: </td>
                <td> <?php if ($Settings['Status']->SearchdRunning) Success('Running');
    else Fail('Not Running'); ?> </td>
            </tr>
        </tbody>
    </table>
    <br/>

</div>
<br/>

<?php

function Success($Text) {
    echo '<span class="Success">' . $Text . '</span>';
}

function Fail($Text) {
    echo '<span class="Fail">' . $Text . '</span>';
}
?>