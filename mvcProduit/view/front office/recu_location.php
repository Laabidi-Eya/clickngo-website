<?php
session_start();

// Vérifier si l'utilisateur a accès à cette page
if (!isset($_SESSION['location_data'])) {
    header("Location: produit.php");
    exit;
}

// Récupérer les informations de location depuis la session
$locationData = $_SESSION['location_data'];

// Générer un numéro de location unique
$numeroLocation = 'LOC-' . strtoupper(substr(uniqid(), -8));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de location - Click'N'Go</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;500&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .recu-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
        }
        
        .recu-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            text-align: center;
        }
        
        .recu-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: #333;
            margin-bottom: 15px;
        }
        
        .recu-header p {
            color: #666;
            font-size: 16px;
            margin: 5px 0;
        }
        
        .recu-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .recu-details h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
        }
        
        .recu-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }
        
        .recu-row:last-child {
            border-bottom: none;
        }
        
        .recu-row:hover {
            background: #fff;
            transform: translateX(5px);
        }
        
        .recu-row span:first-child {
            font-weight: 500;
            color: #333;
            min-width: 200px;
        }
        
        .recu-row span:last-child {
            color: #666;
            text-align: right;
            font-weight: 400;
        }
        
        .buttons-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-download:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 143, 163, 0.3);
        }
        
        .btn-download i {
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .recu-container {
                padding: 20px;
            }
            
            .recu-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .recu-row span:last-child {
                text-align: left;
            }
            
            .buttons-container {
                flex-direction: column;
            }
            
            .btn-download {
                width: 100%;
                justify-content: center;
            }
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .recu-container {
                box-shadow: none;
                margin: 0;
                padding: 20px;
            }
            
            .buttons-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="recu-container">
        <div id="recu">
            <div class="recu-header">
                <h1>Reçu de location - Click'N'Go</h1>
                <p><strong>Date:</strong> <?php echo date('d/m/Y'); ?></p>
                <p><strong>N° de location:</strong> <?php echo $numeroLocation; ?></p>
            </div>
            
            <div class="recu-details">
                <h2>Détails de la location</h2>
                <div class="recu-row">
                    <span><i class="fas fa-user"></i> Nom:</span>
                    <span><?php echo htmlspecialchars($locationData['nom'] ?? ''); ?></span>
                </div>
                <div class="recu-row">
                    <span><i class="fas fa-user"></i> Prénom:</span>
                    <span><?php echo htmlspecialchars($locationData['prenom'] ?? ''); ?></span>
                </div>
                <div class="recu-row">
                    <span><i class="fas fa-box"></i> Produit:</span>
                    <span><?php echo htmlspecialchars($locationData['produit'] ?? ''); ?></span>
                </div>
                <div class="recu-row">
                    <span><i class="fas fa-calendar"></i> Date:</span>
                    <span><?php echo htmlspecialchars($locationData['date_location'] ?? ''); ?></span>
                </div>
                <div class="recu-row">
                    <span><i class="fas fa-clock"></i> Heure de début:</span>
                    <span><?php echo htmlspecialchars($locationData['heure_debut'] ?? ''); ?></span>
                </div>
                <div class="recu-row">
                    <span><i class="fas fa-clock"></i> Heure de fin:</span>
                    <span><?php echo htmlspecialchars($locationData['heure_fin'] ?? ''); ?></span>
                </div>
                <div class="recu-row">
                    <span><i class="fas fa-phone"></i> Téléphone:</span>
                    <span><?php echo htmlspecialchars($locationData['telephone'] ?? ''); ?></span>
                </div>
                <div class="recu-row">
                    <span><i class="fas fa-id-card"></i> Carte d'identité:</span>
                    <span><?php echo htmlspecialchars($locationData['carte_identite'] ?? ''); ?></span>
                </div>
            </div>
        </div>
        
        <div class="buttons-container">
            <button class="btn-download" onclick="genererPDF()">
                <i class="fas fa-download"></i>
                Télécharger en PDF
            </button>
            <a href="produit.php" class="btn-download">
                <i class="fas fa-store"></i>
                Retour à la boutique
            </a>
        </div>
    </div>

    <script>
        function genererPDF() {
            const { jsPDF } = window.jspdf;
            
            html2canvas(document.getElementById('recu')).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const width = pdf.internal.pageSize.getWidth();
                const height = (canvas.height * width) / canvas.width;
                
                pdf.addImage(imgData, 'PNG', 0, 0, width, height);
                pdf.save('recu_location.pdf');
            });
        }
    </script>
</body>
</html> 