<?php
include 'entete.php';

$configuration = getConfiguration();
$indicateurs = getDashboardIndicateurs($configuration['seuil_stock_faible']);
$ventesMensuelles = getVentesMensuellesDashboard(6);
$stockCategories = getStockParCategorieDashboard();
$articlesEnAlerte = getArticlesEnAlerteDashboard($configuration['seuil_stock_faible']);
$meilleursArticles = getMeilleursArticlesDashboard();
$operationsRecentes = getToutesCommandes(array(), 6, 0);
$devise = htmlspecialchars($configuration['devise'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$nombreAlertes = (int) $indicateurs['articles_en_alerte'];
$donneesGraphiques = array(
    'labels' => array_column($ventesMensuelles, 'label'),
    'revenus' => array_column($ventesMensuelles, 'revenu'),
    'categories' => array_column($stockCategories, 'categorie'),
    'stock' => array_map('intval', array_column($stockCategories, 'unites')),
);
?>
<main class="home-content dashboard-page">
    <section class="dashboard-intro">
        <div>
            <p class="eyebrow">PILOTAGE / <?= htmlspecialchars(date('d.m.Y'), ENT_QUOTES, 'UTF-8') ?></p>
            <h1>Vue d'ensemble</h1>
            <p class="dashboard-subtitle">Activite commerciale et disponibilite des stocks.</p>
        </div>
        <a class="button button-primary" href="toutesCommandes.php">
            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
            <span>Ouvrir les operations</span>
        </a>
    </section>

    <section class="kpi-grid" aria-label="Indicateurs principaux">
        <article class="kpi-card kpi-revenue">
            <div class="kpi-topline"><span>Ventes du jour</span><i class="fa-solid fa-cash-register" aria-hidden="true"></i></div>
            <strong><?= number_format((float) $indicateurs['revenu_jour'], 0, ',', ' ') ?> <small><?= $devise ?></small></strong>
            <span class="kpi-note">Montant des ventes actives aujourd hui</span>
        </article>
        <article class="kpi-card kpi-month">
            <div class="kpi-topline"><span>Chiffre du mois</span><i class="fa-solid fa-chart-line" aria-hidden="true"></i></div>
            <strong><?= number_format((float) $indicateurs['revenu_mois'], 0, ',', ' ') ?> <small><?= $devise ?></small></strong>
            <span class="kpi-note">Depuis le debut du mois</span>
        </article>
        <article class="kpi-card kpi-orders">
            <div class="kpi-topline"><span>Commandes actives</span><i class="fa-solid fa-truck-fast" aria-hidden="true"></i></div>
            <strong><?= number_format((int) $indicateurs['commandes_actives'], 0, ',', ' ') ?></strong>
            <span class="kpi-note">Commandes fournisseur en cours</span>
        </article>
        <article class="kpi-card kpi-stock">
            <div class="kpi-topline"><span>Unites disponibles</span><i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i></div>
            <strong><?= number_format((int) $indicateurs['unites_stock'], 0, ',', ' ') ?></strong>
            <span class="kpi-note"><?= (int) $indicateurs['nombre_articles'] ?> references au catalogue</span>
        </article>
        <article class="kpi-card <?= $nombreAlertes > 0 ? 'kpi-alert' : 'kpi-healthy' ?>">
            <div class="kpi-topline"><span>Seuil a surveiller</span><i class="fa-solid <?= $nombreAlertes > 0 ? 'fa-triangle-exclamation' : 'fa-circle-check' ?>" aria-hidden="true"></i></div>
            <strong><?= number_format($nombreAlertes, 0, ',', ' ') ?></strong>
            <span class="kpi-note">Articles a <?= (int) $configuration['seuil_stock_faible'] ?> unite(s) ou moins</span>
        </article>
    </section>

    <section class="dashboard-grid dashboard-charts">
        <article class="dashboard-panel chart-panel chart-panel-wide">
            <header class="panel-heading">
                <div><h2>Evolution des ventes</h2><p>Chiffre d affaires mensuel, ventes actives</p></div>
                <span class="panel-icon"><i class="fa-solid fa-chart-column" aria-hidden="true"></i></span>
            </header>
            <div class="chart-frame"><canvas id="revenus-chart" aria-label="Evolution mensuelle du chiffre d affaires" role="img"></canvas></div>
        </article>
        <article class="dashboard-panel chart-panel">
            <header class="panel-heading">
                <div><h2>Stock par categorie</h2><p>Unites actuellement disponibles</p></div>
                <span class="panel-icon panel-icon-warm"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i></span>
            </header>
            <div class="chart-frame chart-frame-doughnut"><canvas id="stock-chart" aria-label="Repartition des unites par categorie" role="img"></canvas></div>
            <?php if (!$stockCategories) { ?><p class="empty-state">Aucune donnee de stock disponible.</p><?php } ?>
        </article>
    </section>

    <section class="dashboard-grid dashboard-lists">
        <article class="dashboard-panel table-panel">
            <header class="panel-heading">
                <div><h2>Activite recente</h2><p>Dernieres ventes et commandes fournisseur</p></div>
                <a class="text-link" href="toutesCommandes.php">Tout voir <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </header>
            <div class="table-scroll">
                <table class="dashboard-table">
                    <thead><tr><th>Operation</th><th>Article</th><th>Tiers</th><th>Quantite</th><th>Montant</th><th>Etat</th></tr></thead>
                    <tbody>
                    <?php if (!$operationsRecentes) { ?>
                        <tr><td colspan="6" class="empty-cell">Aucune operation enregistree.</td></tr>
                    <?php } ?>
                    <?php foreach ($operationsRecentes as $operation) {
                        $estVente = $operation['type_commande'] === 'vente';
                        $reference = ($estVente ? 'V-' : 'C-') . (int) $operation['id'];
                    ?>
                        <tr>
                            <td><span class="operation-type <?= $estVente ? 'operation-sale' : 'operation-order' ?>"><i class="fa-solid <?= $estVente ? 'fa-arrow-up' : 'fa-arrow-down' ?>" aria-hidden="true"></i><?= $estVente ? 'Vente' : 'Commande' ?></span><small><?= htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars(date('d/m H:i', strtotime($operation['date_operation'])), ENT_QUOTES, 'UTF-8') ?></small></td>
                            <td><?= htmlspecialchars($operation['nom_article'] ?: 'Article sans nom', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($operation['tiers'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                            <td><?= (int) $operation['quantite'] ?></td>
                            <td><?= number_format((float) $operation['prix'], 0, ',', ' ') ?> <?= $devise ?></td>
                            <td><span class="status-pill <?= (string) $operation['etat'] === '1' ? 'status-active' : 'status-cancelled' ?>"><?= (string) $operation['etat'] === '1' ? 'Active' : 'Annulee' ?></span></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="dashboard-panel table-panel alert-panel">
            <header class="panel-heading">
                <div><h2>Stock a surveiller</h2><p>Seuil configure : <?= (int) $configuration['seuil_stock_faible'] ?> unites</p></div>
                <a class="text-link" href="stock.php">Voir le stock <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </header>
            <div class="alert-list">
                <?php if (!$articlesEnAlerte) { ?>
                    <div class="empty-state"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Aucun article sous le seuil.</span></div>
                <?php } ?>
                <?php foreach ($articlesEnAlerte as $article) { ?>
                    <div class="alert-row">
                        <span class="alert-product-icon"><i class="fa-solid fa-box-open" aria-hidden="true"></i></span>
                        <span class="alert-product-name"><strong><?= htmlspecialchars($article['nom_article'] ?: 'Article sans nom', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong><small><?= htmlspecialchars($article['categorie'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small></span>
                        <span class="alert-quantity <?= (int) $article['quantite'] === 0 ? 'quantity-zero' : '' ?>"><?= (int) $article['quantite'] ?><small>unites</small></span>
                    </div>
                <?php } ?>
            </div>
        </article>
    </section>

    <section class="dashboard-panel top-products-panel">
        <header class="panel-heading">
            <div><h2>Articles les plus vendus</h2><p>Classement par quantite vendue, hors ventes annulees</p></div>
            <a class="text-link" href="article.php">Catalogue <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </header>
        <div class="top-product-grid">
            <?php if (!$meilleursArticles) { ?><p class="empty-state">Les articles vendus apparaitront ici.</p><?php } ?>
            <?php foreach ($meilleursArticles as $index => $article) { ?>
                <div class="top-product-row"><span class="rank-number"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><span class="top-product-name"><?= htmlspecialchars($article['nom_article'] ?: 'Article sans nom', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span><strong><?= (int) $article['unites_vendues'] ?> <small>vendues</small></strong></div>
            <?php } ?>
        </div>
    </section>
</main>
<script type="application/json" id="dashboard-data"><?= json_encode($donneesGraphiques, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
<script src="../../public/vendor/chart/chart.umd.min.js"></script>
<script>
    const dashboardData = JSON.parse(document.querySelector('#dashboard-data').textContent);
    const currency = <?= json_encode($configuration['devise'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const numberFormat = new Intl.NumberFormat('fr-FR');
    const revenueCanvas = document.querySelector('#revenus-chart');
    const stockCanvas = document.querySelector('#stock-chart');

    if (revenueCanvas && window.Chart) {
        new Chart(revenueCanvas, {
            type: 'line',
            data: {
                labels: dashboardData.labels,
                datasets: [{
                    label: 'Chiffre d affaires',
                    data: dashboardData.revenus,
                    borderColor: '#087e78',
                    backgroundColor: 'rgba(8, 126, 120, 0.12)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.34,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#087e78'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: context => `${numberFormat.format(context.parsed.y)} ${currency}` } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#71808c', maxRotation: 0 } },
                    y: { beginAtZero: true, grid: { color: 'rgba(100, 120, 130, 0.12)' }, ticks: { color: '#71808c', callback: value => numberFormat.format(value) } }
                }
            }
        });
    }

    if (stockCanvas && window.Chart && dashboardData.stock.length) {
        new Chart(stockCanvas, {
            type: 'doughnut',
            data: {
                labels: dashboardData.categories,
                datasets: [{
                    data: dashboardData.stock,
                    backgroundColor: ['#087e78', '#e49b37', '#4276a6', '#c76f5c', '#76a99d'],
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 18, color: '#53616b' } },
                    tooltip: { callbacks: { label: context => `${context.label}: ${numberFormat.format(context.parsed)} unites` } }
                }
            }
        });
    }
</script>
<?php include 'pied.php'; ?>