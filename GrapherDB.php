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

    public function getlabels()
    {
        $labels = array();
        $query = "SELECT name FROM `axis-x`";
        $result = $this->connection->query($query);
        while ($row = $result->fetch_assoc()) {
            $labels[] = $row['name'];
        }
        return $labels;
    }
}