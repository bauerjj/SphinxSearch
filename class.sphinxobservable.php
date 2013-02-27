<?php

class SphinxObservable implements SplSubject {

    public $_Status = array(); //log of stuff that is happening
    public $_Storage = array(); //storage of objects that are subscribed

    public function __construct() {
        //create subclasses
        $this->_Storage = new SplObjectStorage();
    }

    public function Attach(SplObserver $Observer) {
        $this->_Storage->attach($Observer);
    }

    public function Detach(SplObserver $Observer) {
        $this->_Storage->detach($Observer);
    }

    public function GetStatus() {
        return $this->_Status; //classic get operation
    }

    public function Notify() {
        foreach ($this->_Storage as $Observer) {
            $Observer->update($this);
        }
    }

    public function Update($Level, $SettingsName, $Value = FALSE, $Msg = FALSE) {
        $this->_Status [] = array(
            'Name' => 'test',
            'Priority' => $Level,
            'SettingsName' => $SettingsName,
            'Value' => $Value,
            'Message' => $Msg
        );
        $this->Notify(); //let the subscribers know of this
    }

}