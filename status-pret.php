<div class="container">
    <div class="page-header">
        <h1>Gestion des Statuts de Prêt</h1>
        <p class="page-description">Suivez et gérez les statuts des demandes de prêts</p>
    </div>

    <div class="form-container">
        <h3>Ajouter / Modifier un statut de prêt</h3>
        <form id="status-pret-form">
            <input type="hidden" id="id">
            <div class="form-row">
                <input type="date" id="date_status" required>
                <select id="enum_pret_id" required>
                    <option value="">Sélectionnez un statut</option>
                </select>
            </div>
            <div class="form-row">
                <select id="pret_id" required>
                    <option value="">Sélectionnez un prêt</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ajouter / Modifier</button>
                <button type="button" onclick="resetForm()" class="btn btn-secondary">Annuler</button>
            </div>
        </form>
    </div>

    <div id="result" class="alert" style="display: none;"></div>

    <div class="table-container">
        <h3>Liste des statuts de prêts</h3>
        <table id="table-status-prets">
            <thead>
            <tr>
                <th>ID</th>
                <th>Date status</th>
                <th>Statut</th>
                <th>Prêt</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="5">Chargement...</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>


<script>
    const apiBase = window.apiBase;

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

    function chargerStatusPrets() {
        ajax("GET", "/status-prets", null, (data) => {
            const tbody = document.querySelector("#table-status-prets tbody");
            tbody.innerHTML = "";

            if (!Array.isArray(data)) {
                tbody.innerHTML = '<tr><td colspan="5">Erreur: Format de données invalide</td></tr>';
                return;
            }

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5">Aucun statut de prêt trouvé</td></tr>';
                return;
            }

            data.forEach(status => {
                const tr = document.createElement("tr");
                const dateFormatted = new Date(status.date_status).toLocaleDateString('fr-FR');

                tr.innerHTML = `
                    <td><strong>#${status.id}</strong></td>
                    <td>${dateFormatted}</td>
                    <td><span class="badge badge-primary">${status.enum_libelle || '#' + status.enum_pret_id}</span></td>
                    <td><span class="badge badge-info">Prêt #${status.pret_id}</span></td>
                    <td>
                        <button class="btn btn-outline btn-sm" onclick='remplirFormulaire(${JSON.stringify(status)})'>
                            ✏️ Modifier
                        </button>
                        <button class="btn btn-danger btn-sm" onclick='supprimerStatusPret(${status.id})'>
                            🗑️ Supprimer
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }, (error) => {
            const tbody = document.querySelector("#table-status-prets tbody");
            tbody.innerHTML = `<tr><td colspan="5">Erreur: ${error}</td></tr>`;
        });
    }

    function chargerPretsPourSelect() {
        ajax("GET", "/prets", null, (data) => {
            const select = document.getElementById("pret_id");
            select.innerHTML = '<option value="">Sélectionnez un prêt</option>';

            if (Array.isArray(data)) {
                data.forEach(pret => {
                    const option = document.createElement("option");
                    option.value = pret.id;
                    const montant = parseFloat(pret.montant).toLocaleString('fr-FR', {
                        style: 'currency',
                        currency: 'EUR'
                    });
                    option.textContent = `Prêt #${pret.id} - ${montant}`;
                    select.appendChild(option);
                });
            }
        }, (error) => {
            console.error('Erreur lors du chargement des prêts:', error);
        });
    }

    function chargerEnumsPourSelect() {
        ajax("GET", "/enum-status-prets", null, (data) => {
            const select = document.getElementById("enum_pret_id");
            select.innerHTML = '<option value="">Sélectionnez un statut</option>';

            if (Array.isArray(data)) {
                data.forEach(enumStatus => {
                    const option = document.createElement("option");
                    option.value = enumStatus.id;
                    option.textContent = enumStatus.libelle || `Statut #${enumStatus.id}`;
                    select.appendChild(option);
                });
            }
        }, (error) => {
            console.error('Erreur lors du chargement des statuts:', error);
        });
    }

    function ajouterOuModifier() {
        const id = document.getElementById("id").value;
        const date_status = document.getElementById("date_status").value;
        const enum_pret_id = document.getElementById("enum_pret_id").value;
        const pret_id = document.getElementById("pret_id").value;

        // Validation
        if (!date_status) {
            showMessage("Veuillez sélectionner une date de statut", 'error');
            return;
        }

        if (!enum_pret_id) {
            showMessage("Veuillez sélectionner un statut", 'error');
            return;
        }

        if (!pret_id) {
            showMessage("Veuillez sélectionner un prêt", 'error');
            return;
        }

        const data = `date_status=${date_status}&enum_pret_id=${enum_pret_id}&pret_id=${pret_id}`;

        if (id) {
            ajax("PUT", `/status-prets/${id}`, data, (response) => {
                showMessage(response.message || 'Statut de prêt modifié avec succès', 'success');
                resetForm();
                chargerStatusPrets();
            }, (error) => {
                showMessage("Erreur lors de la modification: " + error, 'error');
            });
        } else {
            ajax("POST", "/status-prets", data, (response) => {
                showMessage(response.message || 'Statut de prêt ajouté avec succès', 'success');
                resetForm();
                chargerStatusPrets();
            }, (error) => {
                showMessage("Erreur lors de l'ajout: " + error, 'error');
            });
        }
    }

    function remplirFormulaire(status) {
        document.getElementById("id").value = status.id;
        document.getElementById("date_status").value = status.date_status;
        document.getElementById("enum_pret_id").value = status.enum_pret_id;
        document.getElementById("pret_id").value = status.pret_id;

        // Faire défiler vers le formulaire
        document.querySelector('.form-container').scrollIntoView({behavior: 'smooth'});
    }

    function supprimerStatusPret(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce statut de prêt ?")) {
            ajax("DELETE", `/status-prets/${id}`, null, (response) => {
                showMessage(response.message || 'Statut de prêt supprimé avec succès', 'success');
                chargerStatusPrets();
            }, (error) => {
                showMessage("Erreur lors de la suppression: " + error, 'error');
            });
        }
    }

    function resetForm() {
        document.getElementById("status-pret-form").reset();
        document.getElementById("id").value = "";
        document.getElementById('result').style.display = 'none';
    }

    // Gestion du formulaire
    document.getElementById('status-pret-form').addEventListener('submit', function (e) {
        e.preventDefault();
        ajouterOuModifier();
    });

    // Chargement initial
    document.addEventListener('DOMContentLoaded', function () {
        chargerStatusPrets();
        chargerPretsPourSelect();
        chargerEnumsPourSelect();
    });
</script>
