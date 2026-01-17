<link rel="stylesheet" href="style2.css">
<script src="maiin2.js" defer></script>


<?php
require_once __DIR__ . '/../../Config.php';
session_start();

require_once __DIR__ . '/ProfanityFilter.php';


function stringToColor($str) {
    $Colors = ['#FF6B6B','#FF8E53','#6B5B95','#88B04B','#F7CAC9','#92A8D1','#955251','#B565A7','#DD4124','#D65076'];
    $hash = 0;
    for ($i = 0; $i < strlen($str); $i++) {
        $hash = ord($str[$i]) + (($hash << 5) - $hash);
    }
    return $Colors[abs($hash) % count($Colors)];
}

try {
    $db = Config::getConnexion();
    $currentUserId = $_SESSION['user']['id_user'];

    $stmt = $db->query("
        SELECT m.id, m.message, m.file_path, m.created_at, m.seen_by, m.user_id, u.full_name, u.profile_picture
        FROM chat_messages m
        JOIN user u ON m.user_id = u.id_user
        ORDER BY m.created_at ASC
    ");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $lastAuthorMessageId = null;
    foreach (array_reverse($messages) as $msg) {
        if ($msg['user_id'] == $currentUserId) {
            $lastAuthorMessageId = $msg['id'];
            break;
        }
    }

    foreach ($messages as $msg) {
        $messageId = $msg['id'];
        $authorId = $msg['user_id'];
        $name = htmlspecialchars($msg['full_name']);

        $text = ProfanityFilter::filtrerTexteAvance($msg['message'], ProfanityFilter::getListeBadWords());
        $text = htmlspecialchars($text);
        
        
        $time = date('H:i', strtotime($msg['created_at']));
        $seenBy = !empty($msg['seen_by']) ? explode(',', $msg['seen_by']) : [];


// Seulement les messages reçus, pas encore vus
if ($currentUserId != $authorId && !in_array($currentUserId, $seenBy)) {
    $seenBy[] = $currentUserId;
    $newSeenBy = implode(',', array_unique($seenBy));

    $updateStmt = $db->prepare("UPDATE chat_messages SET seen_by = :seen WHERE id = :id");
    $updateStmt->execute([
        'seen' => $newSeenBy,
        'id' => $messageId
    ]);
}


        $relativePath = 'View/FrontOffice/' . $msg['profile_picture'];
        $absolutePath = realpath(__DIR__ . '/../../' . $relativePath);
        $hasPhoto = !empty($msg['profile_picture']) && $absolutePath && file_exists($absolutePath);

        echo "<div class='chat-line' data-id='{$messageId}' data-time='$time'>";









        // Bouton options (⋮)
        echo "<div class='chat-options'>";



               // 🎧 Bouton Écouter aligné à droite
               if (!empty($text)) {
                echo "<div style='margin-left: auto; display: flex; align-items: center; gap: 8px;'>
                        <button onclick=\"readMessage('" . htmlspecialchars($msg['message'], ENT_QUOTES) . "')\" style='
                            background-color: #8e44ad;
                            color: white;
                            border: none;
                            padding: 5px 10px;
                            border-radius: 6px;
                            cursor: pointer;
                            font-size: 14px;
                        '>🎧</button>
                      </div>";
            }

    



          


        echo "<button class='options-btn' onclick='toggleOptions(this)'>⋮</button>";

 


        echo "<div class='options-menu'>";



        
        if (!empty($text) && empty($msg['file_path'])) {
          echo "<button onclick='editMessage({$msg['id']})'>✏️ Modifier</button>";
      }
      
        echo "<button onclick='deleteMessage($messageId)'>🗑️ Supprimer</button>";

        
        
        echo "</div></div>"; // chat-options



        // Avatar
        if ($hasPhoto) {
            $webPath = '/Projet Web/mvcUtilisateur/' . $relativePath;
            echo "<img src='" . htmlspecialchars($webPath) . "' class='chat-avatar' alt='avatar'>";
        } else {
            $color = stringToColor($name);
            $initial = strtoupper(substr($name, 0, 1));
            echo "<div class='chat-avatar-placeholder' style='background-color: $color;'>$initial</div>";
        }

        // Message
        echo "<div class='chat-message'>";
// Le nom de l'utilisateur
echo "<p><strong>$name</strong></p>";

// Le message texte (très important : class='chat-text')
if (!empty($text)) {
    echo "<p class='chat-text'>" . $text . "</p>";
}


        if (!empty($msg['file_path'])) {
            $ext = strtolower(pathinfo($msg['file_path'], PATHINFO_EXTENSION));
            $fileUrl = '/Projet Web/mvcUtilisateur/' . $msg['file_path'];

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                echo "<img src='" . htmlspecialchars($fileUrl) . "' class='chat-image' alt='Image' />";
            } elseif (in_array($ext, ['webm', 'mp3'])) {
                echo "<audio controls><source src='" . htmlspecialchars($fileUrl) . "' type='audio/mpeg'></audio>";
            } elseif (in_array($ext, ['mp4'])) {
                echo "<video controls style='max-width: 200px; max-height: 150px;'><source src='" . htmlspecialchars($fileUrl) . "' type='video/mp4'></video>";
            } elseif (in_array($ext, ['pdf'])) {
                echo "<a href='" . htmlspecialchars($fileUrl) . "' target='_blank'>📄 Ouvrir PDF</a>";
            } elseif (in_array($ext, ['doc', 'docx'])) {
                echo "<a href='" . htmlspecialchars($fileUrl) . "' target='_blank'>📄 Ouvrir Word</a>";
            } elseif (in_array($ext, ['zip'])) {
                echo "<a href='" . htmlspecialchars($fileUrl) . "' target='_blank'>📦 Télécharger ZIP</a>";
            } elseif (in_array($ext, ['txt'])) {
                echo "<a href='" . htmlspecialchars($fileUrl) . "' target='_blank'>📄 Voir texte</a>";
            } else {
                echo "<a href='" . htmlspecialchars($fileUrl) . "' target='_blank'>📎 Voir fichier</a>";
            }
        }

        echo "</p>";

        // Vu par
        if ($messageId == $lastAuthorMessageId && $authorId == $currentUserId && !empty($seenBy)) {
            $viewers = [];
            foreach ($seenBy as $viewerId) {
                if ($viewerId != $authorId) {
                    $viewerQuery = $db->prepare("SELECT full_name FROM user WHERE id_user = :id");
                    $viewerQuery->execute(['id' => $viewerId]);
                    $viewerNameResult = $viewerQuery->fetch();
                    if ($viewerNameResult) {
                        $viewers[] = htmlspecialchars($viewerNameResult['full_name']);
                    }
                }
            }
            if (!empty($viewers)) {
                $whoSaw = implode(', ', $viewers);
                echo "<div class='seen-indicator'>✔ Vu par $whoSaw</div>";
            }
        }

        echo "</div>"; // .chat-message
        echo "</div>"; // .chat-line
    }

} catch (PDOException $e) {
    echo "Erreur SQL : " . $e->getMessage();
}
?>
