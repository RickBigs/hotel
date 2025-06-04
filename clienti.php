<?php
// clienti.php
include 'connessione.php';
$clienti = $conn->query("SELECT * FROM clienti ORDER BY cognome, nome");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Elenco Clienti</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>
<?php include 'header.php'; ?>
<h1>Elenco Clienti</h1>
<table>
    <tr>
        <th>Nome</th>
        <th>Cognome</th>
        <th>Cellulare</th>
        <th>Email</th>
        <th>Note</th>
    </tr>
    <?php while($c = $clienti->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($c['nome']); ?></td>
        <td><?php echo htmlspecialchars($c['cognome']); ?></td>
        <td><?php echo htmlspecialchars($c['cellulare']); ?></td>
        <td><?php echo htmlspecialchars($c['email']); ?></td>
        <td><?php echo htmlspecialchars($c['note']); ?></td>
    </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
