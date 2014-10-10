<?php

class TestExceptionJob {
    public function perform() {  
        
    }
    public function setUp() {
        throw new Exception(json_encode($this->args));
    }
    public function tearDown() {

    }
}