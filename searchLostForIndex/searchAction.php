<?php  

include_once __DIR__ . '/../dbconn.php'; 

$slaveConn = getSlaveConnection();

$keyword = $_GET['keyword'];

$stmt = $slaveConn->prepare(
    "SELECT * FROM lost_items 
    WHERE status = 'approved' 
    AND (
        itemName LIKE :keyword 
        OR description LIKE :keyword 
        OR locationFound LIKE :keyword 
        OR pickUpLocation LIKE :keyword 
        OR dateFound LIKE :keyword 
    ) 
    ORDER BY dateFound DESC, itemName ASC ");
$stmt->execute([':keyword' => "%$keyword%"]);
$results = $stmt->fetchAll();

if($results) {
    include __DIR__ . '/tableHead.php';
    ob_start();
    foreach($results as $data) {
    $itemName = htmlspecialchars($data['itemName'], ENT_QUOTES);
    $description = htmlspecialchars($data['description'], ENT_QUOTES);
    $locationFound = htmlspecialchars($data['locationFound'], ENT_QUOTES);
    $pickUpLocation = htmlspecialchars($data['pickUpLocation'], ENT_QUOTES);
    $dateFound = htmlspecialchars($data['dateFound'], ENT_QUOTES);
    $returneeName = htmlspecialchars($data['returneeName'], ENT_QUOTES);
    $imagePath = htmlspecialchars($data['imagePath'], ENT_QUOTES);
    include __DIR__ . '/results.php';
    echo ob_get_clean();
}
include __DIR__ . '/tableTail.php';
} else {
    echo "<BR>No results found.";
}


?>