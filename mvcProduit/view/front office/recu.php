<?php
session_start();

// Vérifier si l'utilisateur a accès à cette page
if (!isset($_SESSION['allow_recu'])) {
    header("Location: produit.php");
    exit;
}

// Récupérer les informations du formulaire depuis la session
$commande = isset($_SESSION['derniere_commande']) ? $_SESSION['derniere_commande'] : [];
$formData = $commande['client'] ?? [];
$panier = $commande['produits'] ?? [];
$total = $commande['total'] ?? 0;
$paiement = $commande['paiement'] ?? '';

// Générer un numéro de commande unique
$numeroCommande = strtoupper(substr(uniqid(), -8));

// Vérifier si la promo a été appliquée
$promo_appliquee = isset($_SESSION['promo_appliquee']) && $_SESSION['promo_appliquee'] === true;
$total_reduit = $total;
if ($promo_appliquee) {
    $total_reduit = $total * 0.8;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de commande - Click'N'Go</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .recu-container {
            max-width: 800px;
            margin: 40px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .recu-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: linear-gradient(135deg, #ff8fa3, #c084fc);
            border-radius: 15px;
            color: white;
            position: relative;
        }
        .recu-header h1 {
            color: white;
            margin: 0 0 15px 0;
            font-size: 28px;
            font-weight: 600;
            position: relative;
            display: inline-block;
        }
        .recu-header h1::after {
            content: " ★";
            position: absolute;
            top: -5px;
            right: -25px;
            color: #fff;
            font-size: 20px;
        }
        .recu-header p {
            color: white;
            margin: 5px 0;
            font-size: 16px;
            position: relative;
            display: inline-block;
        }
        .recu-header p::after {
            content: " ★";
            position: absolute;
            top: 0;
            right: -20px;
            color: #fff;
            font-size: 14px;
        }
        .recu-details {
            margin-bottom: 40px;
            background: #fff;
            border-radius: 15px;
        }
        .recu-details h2 {
            color: #2d3436;
            font-size: 22px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            position: relative;
        }
        .recu-details h2:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background: #ff4d4d;
        }
        .recu-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }
        .recu-row:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }
        .recu-row span:first-child {
            font-weight: 600;
            color: #2d3436;
            min-width: 150px;
        }
        .recu-row span:last-child {
            color: #636e72;
            text-align: right;
        }
        .produits-liste {
            margin: 30px 0;
        }
        .produit-item {
            background: #f8f9fa;
            margin-bottom: 15px;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }
        .produit-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .total {
            background: linear-gradient(135deg, #ff8fa3, #c084fc);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0 40px 0;
            width: calc(100% - 50px);
        }
        .total .recu-row {
            border-bottom: none;
            padding: 10px 15px;
            margin: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .total .recu-row span {
            color: white;
            font-size: 20px;
            padding: 0 10px;
        }
        .btn-download {
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(216, 106, 216, 0.3);
        }
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(216, 106, 216, 0.4);
        }
        .btn-download:active {
            transform: translateY(-1px);
        }
        @media (max-width: 768px) {
            .recu-container {
                margin: 20px;
                padding: 20px;
            }
            .recu-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .recu-row span:last-child {
                text-align: left;
                margin-top: 5px;
            }
            .btn-download {
                width: 100%;
                margin: 10px 0;
            }
        }
        .buttons-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        @media print {
            .recu-container {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 15mm;
                box-shadow: none;
            }
            .recu-header {
                margin-bottom: 20mm;
            }
            .recu-details {
                margin-bottom: 20mm;
            }
            .produit-item {
                margin-bottom: 10mm;
            }
            .total {
                margin-top: 20mm;
            }
            .recu-row {
                margin-bottom: 5mm;
                font-size: 12pt;
            }
            .recu-header h1 {
                font-size: 24pt;
                margin-bottom: 10mm;
            }
            .recu-header p {
                font-size: 14pt;
                margin-bottom: 5mm;
            }
        }
    </style>
</head>
<body>
    <div class="recu-container">
        <div id="recu">
            <div class="recu-header">
                <h1>Reçu de commande - Click'N'Go</h1>
                <p><strong>Date:</strong> <?php echo date('d/m/Y'); ?> </p>
                <p><strong>N° de commande:</strong> #<?php echo $numeroCommande; ?> </p>
            </div>
            
            <div class="recu-details" id="details-commande">
                <h2>Détails de la commande</h2>
                <?php if (!empty($formData)): ?>
                <div class="recu-row">
                    <span>Nom:</span>
                    <span><?php echo htmlspecialchars($formData['nom'] ?? ''); ?></span>
                </div>
                <div class="recu-row">
                    <span>Prénom:</span>
                    <span><?php echo htmlspecialchars($formData['prenom'] ?? ''); ?></span>
                </div>
                <div class="recu-row">
                    <span>Numéro de téléphone:</span>
                    <span><?php echo htmlspecialchars($formData['telephone'] ?? ''); ?></span>
                </div>

                <h2>Produits commandés</h2>
                <div class="produits-liste">
                    <?php if (!empty($panier)): ?>
                        <?php foreach ($panier as $produit): ?>
                        <div class="produit-item">
                            <div class="recu-row">
                                <span><?php echo htmlspecialchars($produit['nom']); ?></span>
                                <span>Quantité: <?php echo $produit['quantite']; ?></span>
                            </div>
                            <div class="recu-row">
                                <span>Prix unitaire:</span>
                                <span><?php echo number_format($produit['prix'], 2); ?> TND</span>
                            </div>
                            <div class="recu-row">
                                <span>Sous-total:</span>
                                <span><?php echo number_format($produit['prix'] * $produit['quantite'], 2); ?> TND</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <p>Aucun produit dans le panier</p>
                    <?php endif; ?>
                </div>

                <div class="total">
                    <div class="recu-row">
                        <span>Prix total:</span>
                        <?php if ($promo_appliquee): ?>
                            <span>
                                <span style="text-decoration: line-through; color: #999; margin-right: 10px;">
                                    <?php echo number_format($total, 2); ?> TND
                                </span>
                                <span style="color: #ff4d4d;">
                                    <?php echo number_format($total_reduit, 2); ?> TND
                                </span>
                            </span>
                        <?php else: ?>
                            <span><?php echo number_format($total, 2); ?> TND</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($promo_appliquee): ?>
                        <div class="recu-row">
                            <span>Économie :</span>
                            <span><?php echo number_format($total - $total_reduit, 2); ?> TND</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <p>Aucune information client disponible</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="buttons-container">
            <button class="btn-download" onclick="genererPDF()">
                <i class="fas fa-file-pdf"></i>
                Exporter en PDF
            </button>
            <button class="btn-download" onclick="genererExcel()">
                <i class="fas fa-file-excel"></i>
                Exporter en Excel
            </button>
            <a href="produit.php" class="btn-download">
                <i class="fas fa-store"></i>
                Retour à la boutique
            </a>
        </div>
    </div>

    <script>
        async function genererPDF() {
            const element = document.getElementById('recu');
            
            const canvas = await html2canvas(element, {
                scale: 3,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            });

            const imgData = canvas.toDataURL('image/jpeg', 1.0);
            const { jsPDF } = window.jspdf;
            
            // Créer le PDF au format A4
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });

            // Obtenir les dimensions de la page A4
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();

            // Définir les marges
            const margin = 10;

            // Calculer les dimensions disponibles
            const availableWidth = pageWidth - (2 * margin);
            const availableHeight = pageHeight - (2 * margin);

            // Calculer le ratio en se basant sur la hauteur et la largeur disponibles
            const widthRatio = availableWidth / canvas.width;
            const heightRatio = availableHeight / canvas.height;
            
            // Utiliser le plus petit ratio pour s'assurer que tout le contenu est visible
            const ratio = Math.min(widthRatio, heightRatio);

            // Calculer les dimensions finales
            const targetWidth = canvas.width * ratio;
            const targetHeight = canvas.height * ratio;

            // Centrer horizontalement et placer en haut avec une marge
            const x = (pageWidth - targetWidth) / 2;
            const y = margin;

            // Ajouter l'image au PDF
            pdf.addImage(
                imgData,
                'JPEG',
                x,
                y,
                targetWidth,
                targetHeight,
                undefined,
                'MEDIUM'
            );

            // Sauvegarder le PDF
            pdf.save('recu-commande.pdf');
        }

        function genererExcel() {
            // Créer un objet de données pour Excel
            const data = [
                ['Reçu de commande - Click\'N\'Go'],
                [''],
                ['Numéro de commande:', document.querySelector('.recu-header p').textContent],
                ['Date:', document.querySelector('.recu-row:nth-child(1) span:last-child').textContent],
                [''],
                ['Informations client:'],
                ['Nom:', document.querySelector('.recu-row:nth-child(2) span:last-child').textContent],
                ['Prénom:', document.querySelector('.recu-row:nth-child(3) span:last-child').textContent],
                ['Téléphone:', document.querySelector('.recu-row:nth-child(4) span:last-child').textContent],
                [''],
                ['Détails de la commande:'],
                ['Produit', 'Quantité', 'Prix unitaire', 'Sous-total']
            ];

            // Ajouter les produits
            const produits = document.querySelectorAll('.produit-item');
            produits.forEach(produit => {
                const nom = produit.querySelector('.recu-row:first-child span:first-child').textContent;
                const quantite = produit.querySelector('.recu-row:first-child span:last-child').textContent.split(':')[1].trim();
                const prixUnitaire = produit.querySelector('.recu-row:nth-child(2) span:last-child').textContent;
                const sousTotal = produit.querySelector('.recu-row:nth-child(3) span:last-child').textContent;
                
                data.push([
                    nom,
                    quantite,
                    prixUnitaire,
                    sousTotal
                ]);
            });

            // Ajouter le total
            data.push(['']);
            const totalRow = document.querySelector('.total .recu-row');
            if (totalRow) {
                const totalText = totalRow.querySelector('span:last-child').textContent;
                data.push(['Total:', '', '', totalText]);
            }

            // Créer un workbook
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(data);

            // Ajuster la largeur des colonnes
            ws['!cols'] = [
                {wch: 40}, // Produit
                {wch: 15}, // Quantité
                {wch: 20}, // Prix unitaire
                {wch: 20}  // Sous-total
            ];

            // Ajouter des styles
            const range = XLSX.utils.decode_range(ws['!ref']);
            for (let R = 0; R <= range.e.r; ++R) {
                const cell_address = {c: 0, r: R};
                const cell_ref = XLSX.utils.encode_cell(cell_address);
                if (!ws[cell_ref]) continue;
                
                // Style pour le titre
                if (R === 0) {
                    ws[cell_ref].s = {
                        font: { bold: true, sz: 16 },
                        alignment: { horizontal: 'center' }
                    };
                }
                
                // Style pour les en-têtes
                if (R === 11) {
                    ws[cell_ref].s = {
                        font: { bold: true },
                        fill: { fgColor: { rgb: "E6E6E6" } }
                    };
                }
            }

            // Ajouter la feuille au workbook
            XLSX.utils.book_append_sheet(wb, ws, "Reçu");

            // Générer et télécharger le fichier Excel
            XLSX.writeFile(wb, "recu-commande.xlsx");
        }
    </script>
</body>
</html> 