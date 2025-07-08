<script type="text/javascript" src="api-config.js"></script>
<script src="assets/js/fontawesome.all.js"></script>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-balance-scale me-3"></i>Comparaison de Simulations</h1>
        <p class="page-description">Sélectionnez et comparez deux simulations de prêt pour analyser leurs conditions</p>
    </div>

    <div id="result" class="alert" style="display: none;"></div>

    <!-- Section de sélection des simulations -->
    <div class="form-container">
        <h3><i class="fas fa-list me-2"></i>Liste des simulations disponibles</h3>
        <p class="text-muted mb-3">Sélectionnez exactement deux simulations pour les comparer</p>
        
        <div id="simulations-list" class="table-container">
            <!-- Le tableau des simulations sera chargé ici via JavaScript -->
        </div>
        
        <div class="form-actions mt-3">
            <button id="btn-comparer" class="btn btn-primary" disabled>
                <i class="fas fa-balance-scale me-2"></i>Comparer les simulations sélectionnées
            </button>
            <button id="btn-reset" class="btn btn-secondary" onclick="resetSelection()">
                <i class="fas fa-undo me-2"></i>Réinitialiser
            </button>
        </div>
    </div>

    <!-- Section de résultats de comparaison -->
    <div id="comparaison-results" class="form-container" style="display: none;">
        <h3><i class="fas fa-chart-bar me-2"></i>Résultats de la comparaison</h3>
        
        <!-- Statistiques générales côte à côte -->
        <div class="comparison-stats">
            <div class="stats-row" style="display: flex; justify-content: space-between;">
                <div class="stat-card stat-card-primary">
                    <div class="stat-header">
                        <i class="fas fa-calculator"></i>
                        <h4>Simulation #<span id="sim1-id"></span></h4>
                    </div>
                    <div id="sim1-stats" class="stat-content"></div>
                </div>
                
                <div class="comparison-divider">
                    <div class="vs-badge">
                        <i class="fas fa-balance-scale"></i>
                        <span>VS</span>
                    </div>
                </div>
                
                <div class="stat-card stat-card-success">
                    <div class="stat-header">
                        <i class="fas fa-calculator"></i>
                        <h4>Simulation #<span id="sim2-id"></span></h4>
                    </div>
                    <div id="sim2-stats" class="stat-content"></div>
                </div>
            </div>
        </div>

        <!-- Tableaux d'échéanciers côte à côte -->
        <div class="comparison-tables">
            <div class="table-comparison-row">
                <div class="table-container table-container-left">
                    <h4 class="table-title table-title-primary">
                        <i class="fas fa-table me-2"></i>Échéancier Simulation #<span id="sim1-id-table"></span>
                    </h4>
                    <div class="table-responsive-custom">
                        <table id="table-sim1" class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Mois</th>
                                    <th>Mensualité</th>
                                    <th>Intérêts</th>
                                    <th>Capital</th>
                                    <th>Restant</th>
                                </tr>
                            </thead>
                            <tbody id="sim1-echeancier">
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="table-container table-container-right">
                    <h4 class="table-title table-title-success">
                        <i class="fas fa-table me-2"></i>Échéancier Simulation #<span id="sim2-id-table"></span>
                    </h4>
                    <div class="table-responsive-custom">
                        <table id="table-sim2" class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Mois</th>
                                    <th>Mensualité</th>
                                    <th>Intérêts</th>
                                    <th>Capital</th>
                                    <th>Restant</th>
                                </tr>
                            </thead>
                            <tbody id="sim2-echeancier">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let simulations = [];
    let selectedSimulations = [];

    function ajax(method, url, data, callback, errorCallback) {
        const xhr = new XMLHttpRequest();
        xhr.open(method, apiBase + url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        callback(response);
                    } catch (e) {
                        if (errorCallback) errorCallback(`Erreur de parsing JSON: ${e.message}`);
                        else showMessage(`Erreur de parsing JSON: ${e.message}`, 'error');
                    }
                } else {
                    let errorMessage = `Erreur HTTP ${xhr.status}`;
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMessage += `: ${errorResponse.error || errorResponse.message || 'Erreur inconnue'}`;
                    } catch (e) {
                        errorMessage += `: ${xhr.responseText || 'Erreur inconnue'}`;
                    }
                    if (errorCallback) errorCallback(errorMessage);
                    else showMessage(errorMessage, 'error');
                }
            }
        };
        xhr.send(data);
    }

    function showMessage(message, type = 'success') {
        const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
        const alertHtml = `<div class="alert ${alertClass}">${message}</div>`;

        // Afficher le message en haut de la page
        const container = document.querySelector('.container-fluid .col-12');
        const existingAlert = container.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        container.insertAdjacentHTML('afterbegin', alertHtml);

        // Masquer le message après 5 secondes
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) alert.remove();
        }, 5000);
    }

    // Charger les simulations au chargement de la page
    document.addEventListener('DOMContentLoaded', function () {
        loadSimulations();

        document.getElementById('btn-comparer').addEventListener('click', function () {
            if (selectedSimulations.length === 2) {
                compareSimulations(selectedSimulations[0], selectedSimulations[1]);
            }
        });
        
        // Ajouter des tooltips aux boutons
        const btnComparer = document.getElementById('btn-comparer');
        btnComparer.title = "Sélectionnez exactement 2 simulations pour les comparer";
        
        const btnReset = document.getElementById('btn-reset');
        btnReset.title = "Réinitialiser toutes les sélections";
    });

    function loadSimulations() {
        ajax("GET", "/comparaisons", null, function (data) {
            simulations = data;
            displaySimulations();
        }, function (error) {
            document.getElementById('simulations-list').innerHTML =
                `<div class="alert alert-danger">Erreur lors du chargement des simulations: ${error}</div>`;
        });
    }

    function displaySimulations() {
        const container = document.getElementById('simulations-list');

        if (!Array.isArray(simulations)) {
            container.innerHTML = '<div class="alert alert-danger">Format de données invalide</div>';
            return;
        }

        if (simulations.length === 0) {
            container.innerHTML = '<div class="alert alert-info">Aucune simulation trouvée</div>';
            return;
        }

        let html = `
            <table id="table-simulations">
                <thead>
                    <tr>
                        <th>N° Simulation</th>
                        <th>Montant demandé</th>
                        <th>Durée</th>
                        <th>Taux Assurance</th>
                        <th>Date de demande</th>
                        <th>Sélection</th>
                    </tr>
                </thead>
                <tbody>
        `;

        simulations.forEach(simulation => {
            const montant = parseFloat(simulation.montant).toLocaleString('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            });
            const dateCreation = new Date(simulation.date_demande).toLocaleDateString('fr-FR');

            html += `
                <tr class="simulation-row" data-id="${simulation.id}">
                    <td><strong>#${simulation.id}</strong></td>
                    <td class="amount">${montant}</td>
                    <td>${simulation.duree_remboursement} mois</td>
                    <td>${simulation.taux_assurance}%</td>
                    <td>${dateCreation}</td>
                    <td class="text-center">
                        <input class="form-check-input simulation-checkbox" 
                               type="checkbox" 
                               value="${simulation.id}" 
                               id="sim-${simulation.id}"
                               onchange="handleSelectionChange(this)">
                    </td>
                </tr>
            `;
        });

        html += `
                </tbody>
            </table>
        `;

        container.innerHTML = html;
    }

    function handleSelectionChange(checkbox) {
        const simulationId = parseInt(checkbox.value);
        const row = checkbox.closest('tr');

        if (checkbox.checked) {
            if (selectedSimulations.length < 2) {
                selectedSimulations.push(simulationId);
                row.classList.add('selected-row');
            } else {
                // Désélectionner le premier et ajouter le nouveau
                const oldId = selectedSimulations.shift();
                const oldCheckbox = document.getElementById(`sim-${oldId}`);
                if (oldCheckbox) {
                    oldCheckbox.checked = false;
                    const oldRow = oldCheckbox.closest('tr');
                    oldRow.classList.remove('selected-row');
                }
                selectedSimulations.push(simulationId);
                row.classList.add('selected-row');
                showMessage('Maximum 2 simulations. La première sélection a été remplacée.', 'info');
            }
        } else {
            selectedSimulations = selectedSimulations.filter(id => id !== simulationId);
            row.classList.remove('selected-row');
        }

        updateCompareButton();
    }

    function updateCompareButton() {
        const btnComparer = document.getElementById('btn-comparer');
        const count = selectedSimulations.length;
        
        btnComparer.disabled = count !== 2;

        if (count === 0) {
            btnComparer.innerHTML = '<i class="fas fa-balance-scale me-2"></i>Comparer les simulations sélectionnées';
            btnComparer.className = 'btn btn-primary';
        } else if (count === 1) {
            btnComparer.innerHTML = '<i class="fas fa-balance-scale me-2"></i>Sélectionnez une deuxième simulation';
            btnComparer.className = 'btn btn-warning';
        } else {
            btnComparer.innerHTML = '<i class="fas fa-balance-scale me-2"></i>Comparer les 2 simulations sélectionnées';
            btnComparer.className = 'btn btn-success';
        }
    }

    function resetSelection() {
        selectedSimulations = [];
        document.querySelectorAll('.simulation-checkbox').forEach(checkbox => {
            checkbox.checked = false;
            checkbox.closest('tr').classList.remove('selected-row');
        });
        updateCompareButton();
        document.getElementById('comparaison-results').style.display = 'none';
        showMessage('Sélection réinitialisée', 'info');
    }

    function showMessage(message, type = 'success') {
        const resultDiv = document.getElementById('result');
        resultDiv.className = `alert alert-${type === 'error' ? 'danger' : type === 'info' ? 'info' : 'success'}`;
        resultDiv.textContent = message;
        resultDiv.style.display = 'block';
        
        // Masquer le message après 5 secondes
        setTimeout(() => {
            resultDiv.style.display = 'none';
        }, 5000);
    }

    function compareSimulations(id1, id2) {
        showMessage('Comparaison en cours...', 'info');

        ajax("GET", `/comparaison/${id1}/${id2}`, null, function (data) {
            displayComparison(data);
            showMessage('Comparaison terminée avec succès', 'success');
        }, function (error) {
            showMessage(`Erreur lors de la comparaison: ${error}`, 'error');
        });
    }

    function displayComparison(data) {
        const resultsDiv = document.getElementById('comparaison-results');

        if (!data || !data.simulation1 || !data.simulation2) {
            showMessage('Données de comparaison invalides', 'error');
            return;
        }

        const sim1 = data.simulation1;
        const sim2 = data.simulation2;

        // Mettre à jour les IDs
        document.getElementById('sim1-id').textContent = sim1.id;
        document.getElementById('sim2-id').textContent = sim2.id;
        document.getElementById('sim1-id-table').textContent = sim1.id;
        document.getElementById('sim2-id-table').textContent = sim2.id;

        // Afficher les statistiques
        document.getElementById('sim1-stats').innerHTML = formatStats(sim1.stats);
        document.getElementById('sim2-stats').innerHTML = formatStats(sim2.stats);

        // Afficher les échéanciers
        document.getElementById('sim1-echeancier').innerHTML = formatEcheancier(sim1.echeancier);
        document.getElementById('sim2-echeancier').innerHTML = formatEcheancier(sim2.echeancier);

        // Afficher la section des résultats
        resultsDiv.style.display = 'block';

        // Scroll vers les résultats
        resultsDiv.scrollIntoView({
            behavior: 'smooth'
        });
    }

    function formatStats(stats) {
        const totalInteret = parseFloat(stats.total_interet).toLocaleString('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        });
        const interetMoyen = parseFloat(stats.interet_moyen_mois).toLocaleString('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        });
        // Affichage du montant demandé (utilisé dans la liste des simulations)
        let montant = '';
        if (typeof stats.montant !== 'undefined') {
            montant = parseFloat(stats.montant).toLocaleString('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            });
        } else if (typeof stats.montant_emprunte !== 'undefined') {
            montant = parseFloat(stats.montant_emprunte).toLocaleString('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            });
        } else {
            montant = 'N/A';
        }
        const sommeMensualites = parseFloat(stats.somme_mensualites || 0).toLocaleString('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        });

        return `
            <div class="stat-item">
                <div class="stat-icon">💰</div>
                <div class="stat-details">
                    <span class="stat-label">Montant emprunté</span>
                    <span class="stat-value"><td class='amount'>${montant}</td></span>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">📅</div>
                <div class="stat-details">
                    <span class="stat-label">Durée</span>
                    <span class="stat-value">${stats.duree_pret} mois</span>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">💳</div>
                <div class="stat-details">
                    <span class="stat-label">Somme des mensualités</span>
                    <span class="stat-value">${sommeMensualites}</span>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">📈</div>
                <div class="stat-details">
                    <span class="stat-label">Total intérêts</span>
                    <span class="stat-value stat-danger">${totalInteret}</span>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">📊</div>
                <div class="stat-details">
                    <span class="stat-label">Intérêt moyen/mois</span>
                    <span class="stat-value">${interetMoyen}</span>
                </div>
            </div>
        `;
    }

    function formatEcheancier(echeancier) {
        if (!Array.isArray(echeancier)) {
            return '<tr><td colspan="5" class="text-center no-data"><i class="fas fa-exclamation-triangle me-2"></i>Aucune donnée d\'échéancier disponible</td></tr>';
        }

        let html = '';
        echeancier.forEach((paiement, index) => {
            const mensualite = parseFloat(paiement.mensualite).toLocaleString('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            });
            const interet = parseFloat(paiement.interet).toLocaleString('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            });
            const amortissement = parseFloat(paiement.amortissement).toLocaleString('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            });
            const montantRestant = parseFloat(paiement.montant_restant).toLocaleString('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            });

            html += `
                <tr>
                    <td class="text-center"><strong>${index + 1}</strong></td>
                    <td class="amount amount-primary">${mensualite}</td>
                    <td class="amount amount-danger">${interet}</td>
                    <td class="amount amount-success">${amortissement}</td>
                    <td class="amount amount-info">${montantRestant}</td>
                </tr>
            `;
        });
        return html;
    }
</script>