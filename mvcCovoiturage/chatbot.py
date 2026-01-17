from flask import Flask, request, jsonify, render_template
from flask_cors import CORS
import ollama
import logging

app = Flask(__name__, template_folder='view')
CORS(app)

logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

@app.route('/')
def index():
    logger.debug("Serving chatt.html")
    return render_template('chatt.html')

@app.route('/chat', methods=['POST'])
def chat():
    logger.debug("Received chat request")
    data = request.get_json()
    user_message = data.get('message')
    if not user_message:
        logger.error("No message provided")
        return jsonify({'response': 'Please provide a message.'}), 400

    try:
        logger.debug(f"Sending message to Ollama: {user_message}")
        response = ollama.chat(model='phi', messages=[
            {'role': 'user', 'content': user_message}
        ])
        bot_response = response['message']['content']
        logger.debug(f"Received response from Ollama: {bot_response}")
        return jsonify({'response': bot_response})
    except Exception as e:
        logger.error(f"Ollama error: {str(e)}")
        return jsonify({'response': f'Error: {str(e)}'}), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)