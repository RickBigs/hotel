<?php
// camere.php
include 'connessione.php';
$camere = $conn->query("SELECT * FROM camere ORDER BY numero");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Elenco Camere</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>
<?php include 'header.php'; ?>
<h1>Elenco Camere</h1>
<table>
    <tr>
        <th>Numero</th>
        <th>Tipologia</th>
        <th>Piano</th>
        <th>Vista</th>
        <th>Capienza</th>
        <th>Note</th>
    </tr>
    <?php while($c = $camere->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($c['numero']); ?></td>
        <td><?php echo htmlspecialchars($c['tipologia']); ?></td>
        <td><?php echo htmlspecialchars($c['piano']); ?></td>
        <td><?php echo htmlspecialchars($c['vista']); ?></td>
        <td><?php echo htmlspecialchars($c['capienza']); ?></td>
        <!-- il campo note può contenere testo libero o può anche non contenere testo-->
        <td><?php echo htmlspecialchars($c['note'] ?? 'Nessuna nota'); ?></td>
    </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
