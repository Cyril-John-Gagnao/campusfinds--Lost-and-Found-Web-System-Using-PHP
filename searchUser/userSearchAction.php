<?php  

include_once __DIR__ . '/../dbconn.php';
$slaveConn = getSlaveConnection();

$keyword = $_GET['keyword'];

$stmt = $slaveConn->prepare("SELECT * from user WHERE email LIKE :email OR name LIKE :name OR course LIKE :course OR year LIKE :year OR department LIKE :department ORDER BY year ASC, department ASC, name ASC" );
$stmt->execute([':email' => "%$keyword%",':name' => "%$keyword%", ':course' => "%$keyword%", ':year' => "%$keyword%", ':department' => "%$keyword%"]);
$results = $stmt->fetchAll();
if($results) {
    include __DIR__ . '/userTableHead.php';
    ob_start();
    foreach($results as $data) {
    $email = htmlspecialchars($data['email'], ENT_QUOTES);
    $name = htmlspecialchars($data['name'], ENT_QUOTES);
    $imagePath = htmlspecialchars($data['imagePath'], ENT_QUOTES);
    $course = htmlspecialchars($data['course'], ENT_QUOTES);
    $year = htmlspecialchars($data['year'], ENT_QUOTES);
    $role = htmlspecialchars($data['role'], ENT_QUOTES);
    $department = htmlspecialchars($data['department'], ENT_QUOTES);
    $user_ID = htmlspecialchars($data['user_ID'], ENT_QUOTES);
    include __DIR__ . '/userResults.php';
    echo ob_get_clean();
}
include __DIR__ . '/userTableTail.php';
} else {
    echo "<BR>No results found.";
}


?>