<?php
$masterConfig = [
    "servername" => "localhost",
    "port" => 3306,
    "username" => "root",
    "password" => "",
    "dbname" => "campusfinds"
];

$slaveConfig = [
    "servername" => "localhost",
    "port" => 3307,
    "username" => "root",
    "password" => "",
    "dbname" => "campusfinds"
];

function getMasterConnection() {
    global $masterConfig;
    
    try {
        $conn = new PDO(
            "mysql:host={$masterConfig['servername']};port={$masterConfig['port']};dbname={$masterConfig['dbname']}",
            $masterConfig['username'],
            $masterConfig['password']
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        echo "Connection to Master Database failed: " . $e->getMessage();
        return null;
    }
}

function getSlaveConnection() {
    global $slaveConfig;
    
    try {
        $conn = new PDO(
            "mysql:host={$slaveConfig['servername']};port={$slaveConfig['port']};dbname={$slaveConfig['dbname']}",
            $slaveConfig['username'],
            $slaveConfig['password']
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        echo "Connection to Slave Database failed: " . $e->getMessage();
        return getMasterConnection();
    }
}

?>