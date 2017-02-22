<?php


class ExampleTest extends TestCase
{
    /**
     * A basic functional test example.
     */
    public function testBasicExample()
    {
        $this->visit('/')
             ->see('Nanobox Cloud Provider – Proxmox')
             ->dontSee('Laravel')
             ->dontSee('Rails')
             ->dontSee('Swagger');
    }
}
