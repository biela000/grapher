<?php

class GrapherDB
{
    private $connection = null;

    public function __construct()
    {
        $this->connection = new mysqli("localhost", "root", "", "grapher");
    }

    public function __destruct()
    {
        $this->connection->close();
    }
}