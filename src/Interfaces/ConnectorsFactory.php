<?php
namespace Tiny\DbUnit\Interfaces;

interface ConnectorsFactory 
{
    public function makeInMemoryConnector();
}