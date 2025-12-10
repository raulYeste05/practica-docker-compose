<?php
$file = "resultados.json";

if (!file_exists($file)) {
    file_put_contents($file, json_encode(["si"=>0, "no"=>0]));
}

$data = json_decode(file_get_contents($file), true);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data[$_POST["voto"]]++;
    file_put_contents($file, json_encode($data));
}


echo "<h1>¿Independizar Linares de Jaén?</h1>";
echo '<form method="POST">';
echo '<button name="voto" value="si">Sí</button>';
echo '<button name="voto" value="no">No</button>';
echo '</form>';

echo "<h2>Resultados</h2>";
echo "Sí: {$data['si']}<br>";
echo "No: {$data['no']}<br>";

echo "<p>Servidor: " . gethostname() . "</p>";
