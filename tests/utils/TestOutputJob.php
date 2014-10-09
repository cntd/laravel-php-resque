<?php

class TestOutputJob {
    public function perform() {  
        
    }
    public function setUp() {
        echo json_encode($this->args);
    }
    public function tearDown() {

    }
}