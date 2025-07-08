<div class="container">
    <div class="page-header">
        <h1>Création de Comptes Clients</h1>
        <p class="page-description">Créez et gérez les comptes clients de la banque</p>
    </div>

    <div class="form-container">
        <h3>Ajouter / Modifier un compte client</h3>
        <form id="compte-client-form">
            <input type="hidden" id="id">
            
            <div class="form-section">
                <h4>Informations du client</h4>
                <div class="form-row">
                    <input type="text" id="nom" placeholder="Nom du client" required>
                    <input type="text" id="prenom" placeholder="Prénom du client" required>
                </div>
                <div class="form-row">
                    <input type="email" id="email" placeholder="Email du client" required>
                    <input type="password" id="mdp" placeholder="Mot de passe">
                </div>
            </div>
            
            <div class="form-section">
                <h4>Informations du compte</h4>
                <div class="form-row">
                    <input type="date" id="date_creation" required>
                    <label for="date_creation">Date de création du compte</label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Créer le compte</button>
                <button type="button" onclick="resetForm()" class="btn btn-secondary">Annuler</button>
            </div>
        </form>
    </div>

    <div id="result" class="alert" style="display: none;"></div>

    <div class="table-container">
        <h3>Liste des comptes clients</h3>
        <table id="table-comptes-clients">
            <thead>
                <tr>
                    <th>N° Compte</th>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Date création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="5">Chargement...</td></tr>
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

    function chargerComptes() {
        ajax("GET", "/compte-clients", null, (data) => {
            const tbody = document.querySelector("#table-comptes-clients tbody");
            tbody.innerHTML = "";
            
            if (!Array.isArray(data)) {
                tbody.innerHTML = '<tr><td colspan="5">Erreur: Format de données invalide</td></tr>';
                return;
            }
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5">Aucun compte client trouvé</td></tr>';
                return;
            }
            
            data.forEach(compte => {
                const tr = document.createElement("tr");
                const dateFormatted = new Date(compte.date_creation).toLocaleDateString('fr-FR');
                
                tr.innerHTML = `
                    <td><strong>${compte.numero}</strong></td>
                    <td>${compte.prenom} <strong>${compte.nom}</strong></td>
                    <td>${compte.email}</td>
                    <td>${dateFormatted}</td>
                    <td>
                        <button class="btn btn-outline btn-sm" onclick='remplirFormulaire(${JSON.stringify(compte)})'>
                            ✏️ Modifier
                        </button>
                        <button class="btn btn-danger btn-sm" onclick='supprimerCompte(${compte.id})'>
                            🗑️ Supprimer
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }, (error) => {
            const tbody = document.querySelector("#table-comptes-clients tbody");
            tbody.innerHTML = `<tr><td colspan="5">Erreur: ${error}</td></tr>`;
        });
    }

    function ajouterOuModifier() {
        const id = document.getElementById("id").value;
        const nom = document.getElementById("nom").value;
        const prenom = document.getElementById("prenom").value;
        const email = document.getElementById("email").value;
        const mdp = document.getElementById("mdp").value;
        const date_creation = document.getElementById("date_creation").value;

        // Validation
        if (!nom || !prenom || !email || !date_creation) {
            showMessage("Veuillez remplir tous les champs obligatoires", 'error');
            return;
        }

        // Pour la création, le mot de passe est obligatoire
        if (!id && !mdp) {
            showMessage("Le mot de passe est obligatoire pour créer un compte", 'error');
            return;
        }

        let data = `nom=${encodeURIComponent(nom)}&prenom=${encodeURIComponent(prenom)}&email=${encodeURIComponent(email)}&date_creation=${date_creation}`;
        
        // Ajouter le mot de passe seulement s'il est fourni
        if (mdp) {
            data += `&mdp=${encodeURIComponent(mdp)}`;
        }

        if (id) {
            ajax("PUT", `/compte-clients/${id}`, data, (response) => {
                showMessage(response.message || 'Compte client modifié avec succès', 'success');
                resetForm();
                chargerComptes();
            }, (error) => {
                showMessage("Erreur lors de la modification: " + error, 'error');
            });
        } else {
            ajax("POST", "/compte-clients", data, (response) => {
                showMessage(response.message || 'Compte client créé avec succès', 'success');
                if (response.data && response.data.numero) {
                    showMessage(`Compte créé avec succès. N° de compte: ${response.data.numero}`, 'success');
                }
                resetForm();
                chargerComptes();
            }, (error) => {
                showMessage("Erreur lors de la création: " + error, 'error');
            });
        }
    }

    function remplirFormulaire(compte) {
        document.getElementById("id").value = compte.id;
        document.getElementById("nom").value = compte.nom;
        document.getElementById("prenom").value = compte.prenom;
        document.getElementById("email").value = compte.email;
        document.getElementById("date_creation").value = compte.date_creation;
        document.getElementById("mdp").value = ''; // Ne pas remplir le mot de passe
        document.getElementById("mdp").placeholder = 'Laisser vide pour conserver le mot de passe actuel';
        
        // Changer le texte du bouton
        document.querySelector('#compte-client-form button[type="submit"]').textContent = 'Modifier le compte';
        
        // Faire défiler vers le formulaire
        document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
    }

    function supprimerCompte(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce compte client ? Cette action supprimera également le client associé.")) {
            ajax("DELETE", `/compte-clients/${id}`, null, (response) => {
                showMessage(response.message || 'Compte client supprimé avec succès', 'success');
                chargerComptes();
            }, (error) => {
                showMessage("Erreur lors de la suppression: " + error, 'error');
            });
        }
    }

    function resetForm() {
        document.getElementById("compte-client-form").reset();
        document.getElementById("id").value = '';
        document.getElementById("mdp").placeholder = 'Mot de passe';
        document.querySelector('#compte-client-form button[type="submit"]').textContent = 'Créer le compte';
    }

    // Gestionnaire du formulaire
    document.getElementById("compte-client-form").addEventListener("submit", function(event) {
        event.preventDefault();
        ajouterOuModifier();
    });

    // Charger les données au chargement de la page
    document.addEventListener("DOMContentLoaded", function() {
        chargerComptes();
        
        // Définir la date par défaut à aujourd'hui
        const today = new Date().toISOString().split('T')[0];
        document.getElementById("date_creation").value = today;
    });
</script>
