<div class="container">
    <div class="page-header">
        <h1>Gestion des Prêts</h1>
        <p class="page-description">Gérez les demandes de prêts et leurs modalités</p>
    </div>

    <div class="form-container">
        <h3>Ajouter / Modifier un prêt</h3>
        <form id="pret-form">
            <input type="hidden" id="id">
            <div class="form-row">
                <input type="number" id="montant" placeholder="Montant (€)" step="0.01" min="1" required>
                <input type="number" id="duree_remboursement" placeholder="Durée remboursement (mois)" min="1" max="360" required>
            </div>
            <div class="form-row">
                <input type="date" id="date_demande" required>
                <select id="modalite_id" required>
                    <option value="">Sélectionnez une modalité</option>
                </select>
            </div>
            <div class="form-row">
                <select id="type_pret_id" required>
                    <option value="">Sélectionnez un type de prêt</option>
                </select>
            </div>
            <div class="form-row">
                <input type="number" id="taux_assurance" placeholder="Taux assurance (%)" step="0.01" min="0" max="100">
            </div>
            <div class="form-row">
                <label for="assurance_par_mois">
                    <input type="checkbox" id="assurance_par_mois" value="1"> Assurance par mois
                </label>
            </div>
            <div class="form-row">
                <select id="compte_client_id" required>
                    <option value="">Sélectionnez un client</option>
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
        <h3>Liste des prêts</h3>
        <table id="table-prets">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Durée remboursement</th>
                    <th>Montant</th>
                    <th>Date demande</th>
                    <th>Modalité</th>
                    <th>Type prêt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="7">Chargement...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
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

    function chargerPrets() {
        ajax("GET", "/prets", null, (data) => {
            const tbody = document.querySelector("#table-prets tbody");
            tbody.innerHTML = "";
            
            if (!Array.isArray(data)) {
                tbody.innerHTML = '<tr><td colspan="7">Erreur: Format de données invalide</td></tr>';
                return;
            }
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7">Aucun prêt trouvé</td></tr>';
                return;
            }
            
            data.forEach(pret => {
                const tr = document.createElement("tr");
                // La date est déjà au format YYYY-MM-DD dans la base
                const dateFormatted = new Date(pret.date_demande).toLocaleDateString('fr-FR');
                const montantFormatted = parseFloat(pret.montant).toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' });
                
                tr.innerHTML = `
                    <td><strong>#${pret.id}</strong></td>
                    <td><span class="badge badge-info">${pret.duree_remboursement} mois</span></td>
                    <td><strong>${montantFormatted}</strong></td>
                    <td>${dateFormatted}</td>
                    <td><span class="badge badge-secondary">${pret.modalite_libelle || '#' + pret.modalite_id}</span></td>
                    <td><span class="badge badge-primary">${pret.type_pret_libelle || '#' + pret.type_pret_id}</span></td>
                    <td>
                        <button class="btn btn-outline btn-sm" onclick='remplirFormulaire(${JSON.stringify(pret)})'>
                            ✏️ Modifier
                        </button>
                        <button class="btn btn-danger btn-sm" onclick='supprimerPret(${pret.id})'>
                            🗑️ Supprimer
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }, (error) => {
            const tbody = document.querySelector("#table-prets tbody");
            tbody.innerHTML = `<tr><td colspan="7">Erreur: ${error}</td></tr>`;
        });
    }

    function chargerModalitesPourSelect() {
        ajax("GET", "/modalites", null, (data) => {
            const select = document.getElementById("modalite_id");
            select.innerHTML = '<option value="">Sélectionnez une modalité</option>';
            
            if (Array.isArray(data)) {
                data.forEach(modalite => {
                    const option = document.createElement("option");
                    option.value = modalite.id;
                    option.textContent = modalite.libelle ? 
                        `${modalite.libelle} (${modalite.nb_mois} mois)` : 
                        `Modalité #${modalite.id}`;
                    select.appendChild(option);
                });
            }
        }, (error) => {
            console.error('Erreur lors du chargement des modalités:', error);
        });
    }

    function chargerTypesPourSelect() {
        ajax("GET", "/type-prets", null, (data) => {
            const select = document.getElementById("type_pret_id");
            select.innerHTML = '<option value="">Sélectionnez un type de prêt</option>';
            
            if (Array.isArray(data)) {
                data.forEach(type => {
                    const option = document.createElement("option");
                    option.value = type.id;
                    option.textContent = type.libelle ? 
                        `${type.libelle} (${type.taux}%)` : 
                        `Type #${type.id}`;
                    select.appendChild(option);
                });
            }
        }, (error) => {
            console.error('Erreur lors du chargement des types de prêt:', error);
        });
    }

    function chargerComptesClientsPourSelect() {
        ajax("GET", "/comptes-clients", null, (data) => {
            const select = document.getElementById("compte_client_id");
            select.innerHTML = '<option value="">Sélectionnez un client</option>';
            if (Array.isArray(data)) {
                data.forEach(client => {
                    const option = document.createElement("option");
                    option.value = client.id;
                    option.textContent = client.numero ? client.numero : `Client #${client.id}`;
                    select.appendChild(option);
                });
            }
        }, (error) => {
            console.error('Erreur lors du chargement des comptes clients:', error);
        });
    }

    function ajouterOuModifier() {
        const id = document.getElementById("id").value;
        const duree_remboursement = document.getElementById("duree_remboursement").value;
        const montant = document.getElementById("montant").value;
        const date_demande = document.getElementById("date_demande").value;
        const modalite_id = document.getElementById("modalite_id").value;
        const type_pret_id = document.getElementById("type_pret_id").value;
        const taux_assurance = document.getElementById("taux_assurance").value || 0;
        const assurance_par_mois = document.getElementById("assurance_par_mois").checked ? 1 : 0;
        const compte_client_id = document.getElementById("compte_client_id").value;

        // Validation
        if (!duree_remboursement || parseInt(duree_remboursement) <= 0) {
            showMessage("Veuillez saisir une durée de remboursement valide", 'error');
            return;
        }

        if (!montant || parseFloat(montant) <= 0) {
            showMessage("Veuillez saisir un montant valide supérieur à 0", 'error');
            return;
        }

        if (!date_demande) {
            showMessage("Veuillez sélectionner une date de demande", 'error');
            return;
        }

        if (!modalite_id) {
            showMessage("Veuillez sélectionner une modalité", 'error');
            return;
        }

        if (!type_pret_id) {
            showMessage("Veuillez sélectionner un type de prêt", 'error');
            return;
        }

        if (!compte_client_id) {
            showMessage("Veuillez sélectionner un client", 'error');
            return;
        }

        const data = `duree_remboursement=${duree_remboursement}&montant=${montant}&date_demande=${date_demande}&modalite_id=${modalite_id}&type_pret_id=${type_pret_id}&taux_assurance=${taux_assurance}&assurance_par_mois=${assurance_par_mois}&compte_client_id=${compte_client_id}`;

        if (id) {
            ajax("PUT", `/prets/${id}`, data, (response) => {
                showMessage(response.message || 'Prêt modifié avec succès', 'success');
                resetForm();
                chargerPrets();
            }, (error) => {
                showMessage("Erreur lors de la modification: " + error, 'error');
            });
        } else {
            ajax("POST", "/prets", data, (response) => {
                showMessage(response.message || 'Prêt ajouté avec succès', 'success');
                resetForm();
                chargerPrets();
            }, (error) => {
                showMessage("Erreur lors de l'ajout: " + error, 'error');
            });
        }
    }

    function remplirFormulaire(pret) {
        document.getElementById("id").value = pret.id;
        document.getElementById("duree_remboursement").value = pret.duree_remboursement;
        document.getElementById("montant").value = pret.montant;
        document.getElementById("date_demande").value = pret.date_demande;
        document.getElementById("modalite_id").value = pret.modalite_id;
        document.getElementById("type_pret_id").value = pret.type_pret_id;
        document.getElementById("taux_assurance").value = pret.taux_assurance || 0;
        document.getElementById("assurance_par_mois").checked = pret.assurance_par_mois == 1;
        document.getElementById("compte_client_id").value = pret.compte_client_id || "";
        
        // Faire défiler vers le formulaire
        document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
    }

    function supprimerPret(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce prêt ?")) {
            ajax("DELETE", `/prets/${id}`, null, (response) => {
                showMessage(response.message || 'Prêt supprimé avec succès', 'success');
                chargerPrets();
            }, (error) => {
                showMessage("Erreur lors de la suppression: " + error, 'error');
            });
        }
    }

    function resetForm() {
        document.getElementById("pret-form").reset();
        document.getElementById("id").value = "";
        document.getElementById('result').style.display = 'none';
    }

    // Gestion du formulaire
    document.getElementById('pret-form').addEventListener('submit', function(e) {
        e.preventDefault();
        ajouterOuModifier();
    });

    // Chargement initial
    chargerPrets();
    chargerModalitesPourSelect();
    chargerTypesPourSelect();
    chargerComptesClientsPourSelect();
</script>