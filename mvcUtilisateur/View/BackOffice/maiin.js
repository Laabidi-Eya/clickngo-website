// === Scroll activités ===
function scrollActivities(direction) {
  const container = document.querySelector('.activites');
  const scrollAmount = 220;
  if (direction === 'next') {
      container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
  } else if (direction === 'prev') {
      container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
  }
}

// === Dropdown menu ===
function toggleDropdown() {
  const menu = document.getElementById('dropdownMenu');
  menu.style.display = (menu.style.display === 'flex') ? 'none' : 'flex';
}

window.addEventListener('click', function (e) {
  const profile = document.querySelector('.user-profile');
  if (profile && !profile.contains(e.target)) {
      const menu = document.getElementById('dropdownMenu');
      if (menu) menu.style.display = 'none';
  }
});

function unbanUser(id) {
  Swal.fire({
    title: 'Confirmer le débannissement ?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Oui, débannir',
    cancelButtonText: 'Annuler',
    confirmButtonColor: '#28a745'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = `indeex.php?action=debannirUser&id=${id}`;
    }
  });
}


function deleteUser(id, name) {
  Swal.fire({
    title: '❌ Supprimer ce profil ?',
    html: `Voulez-vous vraiment supprimer <strong>${name}</strong> (ID: ${id}) ?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Oui, supprimer',
    cancelButtonText: 'Annuler',
    confirmButtonColor: '#d33',
    cancelButtonColor: '#aaa'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('indeex.php?action=supprimerUser', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `id=${id}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const row = document.getElementById(`user-row-${id}`);
          if (row) {
            row.style.transition = 'opacity 0.5s';
            row.style.opacity = 0;
            setTimeout(() => row.remove(), 500);
          }
          Swal.fire({
            icon: 'success',
            title: 'Utilisateur supprimé ✅',
            text: `Le compte a été supprimé.`,
            confirmButtonColor: '#6c63ff'
          });
        } else {
          Swal.fire('Erreur', 'La suppression a échoué.', 'error');
        }
      });
    }
  });
}


function confirmDelete(formElement) {
  Swal.fire({
    title: '❌ Supprimer ce profil ?',
    text: "Cette action est irréversible.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Oui, supprimer',
    cancelButtonText: 'Annuler',
    confirmButtonColor: '#6c63ff',
    cancelButtonColor: '#aaa'
  }).then((result) => {
    if (result.isConfirmed) {
      const action = formElement.querySelector('input[name="action"]').value;
      const id = formElement.querySelector('input[name="id"]').value;
      window.location.href = `indeex.php?action=${action}&id=${id}&deleted=1`;
    }
  });
  return false;
}

// === Changer Role ===
function changeRole(idUser, currentRole) {
  const roleOptions = ['admin', 'user'];
  let optionsHtml = roleOptions.map(role =>
    `<option value="${role}" ${role === currentRole ? 'selected' : ''}>${role}</option>`
  ).join('');

  const newRole = promptifySelect(`Changer le rôle de l'utilisateur`, optionsHtml);

  newRole.then(selectedRole => {
      if (selectedRole && selectedRole !== currentRole) {
          window.location.href = `indeex.php?action=changerRole&id=${idUser}&role=${selectedRole}`;
      }
  });
}

function promptifySelect(title, optionsHtml) {
  return new Promise(resolve => {
      const modal = document.createElement('div');
      modal.innerHTML = `
          <div class="modal-backdrop">
            <div class="modal-box">
              <h3>${title}</h3>
              <select id="role-select">${optionsHtml}</select>
              <div class="modal-actions">
                <button onclick="this.closest('.modal-backdrop').remove()">Annuler</button>
                <button onclick="confirmSelect(this)">Confirmer</button>
              </div>
            </div>
          </div>`;
      document.body.appendChild(modal);
      window.confirmSelect = (btn) => {
          const selected = btn.closest('.modal-box').querySelector('#role-select').value;
          btn.closest('.modal-backdrop').remove();
          resolve(selected);
      };
  });
}

// === Bannir utilisateur ===
function banUser(idUser) {
  const modal = document.createElement('div');
  modal.innerHTML = `
    <div class="modal-backdrop">
      <div class="modal-box">
        <h3>Raison du bannissement</h3>
        <textarea id="ban-reason" placeholder="Entrez la raison ici..."></textarea>
        <div class="modal-actions">
          <button onclick="this.closest('.modal-backdrop').remove()">Annuler</button>
          <button onclick="confirmBan(this, ${idUser})">Confirmer</button>
        </div>
      </div>
    </div>`;
  document.body.appendChild(modal);
}

function confirmBan(btn, idUser) {
  const reason = btn.closest('.modal-box').querySelector('#ban-reason').value.trim();
  if (reason) {
      window.location.href = `indeex.php?action=bannirUser&id=${idUser}&raison=${encodeURIComponent(reason)}`;
  } else {
      alert("Vous devez entrer une raison.");
  }
}

// === Graphique inscriptions ===
window.addEventListener('load', () => {
  const chartElement = document.getElementById('inscriptionChart');
  if (chartElement) {
      const ctx = chartElement.getContext('2d');
      new Chart(ctx, {
          type: 'bar',
          data: {
              labels: inscriptionLabels,
              datasets: [{
                  label: 'Inscriptions par Mois',
                  data: inscriptionData,
                  backgroundColor: 'rgba(54, 162, 235, 0.5)',
                  borderColor: 'rgba(54, 162, 235, 1)',
                  borderWidth: 1
              }]
          },
          options: {
              responsive: true,
              scales: {
                  y: { beginAtZero: true }
              },
              plugins: { legend: { display: false } }
          }
      });

      const listContainer = document.getElementById('inscriptionList');
      inscriptionLabels.forEach((label, index) => {
          const li = document.createElement('li');
          li.textContent = `${label} : ${inscriptionData[index]} inscrit(s)`;
          listContainer.appendChild(li);
      });
  }
});

// === Chatbox - envoyer message ou fichier ===
const form = document.getElementById('chatForm');
const messageInput = document.getElementById('message');
const fileInput = document.getElementById('fileInput');
const messages = document.getElementById('messages');

form.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append('message', messageInput.value);

    if (fileInput.files.length > 0) {
        formData.append('file', fileInput.files[0]);
    }

    fetch('send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);
        messageInput.value = '';
        fileInput.value = '';
        loadMessages();
    })
    .catch(error => console.error('Erreur:', error));
});

function loadMessages() {
    fetch('load_messages.php')
    .then(response => response.text())
    .then(html => {
        messages.innerHTML = html;
        messages.scrollTop = messages.scrollHeight;
    });
}









// 🔁 Activer la section "youtube" comme les autres
document.getElementById('youtubeSectionBtn').addEventListener('click', () => {
  // Retire "active" de toutes les sections
  document.querySelectorAll('.dashboard-section').forEach(section => {
    section.classList.remove('active');
  });

  // Active la section YouTube
  const youtubeSection = document.getElementById('youtube');
  if (youtubeSection) {
    youtubeSection.classList.add('active');
  }

  // Affiche mini lecteur vidéo
  startVideo("https://www.youtube.com/embed/YbJOTdZBX1g"); // ← Remplace par ta vidéo
});
