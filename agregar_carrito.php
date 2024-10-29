<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idPerfume'])) {
    $idPerfume = $_POST['idPerfume'];
    
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
    
    if (isset($_SESSION['carrito'][$idPerfume])) {
        $_SESSION['carrito'][$idPerfume]++;
    } else {
        $_SESSION['carrito'][$idPerfume] = 1;
    }
    
    echo json_encode([
        'success' => true,
        'cantidad' => array_sum($_SESSION['carrito'])
    ]);
} else {
    echo json_encode(['success' => false]);
}