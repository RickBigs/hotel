<?php
// tableau.php
include 'connessione.php';
$oggi = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$start = date('Y-m-d', strtotime($oggi.' -'.(date('N', strtotime($oggi))-1).' days'));
$giorni = [];
for($i=0;$i<7;$i++) $giorni[] = date('Y-m-d', strtotime("$start +$i days"));
$camere = $conn->query("SELECT * FROM camere ORDER BY numero");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Tableau Camere</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>
<?php include 'header.php'; ?>
<h1>Tableau settimanale camere</h1>
<form method="get">
    <input type="date" name="data" value="<?php echo htmlspecialchars($oggi); ?>">
    <button type="submit">Vai alla settimana</button>
</form>
<table class="tableau">
    <tr>
        <th>Camera</th>
        <?php foreach($giorni as $g): ?>
            <th><?php echo date('D d/m', strtotime($g)); ?></th>
        <?php endforeach; ?>
    </tr>
    <?php while($c = $camere->fetch_assoc()): ?>
    <tr>
        <td><?php echo $c['numero']; ?></td>
        <?php foreach($giorni as $g): ?>
            <?php
            $idc = $c['id_camera'];
            // Metà sinistra: partenza (notte precedente termina qui)
            $q_left = $conn->query("SELECT p.id_prenotazione, cl.nome, cl.cognome FROM prenotazioni_camere pc JOIN prenotazioni p ON pc.id_prenotazione = p.id_prenotazione JOIN clienti cl ON p.id_cliente = cl.id_cliente WHERE pc.id_camera = $idc AND p.data_partenza = '$g' AND p.stato = 'confermata'");
            $left = $q_left->fetch_assoc();
            // Metà destra: arrivo (notte inizia qui)
            $q_right = $conn->query("SELECT p.id_prenotazione, cl.nome, cl.cognome FROM prenotazioni_camere pc JOIN prenotazioni p ON pc.id_prenotazione = p.id_prenotazione JOIN clienti cl ON p.id_cliente = cl.id_cliente WHERE pc.id_camera = $idc AND p.data_arrivo = '$g' AND p.stato = 'confermata'");
            $right = $q_right->fetch_assoc();
            // Casella di fermata: la camera è occupata se esiste una prenotazione che include questa data come notte di permanenza
            $q_stay = $conn->query("SELECT p.id_prenotazione, cl.nome, cl.cognome FROM prenotazioni_camere pc JOIN prenotazioni p ON pc.id_prenotazione = p.id_prenotazione JOIN clienti cl ON p.id_cliente = cl.id_cliente WHERE pc.id_camera = $idc AND p.data_arrivo < '$g' AND p.data_partenza > '$g' AND p.stato = 'confermata'");
            $stay = $q_stay->fetch_assoc();
            ?>
            <td class="cella-split">
                <div class="half left <?php echo ($left || $stay) ? 'occupata' : 'libera'; ?>">
                    <?php if($left) echo $left['id_prenotazione'] . '<br>' . htmlspecialchars($left['nome']) . ' ' . htmlspecialchars($left['cognome']);
                    elseif($stay) echo $stay['id_prenotazione'] . '<br>' . htmlspecialchars($stay['nome']) . ' ' . htmlspecialchars($stay['cognome']); ?>
                </div>
                <div class="half right <?php echo ($right || $stay) ? 'occupata' : 'libera'; ?>">
                    <?php if($right) echo $right['id_prenotazione'] . '<br>' . htmlspecialchars($right['nome']) . ' ' . htmlspecialchars($right['cognome']);
                    elseif($stay) echo $stay['id_prenotazione'] . '<br>' . htmlspecialchars($stay['nome']) . ' ' . htmlspecialchars($stay['cognome']); ?>
                </div>
            </td>
        <?php endforeach; ?>
    </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
