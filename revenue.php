<?php
// revenue.php
include 'connessione.php';

// Calcola il revenue totale e dettaglio per prenotazione
$sql = "SELECT pc.daily_price, p.id_prenotazione, p.data_arrivo, p.data_partenza, c.nome, c.cognome, SUM(pc.daily_price * (DATEDIFF(p.data_partenza, p.data_arrivo))) AS totale
        FROM prenotazioni p
        JOIN clienti c ON p.id_cliente = c.id_cliente
        JOIN prenotazioni_camere pc ON p.id_prenotazione = pc.id_prenotazione 
        WHERE p.stato = 'confermata'
        GROUP BY p.id_prenotazione, p.data_arrivo, p.data_partenza, c.nome, c.cognome
        ORDER BY p.data_arrivo DESC";
$result = $conn->query($sql);

$totale_revenue = 0;
$prenotazioni = [];
while($row = $result->fetch_assoc()) {
    $prenotazioni[] = $row;
    $totale_revenue += $row['totale'];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Revenue Hotel</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>
<?php include 'header.php'; ?>
<h1>Revenue Hotel</h1>
<table>
    <tr>
        <th>ID Prenotazione</th>
        <th>Cliente</th>
        <th>Arrivo</th>
        <th>P. Partenza</th>
        <th>Prezzo a notte (€)</th>
        <th>Totale (€)</th>
    </tr>
    <?php foreach($prenotazioni as $p): ?>
    <tr>
        <td><?php echo $p['id_prenotazione']; ?></td>
        <td><?php echo htmlspecialchars($p['nome'] . ' ' . $p['cognome']); ?></td>
        <td><?php echo $p['data_arrivo']; ?></td>
        <td><?php echo $p['data_partenza']; ?></td>
        <td><?php echo $p['daily_price']; ?></td>
        <td><?php echo number_format($p['totale'], 2, ',', '.'); ?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="font-weight:bold;background:#e9ffe9;">
        <td colspan="5" style="text-align:right;">Totale Revenue:</td>
        <td><?php echo number_format($totale_revenue, 2, ',', '.'); ?> €</td>
    </tr>
</table>
</body>
</html>
