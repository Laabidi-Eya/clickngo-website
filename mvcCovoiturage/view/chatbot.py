from flask import Flask, request, jsonify, render_template
import ollama
import re
import logging
import os

app = Flask(__name__)

# Configure logging
logging.basicConfig(level=logging.INFO)
app.logger.setLevel(logging.INFO)

# Set this to True to use test mode (no Ollama required)
TEST_MODE = os.environ.get('TEST_MODE', 'False').lower() in ('true', '1', 't')

# List of keywords related to covoiturage/carpooling
COVOITURAGE_KEYWORDS = [
    'covoiturage', 'carpooling', 'ridesharing', 'ride sharing', 'ride-sharing',
    'car sharing', 'car-sharing', 'carsharing', 'shared ride', 'shared rides',
    'blablacar', 'uber pool', 'lyft shared', 'klaxit', 'karos', 'clickngo',
    'passenger', 'driver', 'commute', 'commuting', 'carpool',
    'shared mobility', 'sustainable transport', 'cost sharing',
    'trajet partagé', 'partage de trajet', 'partage de voiture',
    'conducteur', 'passager', 'trajet', 'voyager ensemble',
    'économie de trajet', 'réserver', 'réservation'
]

def is_about_covoiturage(text):
    """Check if the message is about covoiturage/carpooling"""
    text_lower = text.lower()
    
    # Add common French covoiturage questions
    direct_questions = [
        "c'est quoi", "c est quoi", "qu'est-ce que", "qu est ce que",
        "comment fonctionne", "comment marche", "comment utiliser",
        "avantages", "bénéfices", "prix", "tarif", "cout", "coût"
    ]
    
    # Check for direct covoiturage questions
    for question in direct_questions:
        if question in text_lower and ("covoiturage" in text_lower or "clickngo" in text_lower):
            return True
    
    # Check for direct keyword matches
    for keyword in COVOITURAGE_KEYWORDS:
        if keyword.lower() in text_lower:
            return True
    
    # For very short queries about covoiturage, also return true
    if len(text_lower.split()) <= 5 and ("covoiturage" in text_lower or "clickngo" in text_lower):
        return True
    
    # Return False if no carpooling-related keywords found
    return False

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/chat', methods=['POST'])
def chat():
    data = request.get_json()
    user_message = data.get('message')
    if not user_message:
        return jsonify({'response': 'Please provide a message.'}), 400
    
    try:
        # Check if the message is about covoiturage
        if is_about_covoiturage(user_message):
            # Create a system message to constrain the model's responses
            system_message = """You are a specialized assistant that only answers questions related to 
            carpooling/ridesharing (covoiturage) for the ClickNGo service. Provide helpful, accurate information about topics like:
            - How carpooling works with ClickNGo
            - Benefits of carpooling (environmental, economic, social)
            - How to use the ClickNGo platform for carpooling
            - Safety tips for carpooling with ClickNGo
            - Carpooling etiquette and best practices
            - Legislation and policies related to carpooling
            - Statistics about carpooling usage
            - And other carpooling/ridesharing related topics
            
            End each of your responses with a variation of this French call-to-action:
            "Tu peux réserver chez nous ClickNGo pour ton prochain covoiturage!" or similar encouraging phrases about booking through ClickNGo.
            
            If a question is not related to carpooling/ridesharing, politely explain that you can only 
            provide information about carpooling topics, and still include the ClickNGo reservation call-to-action."""
            
            try:
                # Call Ollama's phi model with the system message
                if not TEST_MODE:
                    response = ollama.chat(model='phi', messages=[
                        {'role': 'system', 'content': system_message},
                        {'role': 'user', 'content': user_message}
                    ])
                    bot_response = response['message']['content']
                else:
                    # Test mode responses - no Ollama needed
                    app.logger.info("Using TEST_MODE responses")
                    test_responses = {
                        "c'est quoi le covoiturage": "Le covoiturage est un système où plusieurs personnes partagent un véhicule pour effectuer un trajet commun. Cela permet de réduire les coûts de transport, diminuer l'empreinte carbone et réduire la congestion routière. Chez ClickNGo, nous mettons en relation conducteurs et passagers pour faciliter vos trajets quotidiens ou occasionnels. Tu peux réserver chez nous ClickNGo pour ton prochain covoiturage!",
                        "avantages": "Les avantages du covoiturage avec ClickNGo sont nombreux : économies financières importantes sur le carburant et les péages, réduction de l'empreinte écologique, diminution du trafic routier, création de liens sociaux, et accès à des voies réservées dans certaines villes. Notre plateforme ClickNGo rend le partage de trajets simple et sécurisé. Tu peux réserver chez nous ClickNGo pour ton prochain covoiturage!",
                        "comment réserver": "Pour réserver un trajet avec ClickNGo, c'est très simple : 1) Créez un compte sur notre application ou site web, 2) Recherchez votre itinéraire en indiquant lieu de départ, destination et horaires, 3) Parcourez les offres disponibles, 4) Sélectionnez le trajet qui vous convient, 5) Effectuez votre réservation en ligne. Le paiement est sécurisé et vous recevrez une confirmation immédiate. Tu peux réserver chez nous ClickNGo pour ton prochain covoiturage dès maintenant!",
                    }
                    
                    # Find best matching response or use default
                    for key in test_responses:
                        if key in user_message.lower():
                            bot_response = test_responses[key]
                            break
                    else:
                        bot_response = "Le covoiturage avec ClickNGo est une solution moderne pour partager vos trajets quotidiens ou occasionnels. Notre plateforme facilite la mise en relation entre conducteurs et passagers, offrant ainsi une alternative économique et écologique aux déplacements traditionnels. Tu peux réserver chez nous ClickNGo pour ton prochain covoiturage!"
            except Exception as model_error:
                # Fallback response if Ollama or model fails
                app.logger.error(f"Ollama model error: {str(model_error)}")
                bot_response = (
                    "Le covoiturage est un service qui permet à plusieurs personnes de partager un véhicule "
                    "pour un trajet commun. Cela permet de réduire les coûts de transport, diminuer la "
                    "pollution et créer du lien social. Chez ClickNGo, nous facilitons la mise en relation "
                    "entre conducteurs et passagers pour rendre le covoiturage simple et accessible à tous. "
                    "Tu peux réserver chez nous ClickNGo pour ton prochain covoiturage!"
                )
        else:
            # If not about covoiturage, return a standard message with ClickNGo branding
            bot_response = ("Je suis l'assistant ClickNGo, spécialisé uniquement dans les questions liées au "
                          "covoiturage (carpooling/ridesharing). N'hésite pas à me demander des informations sur "
                          "le covoiturage, nos services, ou comment utiliser la plateforme ClickNGo. "
                          "Tu peux réserver chez nous ClickNGo pour ton prochain covoiturage!")
        
        return jsonify({'response': bot_response})
    except Exception as e:
        app.logger.error(f"General error: {str(e)}")
        # Provide a helpful fallback response that still fulfills the business purpose
        fallback_response = ("Désolé pour ce problème technique. Le covoiturage avec ClickNGo est un moyen "
                            "économique et écologique de voyager en partageant un véhicule. "
                            "Tu peux réserver chez nous ClickNGo pour ton prochain covoiturage!")
        return jsonify({'response': fallback_response})

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)