// server.js
const express = require('express');
const bodyParser = require('body-parser');
const path = require('path');
const app = express();
const PORT = 3000;

// Middleware
app.use(bodyParser.json());
app.use(express.static(path.join(__dirname, 'public')));

// Serve the HTML file
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Handle chat API requests
app.post('/chat', (req, res) => {
  const message = req.body.message;
  console.log('Received message:', message);
  
  // Simple mock response based on user message
  let response;
  const lowerMsg = message.toLowerCase();
  
  if (lowerMsg.includes('fonctionne') || lowerMsg.includes('comment')) {
    response = "ClickNGo fonctionne de façon simple! Tu t'inscris, tu cherches un trajet qui te convient, et tu réserves en quelques clics. Tu peux aussi proposer tes propres trajets.";
  } else if (lowerMsg.includes('avantage')) {
    response = "Le covoiturage avec ClickNGo offre plusieurs avantages: économies d'argent, réduction de l'empreinte carbone, moins de trafic, et l'opportunité de faire de nouvelles rencontres!";
  } else if (lowerMsg.includes('réserv') || lowerMsg.includes('trajet')) {
    response = "Pour réserver un trajet, connecte-toi à ton compte, utilise la barre de recherche pour trouver ton itinéraire, choisis le trajet qui te convient et confirme ta réservation en effectuant le paiement.";
  } else if (lowerMsg.includes('prix') || lowerMsg.includes('coût') || lowerMsg.includes('tarif')) {
    response = "Les prix sur ClickNGo sont fixés par les conducteurs, mais nous recommandons des tarifs équitables basés sur la distance et les frais de carburant. En moyenne, c'est 30-50% moins cher que d'autres moyens de transport!";
  } else if (lowerMsg.includes('bonjour') || lowerMsg.includes('salut') || lowerMsg.includes('hello')) {
    response = "Bonjour! Comment puis-je t'aider avec tes besoins de covoiturage aujourd'hui?";
  } else {
    response = "Merci pour ta question. Pour plus d'informations sur le covoiturage avec ClickNGo, n'hésite pas à me demander sur nos services, la réservation, ou les avantages du covoiturage!";
  }
  
  // Send response with a slight delay to simulate processing
  setTimeout(() => {
    res.json({ response });
  }, 500);
});

// Start server
app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});