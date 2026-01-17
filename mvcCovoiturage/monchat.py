from flask import Flask, request, jsonify, render_template
from flask_cors import CORS
import requests
import logging
import os

app = Flask(__name__, template_folder='view')
CORS(app)

logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

# OpenRouter API configuration
OPENROUTER_API_KEY = os.getenv('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')
OPENROUTER_API_URL = 'https://openrouter.ai/api/v1/chat/completions'

@app.route('/')
def index():
    logger.debug("Serving chatt_api.html")
    return render_template('chatt_api.html')

@app.route('/chat', methods=['POST'])
def chat():
    logger.debug("Received chat request")
    data = request.get_json()
    user_message = data.get('message')
    if not user_message:
        logger.error("No message provided")
        return jsonify({'response': 'Please provide a message.'}), 400

    if not OPENROUTER_API_KEY:
        logger.error("API key not set")
        return jsonify({'response': 'Error: API key not configured.'}), 500

    try:
        logger.debug(f"Sending message to OpenRouter: {user_message}")
        headers = {
            'Authorization': f'Bearer {OPENROUTER_API_KEY}',
            'Content-Type': 'application/json'
        }
        payload = {
            'model': 'deepseek/r-1',  # DeepSeek R-1 model
            'messages': [{'role': 'user', 'content': user_message}]
        }
        response = requests.post(OPENROUTER_API_URL, json=payload, headers=headers)
        response.raise_for_status()
        bot_response = response.json()['choices'][0]['message']['content']
        logger.debug(f"Received response from OpenRouter: {bot_response}")
        return jsonify({'response': bot_response})
    except Exception as e:
        logger.error(f"OpenRouter error: {str(e)}")
        return jsonify({'response': f'Error: {str(e)}'}), 500

if __name__ == '__main__':
    from waitress import serve
    serve(app, host='0.0.0.0', port=5001)  # Port 5001 to avoid conflict