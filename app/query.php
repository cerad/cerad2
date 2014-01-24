<?php
error_reporting(E_ALL);

$conn = new PDO("mysql:host=localhost; dbname=DBNAME", 'USER', 'PASSWORD');
$conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);

$sql = <<<EOT
SELECT * FROM MemberRidePix WHERE Status=:Status LIMIT 1
;
EOT;

$stmt = $conn->prepare($sql);
$stmt->execute(array('Status' => 2));
$rows = $stmt->fetchAll();
print_r($rows);

$image = $rows[0];
print_r($image);

?>
