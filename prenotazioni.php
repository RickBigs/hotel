<?php
// prenotazioni.php
include 'connessione.php';
$data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

$sql = "SELECT p.*, c.nome, c.cognome FROM prenotazioni p JOIN clienti c ON p.id_cliente = c.id_cliente WHERE p.data_arrivo = '$data' ORDER BY p.data_arrivo";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Prenotazioni</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>
<?php include 'header.php'; ?>
<h1>Prenotazioni per la data: <?php echo htmlspecialchars($data); ?></h1>
<form method="get">
    <input type="date" name="data" value="<?php echo htmlspecialchars($data); ?>">
    <button type="submit">Cerca</button>
</form>
<table>
    <tr><th>Cliente</th><th>Arrivo</th><th>Partenza</th><th>Stato</th><th>Ospiti</th><th>Camere</th><th>Note</th></tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['nome'] . ' ' . $row['cognome']; ?></td>
        <td><?php echo $row['data_arrivo']; ?></td>
        <td><?php echo $row['data_partenza']; ?></td>
        <td><?php echo $row['stato']; ?></td>
        <td><?php echo $row['n_ospiti']; ?></td>
        <td>
        <?php
            $idp = $row['id_prenotazione'];
            $q2 = $conn->query("SELECT numero FROM prenotazioni_camere pc JOIN camere ca ON pc.id_camera = ca.id_camera WHERE pc.id_prenotazione = $idp");
            $camere = [];
            while($r2 = $q2->fetch_assoc()) $camere[] = $r2['numero'];
            echo implode(', ', $camere);
        ?>
        </td>
        <td><?php echo $row['note']; ?></td>
    </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
