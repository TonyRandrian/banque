<div class="container">
    <div class="page-header">
        <h1>Gestion des Fonds</h1>
        <p class="page-description">Ajoutez et gérez les mouvements de fonds de la banque</p>
    </div>

    <div class="form-container">
        <h3>Ajouter un mouvement de fond</h3>
        <form id="fond-form">
            <div class="form-row">
                <input type="number" id="montant" name="montant" placeholder="Montant (€)" step="0.01" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ajouter le mouvement</button>
                <button type="button" onclick="resetForm()" class="btn btn-secondary">Annuler</button>
            </div>
        </form>
    </div>

    <div id="result" class="alert" style="display: none;"></div>

    <div class="table-container">
        <h3>Historique des mouvements</h3>
        <table id="table-fonds">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date du mouvement</th>
                    <th>Solde (€)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="3">Chargement...</td></tr>
            </tbody>
        </table>
    </div>
</div>
<script>
    // Point d'accès API centralisé
    if (typeof window.apiBase === 'undefined') {
        var apiBase = "http://localhost/banque/ws";
    } else {
        var apiBase = window.apiBase;
    }

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
        const resultDiv = document.getElementById('result');
        resultDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
        resultDiv.textContent = message;
        resultDiv.style.display = 'block';
        
        // Masquer le message après 5 secondes
        setTimeout(() => {
            resultDiv.style.display = 'none';
        }, 5000);
    }

    function chargerFonds() {
        ajax("GET", "/api/fond/", null, function(data) {
            const tbody = document.querySelector("#table-fonds tbody");
            tbody.innerHTML = "";
            
            if (!Array.isArray(data)) {
                tbody.innerHTML = '<tr><td colspan="3">Erreur: Format de données invalide</td></tr>';
                return;
            }
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3">Aucun mouvement de fond trouvé</td></tr>';
                return;
            }
            
            data.forEach(f => {
                const tr = document.createElement("tr");
                const date = new Date(f.date_mouvement).toLocaleDateString('fr-FR');
                const solde = parseFloat(f.solde).toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' });
                
                tr.innerHTML = `
                    <td>${f.id}</td>
                    <td>${date}</td>
                    <td><strong>${solde}</strong></td>
                `;
                tbody.appendChild(tr);
            });
        }, function(error) {
            const tbody = document.querySelector("#table-fonds tbody");
            tbody.innerHTML = `<tr><td colspan="3">Erreur: ${error}</td></tr>`;
        });
    }

    function resetForm() {
        document.getElementById('fond-form').reset();
        document.getElementById('result').style.display = 'none';
    }

    document.getElementById('fond-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const montant = document.getElementById('montant').value;
        
        if (!montant || parseFloat(montant) <= 0) {
            showMessage('Veuillez saisir un montant valide supérieur à 0', 'error');
            return;
        }
        
        const params = `montant=${encodeURIComponent(montant)}`;
        ajax("POST", "/api/fond/ajout", params, function(data) {
            showMessage(data.message || 'Mouvement ajouté avec succès', 'success');
            resetForm();
            chargerFonds();
        }, function(error) {
            showMessage('Erreur lors de l\'ajout: ' + error, 'error');
        });
    });

    // Chargement initial
    document.addEventListener('DOMContentLoaded', function() {
        chargerFonds();
    });
</script>
