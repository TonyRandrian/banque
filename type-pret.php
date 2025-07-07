<div class="container">
    <div class="page-header">
        <h1>Gestion des Types de Prêt</h1>
        <p class="page-description">Configurez les différents types de prêts disponibles avec leurs taux respectifs</p>
    </div>

    <div class="form-container">
        <h3>Ajouter / Modifier un type de prêt</h3>
        <form id="type-pret-form">
            <input type="hidden" id="id">
            <div class="form-row">
                <input type="text" id="libelle" placeholder="Libellé du type de prêt" required>
                <input type="number" id="taux" placeholder="Taux (%)" step="0.01" min="0" max="100" required>
            </div>
            <div class="form-row">
                <input type="number" id="taux_assurance" placeholder="Taux assurance (%)" step="0.01" min="0" max="100">
                <input type="number" id="delai_debut_remboursement" placeholder="Délai début remboursement (mois)" min="0" max="120">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ajouter / Modifier</button>
                <button type="button" onclick="resetForm()" class="btn btn-secondary">Annuler</button>
            </div>
        </form>
    </div>

    <div id="result" class="alert" style="display: none;"></div>

    <div class="table-container">
        <h3>Liste des types de prêts</h3>
        <table id="table-type-prets">
            <thead>
                <tr>
                    <th>Libellé</th>
                    <th>Taux (%)</th>
                    <th>Taux Assurance (%)</th>
                    <th>Délai début remboursement (mois)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="4">Chargement...</td></tr>
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

  function chargerTypePrets() {
    ajax("GET", "/type-prets", null, (data) => {
      const tbody = document.querySelector("#table-type-prets tbody");
      tbody.innerHTML = "";
      
      if (!Array.isArray(data)) {
        tbody.innerHTML = '<tr><td colspan="5">Erreur: Format de données invalide</td></tr>';
        return;
      }
      
      if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5">Aucun type de prêt trouvé</td></tr>';
        return;
      }
      
      data.forEach(typePret => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td><strong>${typePret.libelle}</strong></td>
          <td><span class="badge badge-primary">${parseFloat(typePret.taux).toFixed(2)}%</span></td>
          <td><span class="badge badge-secondary">${parseFloat(typePret.taux_assurance || 0).toFixed(2)}%</span></td>
          <td><span class="badge badge-info">${parseInt(typePret.delai_debut_remboursement || 0)} mois</span></td>
          <td>
            <button class="btn btn-outline btn-sm" onclick='remplirFormulaire(${JSON.stringify(typePret)})'>
              ✏️ Modifier
            </button>
            <button class="btn btn-danger btn-sm" onclick='supprimerTypePret(${typePret.id})'>
              🗑️ Supprimer
            </button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    }, (error) => {
      const tbody = document.querySelector("#table-type-prets tbody");
      tbody.innerHTML = `<tr><td colspan="5">Erreur: ${error}</td></tr>`;
    });
  }

  function ajouterOuModifier() {
    const id = document.getElementById("id").value;
    const libelle = document.getElementById("libelle").value.trim();
    const taux = document.getElementById("taux").value;
    const taux_assurance = document.getElementById("taux_assurance").value || 0;
    const delai_debut_remboursement = document.getElementById("delai_debut_remboursement").value || 0;

    // Validation
    if (!libelle) {
      showMessage("Veuillez saisir un libellé", 'error');
      return;
    }

    if (!taux || parseFloat(taux) <= 0) {
      showMessage("Veuillez saisir un taux valide supérieur à 0", 'error');
      return;
    }

    if (parseFloat(taux) > 100) {
      showMessage("Le taux ne peut pas dépasser 100%", 'error');
      return;
    }

    if (parseFloat(taux_assurance) < 0 || parseFloat(taux_assurance) > 100) {
      showMessage("Le taux d'assurance doit être compris entre 0 et 100%", 'error');
      return;
    }

    if (parseInt(delai_debut_remboursement) < 0 || parseInt(delai_debut_remboursement) > 120) {
      showMessage("Le délai de début de remboursement doit être compris entre 0 et 120 mois", 'error');
      return;
    }

    const data = `libelle=${encodeURIComponent(libelle)}&taux=${taux}&taux_assurance=${taux_assurance}&delai_debut_remboursement=${delai_debut_remboursement}`;

    if (id) {
      ajax("PUT", `/type-prets/${id}`, data, (response) => {
        showMessage(response.message || 'Type de prêt modifié avec succès', 'success');
        resetForm();
        chargerTypePrets();
      }, (error) => {
        showMessage("Erreur lors de la modification: " + error, 'error');
      });
    } else {
      ajax("POST", "/type-prets", data, (response) => {
        showMessage(response.message || 'Type de prêt ajouté avec succès', 'success');
        resetForm();
        chargerTypePrets();
      }, (error) => {
        showMessage("Erreur lors de l'ajout: " + error, 'error');
      });
    }
  }

  function remplirFormulaire(typePret) {
    document.getElementById("id").value = typePret.id;
    document.getElementById("libelle").value = typePret.libelle;
    document.getElementById("taux").value = typePret.taux;
    document.getElementById("taux_assurance").value = typePret.taux_assurance || 0;
    document.getElementById("delai_debut_remboursement").value = typePret.delai_debut_remboursement || 0;
    
    // Faire défiler vers le formulaire
    document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
  }

  function supprimerTypePret(id) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce type de prêt ?")) {
      ajax("DELETE", `/type-prets/${id}`, null, (response) => {
        showMessage(response.message || 'Type de prêt supprimé avec succès', 'success');
        chargerTypePrets();
      }, (error) => {
        showMessage("Erreur lors de la suppression: " + error, 'error');
      });
    }
  }

  function resetForm() {
    document.getElementById("type-pret-form").reset();
    document.getElementById("id").value = "";
    document.getElementById('result').style.display = 'none';
  }

  // Gestion du formulaire
  document.getElementById('type-pret-form').addEventListener('submit', function(e) {
    e.preventDefault();
    ajouterOuModifier();
  });

  // Chargement initial
  document.addEventListener('DOMContentLoaded', function() {
    chargerTypePrets();
  });
</script>
