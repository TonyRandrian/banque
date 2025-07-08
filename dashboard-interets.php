<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord des intérêts - Établissement Financier</title>
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }

        .filter-section {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 30px;
        }

        .filter-bar {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        label {
            font-weight: bold;
            color: #34495e;
        }

        input, select, button {
            padding: 8px 12px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-size: 14px;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #ecf0f1;
        }

        .tab {
            padding: 12px 20px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
            color: #7f8c8d;
            border-bottom: 3px solid transparent;
        }

        .tab.active {
            color: #2c3e50;
            border-bottom-color: #3498db;
            font-weight: bold;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        th {
            background-color: #34495e;
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .amount {
            text-align: right;
            font-weight: bold;
            color: #27ae60;
        }

        .total-row {
            background-color: #3498db !important;
            color: white;
            font-weight: bold;
        }

        .total-row td {
            border-top: 2px solid #2980b9;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #e74c3c;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .summary-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>📊 Intérêts gagnés par mois</h1>
    <div class="filter-section">
        <div class="filter-bar">
            <label for="date_debut">📅 Période début :</label>
            <input type="month" id="date_debut">
            <label for="date_fin">📅 Période fin :</label>
            <input type="month" id="date_fin">
            <button onclick="chargerDonnees()">🔍 Filtrer</button>
            <button onclick="exporterCSV()">📄 Exporter CSV</button>
        </div>
    </div>

    <div style="width:100%;max-width:1300px;margin:30px auto 0 auto;">
        <canvas id="chart-interets" height="420"></canvas>
    </div>

    <table id="table-interets">
        <thead>
        <tr>
            <th>📅 Mois/Année</th>
            <th>💰 Intérêts gagnés (€)</th>
        </tr>
        </thead>
        <tbody id="tbody-interets">
        <tr>
            <td colspan="2" class="loading">Sélectionnez une période et cliquez sur "Filtrer"</td>
        </tr>
        </tbody>
    </table>
    <div id="total-global" style="margin-top:30px;font-size:1.2em;font-weight:bold;"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let currentData = {};
    let chart = null;

    function ajax(method, url, data, callback, errorCallback) {
        const xhr = new XMLHttpRequest();
        xhr.open(method, apiBase + url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        callback(JSON.parse(xhr.responseText));
                    } catch (e) {
                        if (errorCallback) errorCallback("Erreur de parsing JSON");
                    }
                } else {
                    if (errorCallback) errorCallback(`Erreur HTTP: ${xhr.status}`);
                }
            }
        };
        xhr.send(data);
    }

    function chargerDonnees() {
        const dateDebut = document.getElementById('date_debut').value;
        const dateFin = document.getElementById('date_fin').value;

        if (!dateDebut || !dateFin) {
            alert('Veuillez sélectionner une période complète.');
            return;
        }

        const tbody = document.getElementById('tbody-interets');
        tbody.innerHTML = '<tr><td colspan="2" class="loading">Chargement...</td></tr>';

        const params = `date_debut=${encodeURIComponent(dateDebut)}&date_fin=${encodeURIComponent(dateFin)}`;
        ajax("POST", "/api/interets/mois", params, function (data) {
            currentData = data;
            afficherTableau(data);
            afficherGraphique(data);
        }, function (error) {
            tbody.innerHTML = `<tr><td colspan="2" class="no-data">Erreur: ${error}</td></tr>`;
            afficherGraphique({});
        });
    }

    function afficherTableau(data) {
        const tbody = document.getElementById('tbody-interets');
        tbody.innerHTML = "";

        let totalInterets = 0;

        if (Object.keys(data).length === 0) {
            tbody.innerHTML = '<tr><td colspan="2" class="no-data">Aucun résultat pour cette période</td></tr>';
            document.getElementById('total-global').textContent = '';
            return;
        }

        for (const mois in data) {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                    <td><strong>${formatMois(mois)}</strong></td>
                    <td class="amount">${parseFloat(data[mois]).toLocaleString('fr-FR')} €</td>
                `;
            tbody.appendChild(tr);
            totalInterets += parseFloat(data[mois]);
        }

        // Ligne de total
        const totalRow = document.createElement("tr");
        totalRow.className = "total-row";
        totalRow.innerHTML = `
                <td><strong>TOTAL</strong></td>
                <td class="amount" style="color: white">${totalInterets.toLocaleString('fr-FR')} €</td>
            `;
        tbody.appendChild(totalRow);

        document.getElementById('total-global').textContent = `Total intérêts gagnés sur la période : ${totalInterets.toLocaleString('fr-FR')} €`;
    }

    function afficherGraphique(data) {
        const canvas = document.getElementById('chart-interets');
        const ctx = canvas.getContext('2d');
        const labels = Object.keys(data).map(formatMois);
        const values = Object.values(data).map(v => parseFloat(v));

        // Fix: reset canvas size to avoid infinite growth
        canvas.width = canvas.parentElement.offsetWidth;
        canvas.height = 420;

        if (chart) {
            chart.destroy();
            chart = null;
        }

        if (labels.length === 0) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            return;
        }

        chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Intérêts gagnés (€)',
                    data: values,
                    backgroundColor: 'rgba(52, 152, 219, 0.7)',
                    borderColor: 'rgba(41, 128, 185, 1)',
                    borderWidth: 1,
                    barPercentage: 0.7,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {display: false},
                    title: {
                        display: true,
                        text: 'Évolution des intérêts gagnés par mois',
                        font: {size: 20}
                    }
                },
                layout: {
                    padding: 20
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {display: true, text: 'Intérêts (€)'},
                        ticks: {font: {size: 16}}
                    },
                    x: {
                        title: {display: true, text: 'Mois'},
                        ticks: {font: {size: 16}}
                    }
                }
            }
        });
    }

    function exporterCSV() {
        if (Object.keys(currentData).length === 0) {
            alert('Aucune donnée à exporter. Veuillez d\'abord filtrer les données.');
            return;
        }

        let csv = 'Mois;Intérêts (€)\n';
        for (const mois in currentData) {
            csv += `${mois};${currentData[mois]}\n`;
        }

        const blob = new Blob([csv], {type: 'text/csv'});
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `interets_${new Date().toISOString().slice(0, 10)}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    }

    function formatMois(mois) {
        const [annee, moisNum] = mois.split('-');
        const moisNoms = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        return `${moisNoms[parseInt(moisNum) - 1]} ${annee}`;
    }

    // Charger la config API globale si présente
    if (typeof window.apiBase === 'undefined') {
        var script = document.createElement('script');
        script.src = '/Web/S4/banque/public/api-config.js';
        script.onload = function () {
            window.onload && window.onload();
        };
        document.head.appendChild(script);
    }

    window.onload = function () {
        const now = new Date();
        const yyyy_mm = now.toISOString().slice(0, 7);
        document.getElementById('date_debut').value = yyyy_mm;
        document.getElementById('date_fin').value = yyyy_mm;
    };
</script>
</body>
</html>
