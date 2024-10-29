<?php
// config.php - Archivo de configuración común
define('DB_HOST', 'pepo');
define('DB_NAME', 'tpmarineli');
define('DB_USER', 'rittermarotta');
define('DB_PASS', 'elnehu123');

function conectarDB() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        error_log("Error de conexión: " . $e->getMessage());
        throw new Exception("Error al conectar con la base de datos");
    }
}

function obtenerPrecioTotal($conn, $carrito) {
    $total = 0;
    foreach ($carrito as $idPerfume => $cantidad) {
        $stmt = $conn->prepare("SELECT precio FROM perfumes WHERE idperfume = :id");
        $stmt->execute([':id' => $idPerfume]);
        $perfume = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($perfume) {
            $total += $perfume['precio'] * $cantidad;
        }
    }
    return $total;
}

function validarSesion() {
    session_start();
    if (!isset($_SESSION['cliente'])) {
        header('Location: login.php');
        exit;
    }
}
