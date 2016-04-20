<?php
use Kodeks\PhpResque\Lib\ResqueJobInterface;

class TestExceptionJob implements ResqueJobInterface {
    public function perform() {  
        
    }
    public function setUp() {
        throw new Exception(json_encode($this->args));
    }
    public function tearDown() {

    }
}