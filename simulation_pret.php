<div class="container">
    <div class="page-header">
        <h1>Simulation de Prêt</h1>
        <p class="page-description">Simulez les mensualités de votre prêt en fonction des paramètres saisis</p>
    </div>

    <div class="form-container">
        <h3>Paramètres de simulation</h3>
        <form id="simulation-form">
            <div class="form-row">
                <input type="number" id="montant" placeholder="Montant du prêt (€)" step="0.01" min="1" required>
                <input type="number" id="duree_remboursement" placeholder="Durée remboursement (mois)" min="1" max="360"
                       required>
            </div>
            <div class="form-row">
                <input type="date" id="date_demande" required>
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
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simuler</button>
                <button type="button" onclick="resetForm()" class="btn btn-secondary">Annuler</button>
            </div>
        </form>
    </div>

    <div id="resultat-simulation" class="alert" style="display: none;"></div>

    <div class="table-container">
        <h3>Tableau d'amortissement</h3>
        <div id="tableau-amortissement"></div>
        <div id="validation-zone" style="margin-top:20px;display:none;">
            <label for="compte_client_select">Choisir un compte client :</label>
            <select id="compte_client_select"></select>
            <button id="valider-simulation" class="btn btn-success">Valider</button>
        </div>
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

    document.getElementById('valider-simulation').onclick = function () {
        const compte_client_id = document.getElementById("compte_client_select").value;
        if (!compte_client_id) {
            showMessage("Veuillez choisir un compte client.", "error");
            return;
        }
        // Récupérer les infos du formulaire
        const montant = parseFloat(document.getElementById("montant").value);
        const duree = parseInt(document.getElementById("duree_remboursement").value, 10);
        const date_demande = document.getElementById("date_demande").value;
        const type_pret_id = document.getElementById("type_pret_id").value;
        const taux_assurance = document.getElementById("taux_assurance").value || 0;
        const assurance_par_mois = document.getElementById("assurance_par_mois").checked ? 1 : 0;

        // Création du prêt
        const pretData = `duree_remboursement=${duree}&montant=${montant}&date_demande=${date_demande}&type_pret_id=${type_pret_id}&taux_assurance=${taux_assurance}&assurance_par_mois=${assurance_par_mois}&compte_client_id=${compte_client_id}`;
        ajax("POST", "/prets", pretData, (response) => {
            if (response && response.data && (response.data.id || response.data.id === 0)) {
                const pret_id = response.data.id;
                let inserted = 0;
                let errors = [];
                if (simulationPaiements.length === 0) {
                    showMessage("Aucune échéance à enregistrer. Veuillez refaire la simulation.", "error");
                    return;
                }
                simulationPaiements.forEach((p, idx) => {
                    const paiementData = `date_prevu_paiment=${p.date_prevu_paiment}&montant_prevu=${p.montant_prevu}&mensualite=${p.mensualite}&interet=${p.interet}&amortissement=${p.amortissement}&assurance=${p.assurance}&montant_restant=${p.montant_restant}&pret_id=${pret_id}`;
                    ajax("POST", "/paiement-modalites", paiementData, () => {
                        inserted++;
                        if (inserted === simulationPaiements.length) {
                            if (errors.length === 0) {
                                showMessage("✅ Prêt et échéancier enregistrés avec succès !", "success");
                                resetForm();
                                document.getElementById("tableau-amortissement").innerHTML = "";
                                document.getElementById("validation-zone").style.display = "none";
                                simulationPaiements = [];
                            } else {
                                showMessage("Prêt créé mais certaines échéances n'ont pas pu être enregistrées :<br>" + errors.join("<br>"), "error");
                                simulationPaiements = [];
                            }
                        }
                    }, (err) => {
                        inserted++;
                        errors.push("Erreur ligne " + (idx + 1) + " : " + err);
                        if (inserted === simulationPaiements.length) {
                            showMessage("Prêt créé mais certaines échéances n'ont pas pu être enregistrées :<br>" + errors.join("<br>"), "error");
                            simulationPaiements = [];
                        }
                    });
                });
            } else if (response && response.error) {
                showMessage("Erreur lors de la création du prêt : " + response.error, "error");
            } else {
                showMessage("Erreur inconnue lors de la création du prêt.", "error");
            }
        }, (err) => {
            showMessage("Erreur lors de la création du prêt : " + err, "error");
        });
    };

    function showMessage(message, type = 'success') {
        const resultDiv = document.getElementById('resultat-simulation');
        resultDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
        resultDiv.innerHTML = message;
        resultDiv.style.display = 'block';

        // Masquer le message après 7 secondes pour les succès, 15s pour les erreurs
        setTimeout(() => {
            resultDiv.style.display = 'none';
        }, type === 'error' ? 15000 : 7000);
    }

    function chargerTypesPourSelect() {
        ajax("GET", "/type-prets", null, (data) => {
            const select = document.getElementById("type_pret_id");
            select.innerHTML = '<option value="">Sélectionnez un type de prêt</option>';

            if (Array.isArray(data)) {
                data.forEach(type => {
                    const option = document.createElement("option");
                    option.value = type.id;
                    option.setAttribute('data-taux', type.taux);
                    option.setAttribute('data-assurance-par-mois', type.assurance_par_mois);
                    option.setAttribute('data-delai-debut-remboursement', type.delai_debut_remboursement || 0);
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

    function chargerComptesClientsPourSelectSimulation() {
        ajax("GET", "/compte-clients", null, (data) => {
            const select = document.getElementById("compte_client_select");
            select.innerHTML = '<option value="">Sélectionnez un client</option>';
            if (Array.isArray(data)) {
                data.forEach(client => {
                    const label = client.numero + (client.nom ? ' - ' + client.nom : '');
                    const option = document.createElement("option");
                    option.value = client.id;
                    option.textContent = label;
                    select.appendChild(option);
                });
            }
        });
    }

    let simulationPaiements = []; // Stocke les lignes du tableau pour validation

    function simulerPret() {
        const montant = parseFloat(document.getElementById("montant").value);
        const duree = parseInt(document.getElementById("duree_remboursement").value, 10);
        const dateDemande = document.getElementById("date_demande").value;
        const typePretSelect = document.getElementById("type_pret_id");
        const typePretOption = typePretSelect.options[typePretSelect.selectedIndex];
        const tauxAnnuel = parseFloat(typePretOption.getAttribute('data-taux')) || 0;
        const tauxAssurance = parseFloat(document.getElementById("taux_assurance").value) || 0;
        const assuranceParMois = document.getElementById("assurance_par_mois").checked;
        const delaiDebutRemboursement = parseInt(typePretOption.getAttribute('data-delai-debut-remboursement')) || 0;

        if (!montant || !duree || !dateDemande || !tauxAnnuel) {
            document.getElementById("resultat-simulation").innerHTML = "<b>Veuillez remplir tous les champs obligatoires.</b>";
            return;
        }
        console.log("Simulation de prêt avec les paramètres suivants :");
        console.log(`Montant: ${montant}€, Durée: ${duree} mois, Date de demande: ${dateDemande}, Taux annuel: ${tauxAnnuel}%, Taux assurance: ${tauxAssurance}%, Assurance par mois: ${assuranceParMois}, Délai début remboursement: ${delaiDebutRemboursement} mois`);

        // Calcul du taux mensuel
        const tauxMensuel = tauxAnnuel / 12 / 100;

        // Formule de mensualité constante : M = [C * t] / [1 - (1 + t)^-n]
        const mensualite = (montant * tauxMensuel) / (1 - Math.pow(1 + tauxMensuel, -duree));
        let montantRestant = montant;
        let datePaiement = new Date(dateDemande);
        let rows = "";

        // Décaler la première échéance si délai de début de remboursement
        if (delaiDebutRemboursement > 0)
            datePaiement.setMonth(datePaiement.getMonth() + delaiDebutRemboursement);
        else
            datePaiement.setMonth(datePaiement.getMonth() + 1); // Première échéance le mois suivant
        // Calcul de l'assurance
        let assuranceTotal = montant * tauxAssurance / 100;
        let assuranceMensuelle = assuranceParMois ? (assuranceTotal / duree) : 0;

        simulationPaiements = []; // Réinitialise avant chaque simulation
        for (let i = 1; i <= duree; i++) {
            console.log(`Calcul de la mensualité pour le paiement #${i}`);
            // Calcul de l'intérêt et de l'amortissement
            const interet = montantRestant * tauxMensuel;
            const amortissement = mensualite - interet;
            montantRestant = montantRestant - amortissement;
            if (montantRestant < 0) montantRestant = 0;

            // Format date paiement (mois suivant à chaque itération)
            const dateStr = datePaiement.toLocaleDateString('fr-FR');

            let assurance = 0;
            if (assuranceParMois) {
                assurance = assuranceMensuelle;
            } else if (i === 1) {
                assurance = assuranceTotal;
            }

            simulationPaiements.push({
                numero_paiement: i,
                date_prevu_paiment: datePaiement.toISOString().slice(0, 10),
                montant_prevu: (mensualite + assurance),
                mensualite: mensualite,
                interet: interet,
                amortissement: amortissement,
                assurance: assurance,
                montant_restant: montantRestant
            });

            rows += `
        <tr>
          <td>${i}</td>
          <td>${dateStr}</td>
          <td>${(mensualite + assurance).toFixed(2)}</td>
          <td>${mensualite.toFixed(2)}</td>
          <td>${interet.toFixed(2)}</td>
          <td>${amortissement.toFixed(2)}</td>
          <td>${assurance.toFixed(2)}</td>
          <td>${montantRestant.toFixed(2)}</td>
        </tr>
      `;
            // Prochaine échéance
            datePaiement.setMonth(datePaiement.getMonth() + 1);
        }

        document.getElementById("tableau-amortissement").innerHTML = `
      <h2>Tableau d'amortissement</h2>
      <table>
        <thead>
          <tr>
            <th>Numéro paiement</th>
            <th>Date prévu paiement</th>
            <th>Montant prévu</th>
            <th>Monsualite</th>
            <th>Intérêt</th>
            <th>Amortissement</th>
            <th>Assurance</th>
            <th>Montant restant</th>
          </tr>
        </thead>
        <tbody>
          ${rows}
        </tbody>
      </table>
    `;

        // Afficher la zone de validation
        document.getElementById("validation-zone").style.display = "block";
        chargerComptesClientsPourSelectSimulation();
    }

    document.getElementById('valider-simulation').onclick = function () {
        const compte_client_id = document.getElementById("compte_client_select").value;
        if (!compte_client_id) {
            showMessage("Veuillez choisir un compte client.", "error");
            return;
        }
        // Récupérer les infos du formulaire
        const montant = parseFloat(document.getElementById("montant").value);
        const duree = parseInt(document.getElementById("duree_remboursement").value, 10);
        const date_demande = document.getElementById("date_demande").value;
        const type_pret_id = document.getElementById("type_pret_id").value;
        const taux_assurance = document.getElementById("taux_assurance").value || 0;
        const assurance_par_mois = document.getElementById("assurance_par_mois").checked ? 1 : 0;

        // Création du prêt
        const pretData = `duree_remboursement=${duree}&montant=${montant}&date_demande=${date_demande}&type_pret_id=${type_pret_id}&taux_assurance=${taux_assurance}&assurance_par_mois=${assurance_par_mois}&compte_client_id=${compte_client_id}`;
        ajax("POST", "/prets", pretData, (response) => {
            if (response && response.data && (response.data.id || response.data.id === 0)) {
                const pret_id = response.data.id;
                let inserted = 0;
                let errors = [];
                if (simulationPaiements.length === 0) {
                    showMessage("Aucune échéance à enregistrer. Veuillez refaire la simulation.", "error");
                    return;
                }
                simulationPaiements.forEach((p, idx) => {
                    const paiementData = `date_prevu_paiment=${p.date_prevu_paiment}&montant_prevu=${p.montant_prevu}&mensualite=${p.mensualite}&interet=${p.interet}&amortissement=${p.amortissement}&assurance=${p.assurance}&montant_restant=${p.montant_restant}&pret_id=${pret_id}`;
                    ajax("POST", "/paiement-modalites", paiementData, () => {
                        inserted++;
                        if (inserted === simulationPaiements.length) {
                            if (errors.length === 0) {
                                showMessage("✅ Prêt et échéancier enregistrés avec succès !", "success");
                                resetForm();
                                document.getElementById("tableau-amortissement").innerHTML = "";
                                document.getElementById("validation-zone").style.display = "none";
                                simulationPaiements = [];
                            } else {
                                showMessage("Prêt créé mais certaines échéances n'ont pas pu être enregistrées :<br>" + errors.join("<br>"), "error");
                                simulationPaiements = [];
                            }
                        }
                    }, (err) => {
                        inserted++;
                        errors.push("Erreur ligne " + (idx + 1) + " : " + err);
                        if (inserted === simulationPaiements.length) {
                            showMessage("Prêt créé mais certaines échéances n'ont pas pu être enregistrées :<br>" + errors.join("<br>"), "error");
                            simulationPaiements = [];
                        }
                    });
                });
            } else if (response && response.error) {
                showMessage("Erreur lors de la création du prêt : " + response.error, "error");
            } else {
                showMessage("Erreur inconnue lors de la création du prêt.", "error");
            }
        }, (err) => {
            showMessage("Erreur lors de la création du prêt : " + err, "error");
        });
    };

    function resetForm() {
        document.getElementById("simulation-form").reset();
        document.getElementById("resultat-simulation").innerHTML = "";
    }

    // Gestion du formulaire
    document.getElementById('simulation-form').addEventListener('submit', function (e) {
        e.preventDefault();
        simulerPret();
    });

    // Chargement initial
    document.addEventListener('DOMContentLoaded', function () {
        chargerTypesPourSelect();
        // chargerComptesClientsPourSelectSimulation(); // Appelé lors de la simulation
    });
</script>