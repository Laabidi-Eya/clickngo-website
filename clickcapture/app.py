from flask import Flask, request, jsonify
from PIL import Image
from io import BytesIO

app = Flask(__name__)

@app.route("/")
def home():
    return "ClickCapture IA server is running."

@app.route("/analyze", methods=["POST"])
def analyze():
    if 'image' not in request.files:
        return jsonify({"suggestion": "Aucune image reçue."}), 400

    image_file = request.files['image']
    img = Image.open(image_file.stream)

    # 💡 Ici tu peux utiliser un vrai modèle IA pour analyser l’image
    # Pour le moment on simule :
    suggestion = "C’est pas exactement Sidi Bou, mais Matmata te donne la même vibe !"

    return jsonify({"suggestion": suggestion})

if __name__ == "__main__":
    app.run(debug=True)
