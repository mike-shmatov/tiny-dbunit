<?php
namespace Tiny\DbUnit\Interfaces;

interface DbConnector
{
    public function getPDO();
    public function getDbName();
    public function getDbUser();
    public function getDbPassword();
    public function getDbHost();
}