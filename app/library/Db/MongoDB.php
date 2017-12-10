<?php
namespace Db;

class MongoDB
{
    public function __construct()
    {
        $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    }
}

