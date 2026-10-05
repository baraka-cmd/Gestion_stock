<?php
include 'entete.php';

$articles = getStock();
$historique = getHistoriqueStock(50);
$configuration = getConfiguration();
$stockParCategorie = getStockParCategorieDashboard();
$donneesStock = array(
    'labels' => array_column($stockParCategorie, 'categorie'),
    'values' => array_map('intval', array_column($stockParCategorie, 'unites')),
);

$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
?>
<div class="home-content stock-page">
    <div class="overview-boxes">
            <?php if ($peutModifier) { ?>
        <div class="box">
            <form action="../ajustementStock.php" method="POST">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($jetonApplication, ENT_QUOTES, 'UTF-8') ?>">
                <h2>Ajuster le stock</h2>

                <label for="id_article">Article</label>
                <select name="id_article" id="id_article" required>
                    <option value="">Choisir un article</option>
                    <?php foreach ($articles as $article) { ?>
                        <option value="<?= (int) $article['id'] ?>">
                            <?= htmlspecialchars($article['nom_article'], ENT_QUOTES, 'UTF-8') ?>
                            (stock : <?= (int) $article['quantite'] ?>)
                        </option>
                    <?php } ?>
                </select>

                <label for="type_mouvement">Type de mouvement</label>
                <select name="type_mouvement" id="type_mouvement" required>
                    <option value="entree">Entree de stock</option>
                    <option value="sortie">Sortie de stock</option>
                </select>

                <label for="quantite">Quantite</label>
                <input type="number" name="quantite" id="quantite" min="1" step="1" required>

                <label for="motif">Motif</label>
                <input type="text" name="motif" id="motif" maxlength="255" required
                       placeholder="Ex. correction inventaire">

                <button type="submit"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i> Enregistrer le mouvement</button>

                <?php if (!empty($message['text'])) { ?>
                    <div class="alert <?= htmlspecialchars($message['type'] ?? 'danger', ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($message['text'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php } ?>
            </form>
        </div>
            <?php } ?>

        <div class="box">
            <h2>Etat du stock</h2>
            <div class="table-scroll">
            <table class="mtable">
                <tr>
                    <th>Article</th>
                    <th>Categorie</th>
                    <th>Quantite</th>
                    <th>Etat</th>
                </tr>
                <?php foreach ($articles as $article) {
                    $quantite = (int) $article['quantite'];
                    $etat = $quantite <= 0 ? 'Rupture' : ($quantite <= (int) $configuration['seuil_stock_faible'] ? 'Stock faible' : 'Normal');
                ?>
                    <tr>
                        <td><?= htmlspecialchars($article['nom_article'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($article['categorie'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $quantite ?></td>
                        <td><span class="stock-state <?= $quantite <= 0 ? 'stock-state-empty' : ($quantite <= (int) $configuration['seuil_stock_faible'] ? 'stock-state-low' : 'stock-state-ok') ?>"><i class="fa-solid <?= $quantite <= 0 ? 'fa-circle-xmark' : ($quantite <= (int) $configuration['seuil_stock_faible'] ? 'fa-triangle-exclamation' : 'fa-circle-check') ?>" aria-hidden="true"></i> <?= $etat ?></span></td>
                    </tr>
                <?php } ?>
            </table>
            </div>
        </div>

        <div class="box stock-chart-box">
            <h2>Stock par categorie</h2>
            <p>Unites disponibles pour chaque categorie.</p>
            <?php if ($stockParCategorie) { ?>
                <div class="chart-frame chart-frame-stock">
                    <canvas id="stock-categories-chart" aria-label="Unites de stock par categorie" role="img"></canvas>
                </div>
            <?php } else { ?>
                <p class="empty-state">Aucun article a representer.</p>
            <?php } ?>
        </div>
    </div>

    <div class="overview-boxes">
        <div class="box">
            <h2>Derniers mouvements</h2>
            <div class="table-scroll">
            <table class="mtable">
                <tr>
                    <th>Date</th>
                    <th>Article</th>
                    <th>Type</th>
                    <th>Quantite</th>
                    <th>Avant</th>
                    <th>Apres</th>
                    <th>Motif</th>
                </tr>
                <?php foreach ($historique as $mouvement) { ?>
                    <tr>
                        <td><?= htmlspecialchars($mouvement['date_mouvement'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($mouvement['nom_article'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $mouvement['type_mouvement'] === 'entree' ? 'Entree' : 'Sortie' ?></td>
                        <td><?= (int) $mouvement['quantite'] ?></td>
                        <td><?= (int) $mouvement['stock_avant'] ?></td>
                        <td><?= (int) $mouvement['stock_apres'] ?></td>
                        <td><?= htmlspecialchars($mouvement['motif'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php } ?>
            </table>
            </div>
        </div>
    </div>
</div>
<script type="application/json" id="stock-chart-data"><?= json_encode($donneesStock, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
<script src="../../public/vendor/chart/chart.umd.min.js"></script>
<script>
    const stockChartData = JSON.parse(document.querySelector('#stock-chart-data').textContent);
    const stockCanvas = document.querySelector('#stock-categories-chart');
    if (stockCanvas && window.Chart) {
        new Chart(stockCanvas, {
            type: 'bar',
            data: {
                labels: stockChartData.labels,
                datasets: [{
                    label: 'Unites disponibles',
                    data: stockChartData.values,
                    backgroundColor: ['#087e78', '#e49b37', '#4276a6', '#c76f5c', '#76a99d'],
                    borderRadius: 5,
                    maxBarThickness: 44
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(100, 120, 130, 0.12)' }, ticks: { precision: 0 } },
                    y: { grid: { display: false } }
                }
            }
        });
    }
</script>
<?php include 'pied.php'; ?>