<?php

$host = "db";          
$dbname = "encuesta";  
$user = "root";        
$pass = "root";        

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
} catch (PDOException $e) {
    die("Error de conexión a MySQL: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $voto = $_POST["voto"];
    $stmt = $pdo->prepare("INSERT INTO votos (voto) VALUES (:voto)");
    $stmt->execute(['voto' => $voto]);
}

$stmt = $pdo->query("
    SELECT 
        SUM(voto = 'si') AS si, 
        SUM(voto = 'no') AS no 
    FROM votos
");

$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h1>¿Independizar Linares de Jaén?</h1>";
echo '<form method="POST">';
echo '<button name="voto" value="si">Sí</button>';
echo '<button name="voto" value="no">No</button>';
echo '</form>';

echo "<h2>Resultados</h2>";
echo "Sí: {$result['si']}<br>";
echo "No: {$result['no']}<br>";

echo "<p>Servidor: " . gethostname() . "</p>";
