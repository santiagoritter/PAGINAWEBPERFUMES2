<?php
session_start();

// Configuración de la base de datos
$servername = "pepo";
$username = "rittermarotta";
$password = "elnehu123";
$dbname = "tpmarineli";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Consulta base para los perfumes
    $query = "SELECT * FROM perfumes";
    
    // Agregar filtros si existen
    $params = [];
    
    if (isset($_GET['notas'])) {
        $query .= " WHERE notas LIKE :notas";
        $params[':notas'] = '%' . $_GET['notas'] . '%';
    }
    
    if (isset($_GET['precio_min']) && isset($_GET['precio_max'])) {
        $query .= isset($_GET['notas']) ? " AND" : " WHERE";
        $query .= " precio BETWEEN :precio_min AND :precio_max";
        $params[':precio_min'] = $_GET['precio_min'];
        $params[':precio_max'] = $_GET['precio_max'];
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $perfumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    die();
}

// Función para agregar al carrito
function agregarAlCarrito($idPerfume) {
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
    
    if (isset($_SESSION['carrito'][$idPerfume])) {
        $_SESSION['carrito'][$idPerfume]++;
    } else {
        $_SESSION['carrito'][$idPerfume] = 1;
    }
}

// Procesar formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];
    
    $stmt = $conn->prepare("SELECT * FROM cliente WHERE usuario = :usuario AND contraseña = :contraseña");
    $stmt->execute([':usuario' => $usuario, ':contraseña' => $contraseña]);
    $cliente = $stmt->fetch();
    
    if ($cliente) {
        $_SESSION['cliente'] = $cliente;
        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ritta et Compagnie - Perfumes</title>
    <meta charset="UTF-8">
</head>
<body>
    <div id="encabezado">
        <div class="logo">
            <a href="inicio.html"><img src="logo.png" alt="Logo"></a>
        </div>
        
        <div class="barra_de_busqueda">
            <div class="lupa">
                <img src="lupa.png" alt="Buscar">
            </div>
        </div>
        
        <?php if(isset($_SESSION['cliente'])): ?>
            <div class="usuario-info">
                Bienvenido, <?php echo htmlspecialchars($_SESSION['cliente']['usuario']); ?>
                <a href="logout.php">Cerrar sesión</a>
            </div>
        <?php else: ?>
            <div class="login-form">
                <form method="POST">
                    <input type="text" name="usuario" placeholder="Usuario">
                    <input type="password" name="contraseña" placeholder="Contraseña">
                    <input type="submit" name="login" value="Iniciar sesión">
                </form>
            </div>
        <?php endif; ?>
        
        <div class="chango">
            <a href="carrito.php">
                <img src="carrito-de-compras.png" alt="Carrito">
                <?php if(isset($_SESSION['carrito'])): ?>
                    <span class="carrito-count"><?php echo array_sum($_SESSION['carrito']); ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <main role="main">
        <div id="perfumes" class="section">
            <?php foreach($perfumes as $perfume): ?>
                <div class="boton">
                    <a href="producto.php?id=<?php echo $perfume['idperfume']; ?>" class="link">
                        <img src="imagenes/<?php echo $perfume['idperfume']; ?>.png" 
                            alt="<?php echo htmlspecialchars($perfume['nombreperfumes']); ?>" 
                            class="perfume">
                        
                        <div class="perfume-info">
                            <div class="nombre">
                                <?php echo htmlspecialchars($perfume['nombreperfumes']); ?>
                            </div>
                            <div class="descripcion">
                                <?php echo htmlspecialchars($perfume['descripcion']); ?>
                            </div>
                            <div class="notas">
                                <?php echo htmlspecialchars($perfume['notas']); ?>
                            </div>
                            <div class="precio">
                                $<?php echo number_format($perfume['precio'], 2); ?>
                            </div>
                        </div>
                        
                        <?php if(isset($_SESSION['cliente'])): ?>
                            <button onclick="agregarAlCarrito(<?php echo $perfume['idperfume']; ?>)">
                                Agregar al carrito
                            </button>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        function agregarAlCarrito(idPerfume) {
            fetch('agregar_carrito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'idPerfume=' + idPerfume
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Producto agregado al carrito');
                    // Actualizar contador del carrito
                    document.querySelector('.carrito-count').textContent = data.cantidad;
                }
            });
        }
    </script>
</body>
</html>