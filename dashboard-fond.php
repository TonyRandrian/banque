<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord des fonds - Établissement Financier</title>
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
            background-color: #27ae60;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #229954;
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
            background-color: #27ae60;
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
            background-color: #27ae60 !important;
            color: white;
            font-weight: bold;
        }

        .total-row td {
            border-top: 2px solid #229954;
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
            background: linear-gradient(135deg, #27ae60, #229954);
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

        .info-box {
            background: #e8f5e8;
            border-left: 4px solid #27ae60;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .info-box h4 {
            margin: 0 0 10px 0;
            color: #27ae60;
        }

        .info-box p {
            margin: 0;
            color: #2c3e50;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>💰 Solde des fonds par mois</h1>
    
    
    <div class="filter-section">
        <div class="filter-bar">
            <label for="date_debut">📅 Période début :</label>
            <input type="month" id="date_debut">
            <label for="date_fin">📅 Période fin :</label>
            <input type="month" id="date_fin">
            <button onclick="chargerDonnees()">🔍 Filtrer</button>
        </div>
    </div>

    <table id="table-fonds">
        <thead>
        <tr>
            <th>📅 Mois/Année</th>
            <th>💰 Solde disponible (€)</th>
        </tr>
        </thead>
        <tbody id="tbody-fonds">
        <tr>
            <td colspan="2" class="loading">Sélectionnez une période et cliquez sur "Filtrer"</td>
        </tr>
        </tbody>
    </table>
    <div id="total-global" style="margin-top:30px;font-size:1.2em;font-weight:bold;"></div>
</div>

<script>
    let currentData = {};

    function ajax(method, url, data, callback, errorCallback) {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
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

        // Vérification que date début <= date fin
        if (dateDebut > dateFin) {
            alert('La date de début doit être antérieure ou égale à la date de fin.');
            return;
        }

        const tbody = document.getElementById('tbody-fonds');
        tbody.innerHTML = '<tr><td colspan="2" class="loading">Chargement des données...</td></tr>';

        const params = `date_debut=${encodeURIComponent(dateDebut)}&date_fin=${encodeURIComponent(dateFin)}`;
        ajax("POST", "/banque/ws/api/fonds/mois", params, function (data) {
            if (data && typeof data === 'object' && !data.error) {
                currentData = data;
                afficherTableau(data);
            } else {
                const errorMsg = data.error || 'Erreur inconnue lors du chargement des données';
                tbody.innerHTML = `<tr><td colspan="2" class="no-data">Erreur: ${errorMsg}</td></tr>`;
                document.getElementById('total-global').textContent = '';
            }
        }, function (error) {
            tbody.innerHTML = `<tr><td colspan="2" class="no-data">Erreur de connexion: ${error}</td></tr>`;
            document.getElementById('total-global').textContent = '';
        });
    }

    function afficherTableau(data) {
        const tbody = document.getElementById('tbody-fonds');
        tbody.innerHTML = "";

        let soldeActuel = 0;
        let totalSoldes = 0;
        let nombreMois = 0;

        if (!data || Object.keys(data).length === 0) {
            tbody.innerHTML = '<tr><td colspan="2" class="no-data">Aucun résultat pour cette période. Vérifiez que des données existent pour ces dates.</td></tr>';
            document.getElementById('total-global').textContent = '';
            return;
        }

        const moisTries = Object.keys(data).sort();
        
        for (const mois of moisTries) {
            const solde = parseFloat(data[mois]) || 0;
            const tr = document.createElement("tr");
            
            const styleClass = solde < 0 ? 'style="color: #e74c3c;"' : '';
            
            tr.innerHTML = `
                <td><strong>${formatMois(mois)}</strong></td>
                <td class="amount" ${styleClass}>${solde.toLocaleString('fr-FR')} €</td>
            `;
            tbody.appendChild(tr);
            
            soldeActuel = solde;
            totalSoldes += solde;
            nombreMois++;
        }

        if (moisTries.length > 0) {
            const soldeMoyen = totalSoldes / nombreMois;
            document.getElementById('total-global').innerHTML = `
                <div style="color: #27ae60;">
                    💰 <strong>Solde actuel</strong> (${formatMois(moisTries[moisTries.length - 1])}) : <strong>${soldeActuel.toLocaleString('fr-FR')} €</strong><br>
                    📊 <strong>Solde moyen</strong> sur la période : <strong>${soldeMoyen.toLocaleString('fr-FR')} €</strong><br>
                    📈 <strong>Évolution</strong> : ${moisTries.length > 1 ? (soldeActuel - parseFloat(data[moisTries[0]])).toLocaleString('fr-FR') + ' €' : 'N/A'}
                </div>
            `;
        }
    }

    function exporterCSV() {
        if (Object.keys(currentData).length === 0) {
            alert('Aucune donnée à exporter. Veuillez d\'abord filtrer les données.');
            return;
        }

        let csv = 'Mois;Solde (€)\n';
        const moisTries = Object.keys(currentData).sort();
        for (const mois of moisTries) {
            csv += `${mois};${currentData[mois]}\n`;
        }

        const blob = new Blob([csv], {type: 'text/csv'});
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `solde_fonds_${new Date().toISOString().slice(0, 10)}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    }

    function formatMois(mois) {
        const [annee, moisNum] = mois.split('-');
        const moisNoms = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        return `${moisNoms[parseInt(moisNum) - 1]} ${annee}`;
    }

    window.onload = function () {
        const now = new Date();
        const yyyy_mm = now.toISOString().slice(0, 7);
        document.getElementById('date_debut').value = yyyy_mm;
        document.getElementById('date_fin').value = yyyy_mm;
        
        // Charger les statistiques générales
        chargerStatistiques();
    };

    function chargerStatistiques() {
        ajax("GET", "/banque/ws/api/fonds/stats", "", function (data) {
            if (data && !data.error) {
                // Afficher les statistiques en haut si nécessaire
                console.log("Statistiques des fonds:", data);
            }
        }, function (error) {
            console.log("Erreur lors du chargement des statistiques:", error);
        });
    }
</script>
</body>
</html>
