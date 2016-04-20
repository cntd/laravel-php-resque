<?php
use Kodeks\PhpResque\Lib\ResqueJobInterface;

class TestOutputJob implements ResqueJobInterface{
    public function perform() {  
        
    }
    public function setUp() {
        echo json_encode($this->args);
    }
    public function tearDown() {

    }
}