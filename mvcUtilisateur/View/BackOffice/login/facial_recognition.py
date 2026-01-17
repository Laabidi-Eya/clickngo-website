import face_recognition
import cv2
import os
import pickle
import sys

# Charger les visages enregistrés
KNOWN_FACES_DIR = "C:/xampp/htdocs/face/database"
TOLERANCE = 0.6  # Seuil de similarité

def load_known_faces():
    known_face_encodings = []
    known_face_ids = []
    
    if not os.path.exists(KNOWN_FACES_DIR):
        os.makedirs(KNOWN_FACES_DIR)
        return known_face_encodings, known_face_ids
    
    for filename in os.listdir(KNOWN_FACES_DIR):
        if filename.endswith(".pkl"):
            user_id = os.path.splitext(filename)[0]
            with open(os.path.join(KNOWN_FACES_DIR, filename), "rb") as f:
                data = pickle.load(f)
                known_face_encodings.append(data['encoding'])
                known_face_ids.append(user_id)
    
    return known_face_encodings, known_face_ids

def recognize_face(image_path):
    known_face_encodings, known_face_ids = load_known_faces()
    
    if not known_face_encodings:
        print("Aucun visage enregistré dans la base de données", file=sys.stderr)
        return None
    
    # Charger l'image
    frame = cv2.imread(image_path)
    if frame is None:
        print("Erreur: Impossible de lire l'image", file=sys.stderr)
        return None
    
    # Trouver les visages dans l'image
    face_locations = face_recognition.face_locations(frame)
    face_encodings = face_recognition.face_encodings(frame, face_locations)
    
    for face_encoding in face_encodings:
        # Comparer avec les visages connus
        matches = face_recognition.compare_faces(
            known_face_encodings, face_encoding, tolerance=TOLERANCE)
        
        if True in matches:
            first_match_index = matches.index(True)
            return known_face_ids[first_match_index]
    
    return None

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python facial_recognition.py <image_path>", file=sys.stderr)
        sys.exit(1)
    
    image_path = sys.argv[1]
    user_id = recognize_face(image_path)
    if user_id:
        print(user_id)
    else:
        print("Aucun visage reconnu", file=sys.stderr)