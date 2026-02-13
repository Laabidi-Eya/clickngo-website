<?php
// Configuration session propre
if (session_status() === PHP_SESSION_NONE) {
  ini_set('session.cookie_path', '/');
  session_start();
}

// Vérification utilisateur connecté
if (!isset($_SESSION['user']['id_user'])) {
  header('Location: login.php');
  exit();
}

// Récupération infos user
$photoPath = $_SESSION['user']['profile_picture'] ?? '';
$fullName = $_SESSION['user']['full_name'] ?? 'Utilisateur';

$photoRelativePath = '../../' . $photoPath;
$absolutePath = realpath(__DIR__ . '/' . $photoRelativePath);
$showPhoto = !empty($photoPath) && $absolutePath && file_exists($absolutePath);

function stringToColor($str)
{
  $Colors = ['#FF6B6B', '#FF8E53', '#6B5B95', '#88B04B', '#F7CAC9', '#92A8D1', '#955251', '#B565A7', '#DD4124', '#D65076'];
  $hash = 0;
  for ($i = 0; $i < strlen($str); $i++) {
    $hash = ord($str[$i]) + (($hash << 5) - $hash);
  }
  return $Colors[abs($hash) % count($Colors)];
}

require_once(__DIR__ . "/../../config.php");

$currentUserId = $_SESSION['user']['id_user'];

if (isset($_GET['idChatbox'])) {
  $chatboxId = (int)$_GET['idChatbox'];

  $db = Config::getConnexion();

  $stmt = $db->prepare("
        SELECT id, seen_by, user_id
        FROM chat_messages
        WHERE chatbox_id = :chatboxId
        ORDER BY created_at DESC
        LIMIT 1
    ");
  $stmt->execute(['chatboxId' => $chatboxId]);
  $lastMessage = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($lastMessage) {
    $seenByArray = array_filter(explode(',', $lastMessage['seen_by'] ?? ''));

    // 📌 Correction : tjib user_id même si c'est son propre message
    if (!in_array($currentUserId, $seenByArray)) {
      $seenByArray[] = $currentUserId;
      $newSeenBy = implode(',', array_unique($seenByArray));

      $updateStmt = $db->prepare("
                UPDATE chat_messages
                SET seen_by = :seenBy
                WHERE id = :messageId
            ");
      $updateStmt->execute([
        'seenBy' => $newSeenBy,
        'messageId' => $lastMessage['id']
      ]);
    }
  }
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Chatbox Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f2f2f2;
      margin: 0;
      padding: 20px;
    }

    .chat-container {
      max-width: 600px;
      margin: auto;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      padding: 20px;
    }

    #messages {
      height: 400px;
      overflow-y: auto;
      border: 1px solid #ddd;
      padding: 10px;
      background: #fafafa;
      border-radius: 8px;
      margin-bottom: 15px;
    }

    .chat-line {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin-bottom: 12px;
      background: #f2f2f2;
      padding: 10px;
      border-radius: 10px;
      transition: background 0.3s;
      position: relative;
    }

    .chat-line:hover {
      background: #ede6fb;
      cursor: pointer;
    }

    .chat-avatar,
    .chat-avatar-placeholder {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #8e44ad;
    }

    .chat-avatar-placeholder {
      background-color: #ccc;
      color: white;
      font-weight: bold;
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .chat-message {
      flex: 1;
    }

    .chat-message p {
      margin: 0;
      padding: 0;
    }

    #chatForm {
      display: flex;
      gap: 10px;
      align-items: center;
    }

    #message {
      flex: 1;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }

    #sendBtn {
      padding: 0 20px;
      background: #8e44ad;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.2s;
    }

    #sendBtn:hover {
      background: #732d91;
    }

    #emojiPopup {
      position: absolute;
      top: -150px;
      right: 70px;
      background: white;
      border: 1px solid #ccc;
      border-radius: 8px;
      padding: 10px;
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      z-index: 1000;
    }

    #emojiPopup span {
      cursor: pointer;
      font-size: 24px;
    }
  </style>
</head>

<body>
  <a href="indeex.php" style="display: inline-block; margin-bottom: 20px; text-decoration: none;">
    <button type="button" style="
    padding: 10px 20px;
    background-color: #8e44ad;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-size: 16px;
    box-shadow: 0 4px 10px rgba(142, 68, 173, 0.3);
    transition: background-color 0.3s, box-shadow 0.3s, transform 0.2s;
  "
      onmouseover="this.style.backgroundColor='#732d91'; this.style.boxShadow='0 6px 15px rgba(142, 68, 173, 0.5)'; this.style.transform='scale(1.05)';"
      onmouseout="this.style.backgroundColor='#8e44ad'; this.style.boxShadow='0 4px 10px rgba(142, 68, 173, 0.3)'; this.style.transform='scale(1)';">
      ← Retour au Dashboard
    </button>
  </a>


  <!-- ✅ User Profile à mettre où tu veux dans la page -->

  <div class="user-profile" style="position: absolute; top: 20px; right: 20px; z-index: 999;">
    <?php if (isset($_SESSION['user'])): ?>
      <?php
      $photoPath = $_SESSION['user']['profile_picture'] ?? '';
      $fullName = $_SESSION['user']['full_name'] ?? 'Utilisateur';

      $photoRelativePath = '../FrontOffice/' . $photoPath;
      $absolutePath = realpath(__DIR__ . '/' . $photoRelativePath);
      $showPhoto = !empty($photoPath) && $absolutePath && file_exists($absolutePath);
      ?>

      <?php if ($showPhoto): ?>
        <img src="/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>"
          alt="Photo de profil"
          class="profile-photo"
          style="width: 50px; height: 50px; border-radius: 50%; cursor: pointer; object-fit: cover;"
          onclick="toggleDropdown()">
      <?php else: ?>
        <div class="profile-circle"
          style="width: 50px; height: 50px; border-radius: 50%; background-color: <?= stringToColor($fullName) ?>;
               color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; cursor: pointer;"
          onclick="toggleDropdown()">
          <?= strtoupper(substr($fullName, 0, 1)) ?>
        </div>
      <?php endif; ?>

      <!-- Menu déroulant -->
      <div class="dropdown-menu" id="dropdownMenu" style="display: none; position: absolute; right: 0; top: 60px; background: white; border: 1px solid #ccc; border-radius: 6px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); z-index: 1000;">
        <a href="/mvcUtilisateur/View/FrontOffice/profile.php" style="display: block; padding: 10px; text-decoration: none; color: black;">👤 Mon Profil</a>
        <a href="/mvcUtilisateur/View/BackOffice/login/logout.php" style="display: block; padding: 10px; text-decoration: none; color: black;">🚪 Déconnexion</a>
      </div>
    <?php endif; ?>
  </div>

  <style>
    #voiceBtn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 8px 14px;
      background-color: #8e44ad;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    #voiceBtn:hover {
      background-color: #732d91;
      transform: scale(1.05);
    }
  </style>




  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const voiceBtn = document.getElementById('voiceBtn');
      const messageInput = document.getElementById('message');

      if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition; // Utiliser la bonne API selon le navigateur
        const recognition = new SpeechRecognition(); // Créer une instance de reconnaissance vocale
        recognition.lang = 'fr-FR'; // Français
        recognition.continuous = false;
        recognition.interimResults = false;

        let isRecording = false; // ✅ Variable pour savoir si enregistrement actif

        voiceBtn.addEventListener('click', () => {
          if (!isRecording) {
            recognition.start();
            voiceBtn.style.backgroundColor = '#e74c3c'; // 🔴 Enregistrement (rouge)
            voiceBtn.textContent = '🛑 Arrêter';
            isRecording = true;
          } else {
            recognition.stop();
            voiceBtn.style.backgroundColor = '#8e44ad'; // 🟣 Normal
            voiceBtn.textContent = '🖋️ ';
            isRecording = false;
          }
        });

        recognition.onresult = (event) => {
          const transcript = event.results[0][0].transcript;
          messageInput.innerText += ' ' + transcript;
        };

        recognition.onerror = (event) => {
          console.error('Erreur reconnaissance vocale:', event.error);
          // Reset bouton si erreur
          voiceBtn.style.backgroundColor = '#8e44ad';
          voiceBtn.textContent = '🖋️ ';
          isRecording = false;
        };

        recognition.onend = () => {
          if (isRecording) {
            // Si l'utilisateur n'a pas cliqué Stop, continue rien faire
            voiceBtn.style.backgroundColor = '#8e44ad';
            voiceBtn.textContent = '🖋️ ';
            isRecording = false;
          }
        };

      } else {
        voiceBtn.disabled = true;
        alert('Reconnaissance vocale non supportée sur ce navigateur.');
      }
    });





    // Fonction pour ouvrir/fermer le menu
    function toggleDropdown() {
      const menu = document.getElementById('dropdownMenu');
      if (menu.style.display === 'block') {
        menu.style.display = 'none';
      } else {
        menu.style.display = 'block';
      }
    }

    // ✅ Fermer le menu si on clique en dehors
    document.addEventListener('click', function(event) {
      const menu = document.getElementById('dropdownMenu');
      const profile = document.querySelector('.user-profile');
      if (!profile.contains(event.target)) {
        menu.style.display = 'none';
      }
    });
  </script>


  <div class="chat-container">
    <h2 style="text-align: center; color: #8e44ad;">💬 Chatbox Admin</h2>

    <div class="chat-container">
      <button id="clearChatBtn" style="
    background-color: #e74c3c;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 10px;
  ">🗑️ Vider Conversation</button>

      <div id="messages"></div>



      <form id="chatForm" enctype="multipart/form-data" style="margin-top: 10px; display: flex; gap: 8px; align-items: center;">
        <div id="message" contenteditable="true" placeholder="Écris ton message..."
          style="flex: 1; padding: 10px; border-radius: 8px; border: 1px solid #ccc; min-height: 40px; overflow-y: auto;"></div>

        <button type="button" id="fileBtn" style="
  padding: 8px 12px;
  background-color: #8e44ad;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s;
">📎</button>

        <input type="file" id="fileInput" style="display: none;">

        <button type="button" id="emojiBtn" style="
  padding: 8px 12px;
  background-color: #8e44ad;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s;
">😂</button>


        <button type="button" id="voiceBtn" style="
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background-color: #8e44ad;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
">
          🖋️
        </button>


        <button type="button" id="recordBtn" style="background-color: #8e44ad; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer;">🎤</button>

        <style>
          #recordBtn.recording {
            background-color: #e74c3c;
            animation: blink 1s infinite;
          }

          @keyframes blink {
            0% {
              background-color: #e74c3c;
            }

            50% {
              background-color: #c0392b;
            }

            100% {
              background-color: #e74c3c;
            }
          }
        </style>




        <button type="submit" id="sendBtn" style="padding: 8px 20px; background-color: #8e44ad; color: white; border: none; border-radius: 8px;">Envoyer</button>
      </form>



      <div id="preview" style="margin-top: 10px;"></div>
    </div>


    <style>
      /* Animation cercle rouge */
      .recording {
        animation: blink 1s infinite;
      }

      @keyframes blink {
        0% {
          background-color: #e74c3c;
        }

        50% {
          background-color: #c0392b;
        }

        100% {
          background-color: #e74c3c;
        }
      }

      /* Chrono Style */
      #timer {
        margin-top: 10px;
        font-weight: bold;
        color: red;
        font-size: 16px;
      }
    </style>


    <script>
      let recorder;
      let audioChunks;
      let recordingStatus;
      let timerInterval;
      let seconds = 0;

      document.getElementById('recordBtn').addEventListener('click', async function() {
        // Récupérer les éléments HTML nécessaires
        const input = document.getElementById('message');
        const fileInput = document.getElementById('fileInput');
        const recordBtn = document.getElementById('recordBtn');

        // Si l'enregistreur n'existe pas encore, démarrer un nouvel enregistrement
        if (!recorder) {
          try {
            // Accès au microphone utilisateur
            const stream = await navigator.mediaDevices.getUserMedia({
              audio: true
            });
            recorder = new MediaRecorder(stream); //api javascript pour enregistrer le son
            audioChunks = []; // Tableau pour stocker les données audio

            // Événement lorsque des données audio sont disponibles
            recorder.addEventListener('dataavailable', event => {
              audioChunks.push(event.data); // Ajouter les données audio au tableau
            });

            // Événement lorsque l'enregistrement s'arrête
            recorder.addEventListener('stop', () => {
              clearInterval(timerInterval); // Arrêter le chronomètre
              resetTimerAndUI(); // Réinitialiser l'interface

              // Création d'un fichier audio à partir des données enregistrées
              const audioBlob = new Blob(audioChunks, {
                type: 'audio/webm'
              });
              const audioURL = URL.createObjectURL(audioBlob); // URL pour lire le fichier audio

              // Nettoyer le champ d'entrée pour afficher l'audio enregistré
              input.innerHTML = '';

              // Conteneur pour le lecteur audio et bouton de suppression
              const container = document.createElement('div');
              container.style.display = 'flex';
              container.style.alignItems = 'center';
              container.style.gap = '10px';
              container.style.marginTop = '10px';

              // Lecteur audio
              const audio = document.createElement('audio');
              audio.controls = true;
              audio.src = audioURL;
              audio.style.maxWidth = '250px';

              // Bouton pour supprimer l'audio enregistré
              const deleteBtn = document.createElement('span');
              deleteBtn.textContent = '❌';
              deleteBtn.style.cursor = 'pointer';
              deleteBtn.style.color = 'red';
              deleteBtn.style.fontSize = '20px';
              deleteBtn.onclick = () => {
                input.innerHTML = '';
                fileInput.value = '';
              };

              // Ajouter l'audio et le bouton au conteneur puis à l'entrée
              container.appendChild(audio);
              container.appendChild(deleteBtn);
              input.appendChild(container);

              // Préparer l'audio enregistré comme fichier pour envoi éventuel
              fileInput.files = createFileList(audioBlob, 'voice_message.webm');
            });

            // Commencer l'enregistrement
            recorder.start();

            // Mettre à jour l'interface pour indiquer l'état d'enregistrement
            recordBtn.classList.add('recording');

            recordingStatus = document.createElement('div');
            recordingStatus.id = 'recording-status';
            recordingStatus.innerText = '🎤 Enregistrement en cours...';
            recordingStatus.style.color = 'red';
            recordingStatus.style.fontWeight = 'bold';
            recordingStatus.style.marginTop = '10px';
            document.querySelector('.chat-container').appendChild(recordingStatus);

            // Afficher un chronomètre
            const timer = document.createElement('div');
            timer.id = 'timer';
            timer.style.marginTop = '5px';
            timer.style.color = 'red';
            timer.style.fontSize = '16px';
            timer.style.fontWeight = 'bold';
            document.querySelector('.chat-container').appendChild(timer);

            // Mise à jour du chronomètre chaque seconde
            timerInterval = setInterval(() => {
              seconds++;
              const min = Math.floor(seconds / 60);
              const sec = seconds % 60;
              timer.textContent = `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
            }, 1000);

          } catch (err) {
            console.error('Erreur accès microphone:', err); // Gestion des erreurs d'accès au microphone
          }
        } else {
          // Si l'enregistrement est en cours, arrêter
          recorder.stop();
          recorder = null;
          resetTimerAndUI(); // Réinitialiser l'interface
        }
      });

      function createFileList(fileBlob, fileName) {
        const dataTransfer = new DataTransfer();
        const file = new File([fileBlob], fileName, {
          type: 'audio/webm'
        });
        dataTransfer.items.add(file);
        return dataTransfer.files;
      }

      // 🛠 Fonction utilitaire pour reset chrono + design bouton
      function resetTimerAndUI() {
        const recordBtn = document.getElementById('recordBtn');

        clearInterval(timerInterval);
        seconds = 0;

        if (recordingStatus) recordingStatus.remove();
        if (document.getElementById('timer')) document.getElementById('timer').remove();

        recordBtn.classList.remove('recording');
      }




      document.addEventListener('DOMContentLoaded', function() {



        const clearBtn = document.getElementById('clearChatBtn');
        if (clearBtn) {
          clearBtn.addEventListener('click', function() {
            if (confirm('Es-tu sûr de vouloir supprimer toute la conversation ?')) {
              fetch('clear_conversation.php', {
                  method: 'POST'
                })
                .then(response => response.text())
                .then(data => {
                  console.log(data);
                  alert('✅ Conversation vidée !');
                  loadMessages(); // Recharger les messages après suppression
                })
                .catch(error => console.error('Erreur suppression:', error));
            }
          });
        }



        const form = document.getElementById('chatForm');
        const input = document.getElementById('message');
        const fileInput = document.getElementById('fileInput');
        const fileBtn = document.getElementById('fileBtn');
        const emojiBtn = document.getElementById('emojiBtn');
        const messages = document.getElementById('messages');
        let emojiPopup = null;

        form.addEventListener('submit', function(e) {
          e.preventDefault();

          const formData = new FormData();
          let finalMessage = input.innerText.trim(); // ⬅️ فقط texte (مش innerHTML)

          // si fichier sélectionné ➔ vider le texte
          if (fileInput.files.length > 0) {
            finalMessage = '';
            formData.append('file', fileInput.files[0]);
          }

          formData.append('message', finalMessage);

          fetch('send_message.php', {
              method: 'POST',
              body: formData
            })
            .then(response => response.text())
            .then(data => {
              console.log(data);
              input.innerHTML = '';
              fileInput.value = '';
              loadMessages();
            })
            .catch(error => console.error('Erreur envoi :', error));
        });

        fileBtn.addEventListener('click', function(e) {
          e.preventDefault();
          fileInput.click();
        });

        // Quand fichier sélectionné
        fileInput.addEventListener('change', function() {
          input.innerHTML = ''; // Vider champ input avant ajout

          if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const fileType = file.type;

            if (fileType.startsWith('image/')) {
              const reader = new FileReader();
              reader.onload = function(e) {
                const container = document.createElement('div');
                container.style.display = 'flex';
                container.style.alignItems = 'center';
                container.style.gap = '10px';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '100px';
                img.style.maxHeight = '100px';
                img.style.borderRadius = '8px';
                img.style.margin = '5px';

                const deleteBtn = createDeleteButton();

                container.appendChild(img);
                container.appendChild(deleteBtn);
                input.appendChild(container);
              };
              reader.readAsDataURL(file);
            } else {
              let icon = '📎'; // Default
              if (fileType.includes('pdf')) icon = '📄';
              else if (fileType.includes('audio')) icon = '🎵';
              else if (fileType.includes('video')) icon = '🎬';
              else if (fileType.includes('zip') || fileType.includes('compressed')) icon = '📦';
              else if (fileType.includes('text')) icon = '📃';
              else if (fileType.includes('word') || fileType.includes('msword') || fileType.includes('officedocument')) icon = '📝';

              const container = document.createElement('div');
              container.style.display = 'flex';
              container.style.alignItems = 'center';
              container.style.gap = '10px';

              const fileName = document.createElement('span');
              fileName.textContent = `${icon} ${file.name}`;
              fileName.style.display = 'inline-block';
              fileName.style.marginTop = '5px';
              fileName.style.fontSize = '14px';
              fileName.style.color = '#555';

              const deleteBtn = createDeleteButton();

              container.appendChild(fileName);
              container.appendChild(deleteBtn);
              input.appendChild(container);
            }
          }
        });

        function createDeleteButton() {
          const deleteBtn = document.createElement('span');
          deleteBtn.textContent = '❌';
          deleteBtn.style.cursor = 'pointer';
          deleteBtn.style.color = 'red';
          deleteBtn.style.fontSize = '18px';
          deleteBtn.style.fontWeight = 'bold';

          deleteBtn.onclick = () => {
            input.innerHTML = ''; // 🚀 Tnahi kol fichier
            fileInput.value = ''; // 🚀 Reset le file input pour permettre sélection
          };

          return deleteBtn;
        }


        // Bouton 😂 Emoji
        emojiBtn.addEventListener('click', function(e) { 
          e.preventDefault();  // Empêcher le comportement par défaut du bouton
          if (emojiPopup) {
            emojiPopup.remove(); // Si le popup existe déjà, le supprimer
            emojiPopup = null; // Réinitialiser la variable
            return;
          }
          showEmojiPopup();
        });

        function showEmojiPopup() {
          const popup = document.createElement('div'); // Créer le popup
          popup.id = 'emojiPopup'; // ID pour le style
          popup.style.position = 'absolute'; // Position absolue pour le popup
          popup.style.top = (emojiBtn.getBoundingClientRect().top + window.scrollY - 110) + 'px';
          popup.style.left = (emojiBtn.getBoundingClientRect().left + window.scrollX - 20) + 'px';
          popup.style.background = 'white';
          popup.style.border = '1px solid #ccc';
          popup.style.borderRadius = '10px';
          popup.style.padding = '12px';
          popup.style.display = 'flex';
          popup.style.flexWrap = 'wrap';
          popup.style.gap = '10px';
          popup.style.zIndex = '9999';
          popup.style.boxShadow = '0 4px 10px rgba(0,0,0,0.2)';

          const emojis = ['😂', '😍', '🔥', '😎', '🥲', '🎉', '❤️', '👍', '🤩', '🙌', '💯', '🤔', '👏', '😭', '😊', '🤗']; 
          emojis.forEach(e => {
            const span = document.createElement('span'); // Créer un élément span pour chaque emoji
            span.textContent = e; // Ajouter l'emoji au span
            span.style.cursor = 'pointer';
            span.style.fontSize = '22px';
            span.addEventListener('click', () => {
              input.innerHTML += e; // ✅ Ajouter emoji au contenteditable
              popup.remove();
            });
            popup.appendChild(span);
          });

          document.body.appendChild(popup);
          emojiPopup = popup;
        }

        // Charger les messages
        function loadMessages() {
          fetch('load_messages.php')
            .then(response => response.text())
            .then(html => {
              const messages = document.getElementById('messages');
              if (html.trim() === '') {
                messages.innerHTML = "<div style='text-align:center; margin-top:20px; color:#aaa;'>📭 Aucun message pour le moment</div>";
              } else {
                messages.innerHTML = html;
              }
              messages.scrollTop = messages.scrollHeight;
              activateClickOnMessages();
            })
            .catch(error => console.error('Erreur chargement messages:', error));
        }


        loadMessages();
        //setInterval(loadMessages, 3000);
      });

      function deleteMessage(messageId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
          // Supprimer directement du DOM d'abord
          const messageLine = document.querySelector(`[data-id='${messageId}']`);
          if (messageLine) {
            messageLine.remove();
          }

          // Ensuite envoyer requête serveur sans attendre résultat
          fetch('delete_message.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                id: messageId
              })
            })
            .catch(error => console.error('Erreur suppression:', error));
        }
        location.reload();
      }


      function readMessage(text) {
  const synth = window.speechSynthesis;
  if (!synth) {
    alert('Synthèse vocale non supportée sur ce navigateur.');
    return;
  }

  // 🧼 Supprimer les mots censurés ("****", "***", etc.)
  const cleanedText = text
    .split(' ')
    .filter(word => !/^\*+$/g.test(word)) // Ignore les mots comme "****"
    .join(' ');

  const utterance = new SpeechSynthesisUtterance(cleanedText);
  utterance.lang = 'fr-FR'; // tu peux changer ça dynamiquement si besoin
  synth.speak(utterance);
}

    </script>
</body>

</html>


<script>
  // Définir la fonction toggleOptions tout en haut (globale)
  function toggleOptions(button) {
    const menu = button.nextElementSibling;

    // Fermer tous les autres menus
    document.querySelectorAll('.options-menu').forEach(m => {
      if (m !== menu) m.style.display = 'none';
    });

    // Toggle ouvrir/fermer ce menu
    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
  }

  // Puis DOMContentLoaded pour le reste du comportement
  document.addEventListener('DOMContentLoaded', function() {

    // Fermer menus si clic dehors
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.chat-options')) {
        document.querySelectorAll('.options-menu').forEach(m => m.style.display = 'none');
      }
    });

    // Clic sur messages pour afficher l'heure
    function activateClickOnMessages() {
      document.querySelectorAll('.chat-line').forEach(line => {
        line.addEventListener('click', function(e) {
          // Ne pas déclencher si clic sur les options
          if (e.target.closest('.chat-options') || e.target.classList.contains('options-btn') || e.target.closest('.options-menu')) {
            return;
          }

          const existing = this.querySelector('.chat-time');
          if (existing) {
            existing.remove();
          } else {
            const time = this.getAttribute('data-time');
            const timeSpan = document.createElement('span');
            timeSpan.className = 'chat-time';
            timeSpan.textContent = time;
            timeSpan.style.display = 'block';
            timeSpan.style.fontSize = '12px';
            timeSpan.style.color = '#999';
            timeSpan.style.marginTop = '5px';
            this.querySelector('.chat-message').appendChild(timeSpan);
          }
        });
      });
    }

    // Charger les messages
    function loadMessages() {
      fetch('load_messages.php')
        .then(response => response.text())
        .then(html => {
          const messages = document.getElementById('messages');
          messages.innerHTML = html;
          messages.scrollTop = messages.scrollHeight;
          activateClickOnMessages(); // 🛠
        });
    }

    // Initialisation
    loadMessages();
    //setInterval(loadMessages, 3000);

  });
</script>

<script>
  // Définir la fonction toggleOptions tout en haut (globale)
  function toggleOptions(button) {
    const menu = button.nextElementSibling;

    // Fermer tous les autres menus
    document.querySelectorAll('.options-menu').forEach(m => {
      if (m !== menu) m.style.display = 'none';
    });

    // Toggle ouvrir/fermer ce menu
    menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
  }

  // Puis DOMContentLoaded pour le reste du comportement
  document.addEventListener('DOMContentLoaded', function() {

    // Fermer menus si clic dehors
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.chat-options')) {
        document.querySelectorAll('.options-menu').forEach(m => m.style.display = 'none');
      }
    });


    // Clic sur messages pour afficher l'heure
    function activateClickOnMessages() {
      document.querySelectorAll('.chat-line').forEach(line => {
        line.addEventListener('click', function(e) {
          // Ne pas déclencher si clic sur les options
          if (e.target.closest('.chat-options') || e.target.classList.contains('options-btn') || e.target.closest('.options-menu')) {
            return;
          }

          const existing = this.querySelector('.chat-time');
          if (existing) {
            existing.remove();
          } else {
            const time = this.getAttribute('data-time');
            const timeSpan = document.createElement('span');
            timeSpan.className = 'chat-time';
            timeSpan.textContent = time;
            timeSpan.style.display = 'block';
            timeSpan.style.fontSize = '12px';
            timeSpan.style.color = '#999';
            timeSpan.style.marginTop = '5px';
            this.querySelector('.chat-message').appendChild(timeSpan);
          }
        });
      });
    }

    // Charger les messages
    function loadMessages() {
      fetch('load_messages.php')
        .then(response => response.text())
        .then(html => {
          const messages = document.getElementById('messages');
          messages.innerHTML = html;
          messages.scrollTop = messages.scrollHeight;
          activateClickOnMessages(); // 🛠
        });
    }

    // Initialisation
    loadMessages();
    //setInterval(loadMessages, 3000);

  });




  function saveEditedMessage(messageId) {
  const messageLine = document.querySelector(`[data-id='${messageId}']`);
  const input = messageLine.querySelector('.edit-input');
  const newText = input.value.trim();

  if (newText !== "") {
    fetch('update_message.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id: messageId,
          text: newText
        })
      })
      .then(response => response.text())
      .then(() => {
        // Recharge toute la page juste après la mise à jour
        window.location.reload(); 
      })
      .catch(error => console.error('Erreur modification:', error));
  }
}


  function editMessage(messageId) {
    const messageLine = document.querySelector(`[data-id='${messageId}']`);
    if (!messageLine) {
      console.error('Pas trouvé la ligne du message', messageId);
      return;
    }
    const chatText = messageLine.querySelector('.chat-text');
    if (!chatText) {
      console.error('Pas trouvé le texte du message', messageId);
      return;
    }

    const originalText = chatText.innerText;

    chatText.innerHTML = `
    <input type="text" value="${originalText}" class="edit-input" id="edit-input-${messageId}" />
    <button onclick="saveEditedMessage(${messageId})" class="save-btn">✅</button>
    <button onclick="cancelEdit(${messageId}, \`${originalText}\`)" class="cancel-btn">❌</button>
  `;

    const input = document.getElementById(`edit-input-${messageId}`);
    if (input) {
      input.focus();
      input.setSelectionRange(input.value.length, input.value.length);
    }
  }

  function cancelEdit(messageId, originalText) {
    const messageLine = document.querySelector(`[data-id='${messageId}']`);
    const chatText = messageLine.querySelector('.chat-text') || messageLine.querySelector('.edit-input').parentElement;

    if (chatText) {
      chatText.innerHTML = `
      <p class="chat-text">${originalText}</p>
    `;
    }
  }
</script>