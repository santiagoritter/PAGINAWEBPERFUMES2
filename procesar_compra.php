<?php
session_start();

if (!isset($_SESSION['cliente']) || !isset($_SESSION['carrito'])) {
    header('Location: index.php');
    exit;
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Iniciar transacción
    $conn->beginTransaction();
    
    // Crear nueva compra
    $stmt = $conn->prepare("INSERT INTO compras (dnicliente, fecha, cantcomprado, preciototal) VALUES (:dni, NOW(), :cant, :total)");
    $cantTotal = array_sum($_SESSION['carrito']);
    $precioTotal = 0; // Calcular según los precios de los productos
    
    $stmt->execute([
        ':dni' => $_SESSION['cliente']['dni'],
        ':cant' => $cantTotal,
        ':total' => $precioTotal
    ]);
    
    $idCompra = $conn->lastInsertId();
    
    // Registrar los perfumes comprados
    foreach ($_SESSION['carrito'] as $idPerfume => $cantidad) {
        $stmt = $conn->prepare("INSERT INTO compraperfumes (idperfumeperfumes, idcompracompras) VALUES (:idPerfume, :idCompra)");
        $stmt->execute([
            ':idPerfume' => $idPerfume,
            ':idCompra' => $idCompra
        ]);
    }
    
    $conn->commit();
    unset($_SESSION['carrito']); // Vaciar carrito
    
    header('Location: compra_exitosa.php');
    
} catch(PDOException $e) {
    $conn->rollBack();
    echo "Error en la compra: " . $e->getMessage();
}