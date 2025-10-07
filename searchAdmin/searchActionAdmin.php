<?php  
include_once __DIR__ . '/../dbconn.php'; 

$slaveConn = getSlaveConnection();

$keyword = $_GET['keyword'];

$stmt = $slaveConn->prepare("SELECT * from admin WHERE email LIKE :email OR name LIKE :name OR department LIKE :department ORDER BY department ASC, name ASC" );
$stmt->execute([':email'=>"%$keyword%" , ':name' => "%$keyword%", ':department' => "%$keyword%"]);
$results = $stmt->fetchAll();
if($results) {
    include __DIR__ . '/tableHeadAdmin.php';
    ob_start();
    foreach($results as $data) {
    $email = htmlspecialchars($data['email'], ENT_QUOTES);
    $name = htmlspecialchars($data['name'], ENT_QUOTES);
    $imagePath = htmlspecialchars($data['imagePath'], ENT_QUOTES);
    $department = htmlspecialchars($data['department'], ENT_QUOTES);
    $admin_ID = htmlspecialchars($data['admin_ID'], ENT_QUOTES);
    include __DIR__ . '/resultsAdmin.php';
    echo ob_get_clean();
}
include __DIR__ . '/tableTailAdmin.php';
} else {
    echo "<BR>No results found.";
}


?>