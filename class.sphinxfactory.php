<?php

/**
 * Factory class for the Sphinx Search plugin
 */
class SphinxFactory{

    public static function BuildSphinx($Sender, $View){
        return new SphinxSearchAdmin($Sender,$View);
    }

    public static function BuildObservable(){
        return new SphinxObservable();
    }

    public static function BuildInstall($Settings){
        return new SphinxSearchInstall($Settings);
    }

    public static function BuildSettings(){
        return  SphinxSearchSettings::GetInstance();
    }

    public static function BuildService($Settings){
        return new SphinxSearchService($Settings);
    }

    public static function BuildWizard($Settings){
        return new SphinxSearchInstallWizard($Settings);
    }

    public static function BuildAPI(){
        return new SphinxClient(); //ships with the sphinx search engine
    }

}