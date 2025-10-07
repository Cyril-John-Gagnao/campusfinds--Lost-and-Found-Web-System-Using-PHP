<?php  

include_once __DIR__ . '/../dbconn.php';

$slaveConn = getSlaveConnection();

$keyword = $_GET['keyword'];

$stmt = $slaveConn->prepare("SELECT * from claims WHERE itemName LIKE :itemName OR description LIKE :description OR claimerName LIKE :claimerName OR claimDate LIKE :claimDate OR pickUpLocation LIKE :pickUpLocation ORDER BY claimDate DESC, itemName ASC" );
$stmt->execute([':itemName'=>"%$keyword%" , ':description' => "%$keyword%", ':claimerName' => "%$keyword%", ':claimDate' => "%$keyword%", ':pickUpLocation' => "%$keyword%"]);
$results = $stmt->fetchAll();
if($results) {
    include __DIR__ . '/claimsTableHead.php';
    ob_start();
    foreach($results as $data) {
    $itemName = htmlspecialchars($data['itemName'], ENT_QUOTES);
    $description = htmlspecialchars($data['description'], ENT_QUOTES);
    $claimerName = htmlspecialchars($data['claimerName'], ENT_QUOTES);
    $claimDate = htmlspecialchars($data['claimDate'], ENT_QUOTES);
    $pickUpLocation = htmlspecialchars($data['pickUpLocation'], ENT_QUOTES);
    $imagePath = htmlspecialchars($data['imagePath'], ENT_QUOTES);
    include __DIR__ . '/claimsResults.php';
    echo ob_get_clean();
}
include __DIR__ . '/claimsTableTail.php';
} else {
    echo "<BR>No results found.";
}


?>