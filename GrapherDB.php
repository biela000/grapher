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

    public function getdataassociatedwithlabels()
    {
        $data = array();
        $query = "SELECT `axis-x`.name, `data`.value FROM `axis-x` INNER JOIN `data` ON `axis-x`.name = `data`.axis_x_value";
        $result = $this->connection->query($query);
        while ($row = $result->fetch_assoc()) {
            $data[$row['name']][] = $row['value'];
        }
        return $data;
    }
}