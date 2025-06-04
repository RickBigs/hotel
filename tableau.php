<?php
// tableau.php
include 'connessione.php';
$oggi = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
// Calcola la data di inizio: 2 giorni prima della data selezionata
$start = date('Y-m-d', strtotime($oggi.' -2 days'));
$giorni = [];
// Crea un array di 10 giorni: -2, -1, oggi, +1, +2, +3, +4, +5, +6, +7
for($i=0;$i<10;$i++) $giorni[] = date('Y-m-d', strtotime("$start +$i days"));
$camere = $conn->query("SELECT * FROM camere ORDER BY numero");

// Funzione helper per generare il contenuto della prenotazione
function renderPrenotazioneInfo($pren) {
    if(!$pren) return '';
    return '<div class="pren-info-small">
                <span class="pren-id-small">' . $pren['id_prenotazione'] . '</span><br>
                <span class="pren-nome">' . htmlspecialchars($pren['nome'] . ' ' . $pren['cognome']) . '</span>
            </div>';
}
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
<h1>Tableau camere (10 giorni: -2/+7)</h1>
<div class="navigation-controls">
    <form method="get" style="display: inline-block;">
        <input type="hidden" name="data" value="<?php echo date('Y-m-d', strtotime($oggi.' -1 day')); ?>">
        <button type="submit">← Giorno precedente</button>
    </form>
    
    <form method="get" style="display: inline-block; margin: 0 20px;">
        <input type="date" name="data" value="<?php echo htmlspecialchars($oggi); ?>">
        <button type="submit">Vai alla data</button>
    </form>
    
    <form method="get" style="display: inline-block;">
        <input type="hidden" name="data" value="<?php echo date('Y-m-d', strtotime($oggi.' +1 day')); ?>">
        <button type="submit">Giorno successivo →</button>
    </form>
    
    <form method="get" style="display: inline-block; margin-left: 20px;">
        <input type="hidden" name="data" value="<?php echo date('Y-m-d'); ?>">
        <button type="submit" style="background: #2d6a4f;">Oggi</button>
    </form>
</div>
<p style="text-align: center; font-weight: bold; color: #2d6a4f;">
    Data centrale: <?php echo date('d/m/Y', strtotime($oggi)); ?> 
    (dal <?php echo date('d/m/Y', strtotime($start)); ?> al <?php echo date('d/m/Y', strtotime(end($giorni))); ?>)
</p>
<table class="tableau">    <tr>
        <th style="width: 80px;">Camera</th>
        <?php foreach($giorni as $index => $g): 
            $is_oggi = ($g == date('Y-m-d'));
            $is_centrale = ($g == $oggi);
            $day_class = '';
            if($is_oggi) $day_class .= ' oggi';
            if($is_centrale) $day_class .= ' centrale';
        ?>
            <th class="<?php echo $day_class; ?>" style="<?php echo $is_oggi ? 'background: #d63031; color: white;' : ($is_centrale ? 'background: #2d6a4f; color: white;' : ''); ?>">
                <?php echo date('D d/m', strtotime($g)); ?>
                <?php if($is_oggi): ?><br><small>(OGGI)</small><?php endif; ?>
                <?php if($is_centrale && !$is_oggi): ?><?php endif; ?>
            </th>
        <?php endforeach; ?>
    </tr>
    <?php while($c = $camere->fetch_assoc()): ?>
    <tr>
        <td><?php echo $c['numero']; ?></td>
        <?php 
        $idc = $c['id_camera'];
        
        // Ottieni tutte le prenotazioni che interessano questo periodo per questa camera
        $prenotazioni_settimana = [];
        $q_pren = $conn->query("SELECT p.id_prenotazione, p.data_arrivo, p.data_partenza, cl.nome, cl.cognome 
                               FROM prenotazioni_camere pc 
                               JOIN prenotazioni p ON pc.id_prenotazione = p.id_prenotazione 
                               JOIN clienti cl ON p.id_cliente = cl.id_cliente 
                               WHERE pc.id_camera = $idc 
                               AND p.stato = 'confermata' 
                               AND p.data_arrivo <= '" . end($giorni) . "' 
                               AND p.data_partenza >= '" . reset($giorni) . "'");
          while($pren = $q_pren->fetch_assoc()) {
            $prenotazioni_settimana[] = $pren;
        }
        
        foreach($giorni as $g): 
            // Trova prenotazioni per questo giorno
            $stato_giorno = [
                'partenza' => null,
                'arrivo' => null,
                'soggiorno' => null
            ];
            
            foreach($prenotazioni_settimana as $pren) {
                if($pren['data_partenza'] == $g) {
                    $stato_giorno['partenza'] = $pren;
                }
                if($pren['data_arrivo'] == $g) {
                    $stato_giorno['arrivo'] = $pren;
                }
                if($pren['data_arrivo'] < $g && $pren['data_partenza'] > $g) {
                    $stato_giorno['soggiorno'] = $pren;
                }
            }
            
            // Determina la prenotazione attiva e il tipo di blocco
            $prenotazione_attiva = $stato_giorno['arrivo'] ?: $stato_giorno['soggiorno'] ?: $stato_giorno['partenza'];
            
            $blocco_class = '';
            if($prenotazione_attiva) {
                $blocco_class = 'occupata';
                if($stato_giorno['arrivo'] && !$stato_giorno['partenza']) {
                    $blocco_class .= ' blocco-inizio';
                } elseif($stato_giorno['partenza'] && !$stato_giorno['arrivo']) {
                    $blocco_class .= ' blocco-fine';
                } elseif($stato_giorno['arrivo'] && $stato_giorno['partenza']) {
                    $blocco_class .= ' blocco-singolo';
                } elseif($stato_giorno['soggiorno']) {
                    $blocco_class .= ' blocco-mezzo';
                }
            }
            
            // Determina quale prenotazione mostrare in ogni metà
            $pren_left = $stato_giorno['partenza'] ?: $stato_giorno['soggiorno'];
            $pren_right = $stato_giorno['arrivo'] ?: $stato_giorno['soggiorno'];
            ?>
            <td class="cella-split <?php echo $blocco_class; ?>" 
                <?php if($prenotazione_attiva): ?>
                data-prenotazione="<?php echo $prenotazione_attiva['id_prenotazione']; ?>"
                <?php endif; ?>>
                <div class="half left <?php echo $pren_left ? 'occupata' : 'libera'; ?>">
                    <?php echo renderPrenotazioneInfo($pren_left); ?>
                </div>
                <div class="half right <?php echo $pren_right ? 'occupata' : 'libera'; ?>">
                    <?php echo renderPrenotazioneInfo($pren_right); ?>
                </div>
            </td>
        <?php endforeach;?>
    </tr>
    <?php endwhile; ?>
</table>
</body>
</html>