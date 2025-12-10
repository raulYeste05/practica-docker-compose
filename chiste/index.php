<?php
$chistes = [
    "¿Qué le dice un Bit al otro?- Nos vemos en el bus",
    "Yo no fallo, es que tu código no entiende mi lógica.",
    "¿Cuál es la bebida favorita de un programador?-El CoffeeScript.",
    "Programador: alguien que resuelve un problema que no sabías que tenías.",
    "El camino al infierno está pavimentado con if anidados."
];

echo "<h1>Chiste informático:</h1>";
echo "<p>" . $chistes[array_rand($chistes)] . "</p>";
echo "<p>Servidor: " . gethostname() . "</p>";
