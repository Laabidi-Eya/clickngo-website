import face_recognition
import cv2
import os
import pickle
import sys

# Chemin vers le dossier principal des encodages faciaux et des photos
KNOWN_FACES_DIR = "C:/xampp/htdocs/face/database"

def register_face(user_id):
    # Créer le dossier s'il n'existe pas
    if not os.path.exists(KNOWN_FACES_DIR):
        print(f"Création du dossier: {KNOWN_FACES_DIR}", file=sys.stderr)
        os.makedirs(KNOWN_FACES_DIR)
    
    video_capture = cv2.VideoCapture(0)
    if not video_capture.isOpened():
        print("Erreur: Impossible d'ouvrir la caméra", file=sys.stderr)
        return False
    
    face_encoding = None
    frame_to_save = None

    window_name = "Enregistrement du visage"
    cv2.namedWindow(window_name, cv2.WINDOW_NORMAL)
    cv2.setWindowProperty(window_name, cv2.WND_PROP_TOPMOST, 1)

    print("Appuyez sur 'c' pour capturer la photo", file=sys.stderr)

    while True:
        ret, frame = video_capture.read()
        if not ret:
            print("Erreur: Impossible de lire l'image de la caméra", file=sys.stderr)
            break

        face_locations = face_recognition.face_locations(frame)

        if face_locations:
            cv2.putText(frame, "Visage détecté, appuyez sur 'c' pour capturer", 
                        (30, 30), cv2.FONT_HERSHEY_SIMPLEX, 
                        0.7, (0, 255, 0), 2)
        else:
            cv2.putText(frame, "Positionnez votre visage correctement", 
                        (30, 30), cv2.FONT_HERSHEY_SIMPLEX, 
                        0.7, (0, 0, 255), 2)

        cv2.imshow(window_name, frame)
        key = cv2.waitKey(1) & 0xFF

        if key == ord('q'):
            print("Annulé par l'utilisateur.", file=sys.stderr)
            break

        if key == ord('c') and face_locations:
            top, right, bottom, left = face_locations[0]
            face_encoding = face_recognition.face_encodings(frame, [(top, right, bottom, left)])[0]
            frame_to_save = frame

            cv2.putText(frame, "Photo prise avec succès!", 
                        (30, 60), cv2.FONT_HERSHEY_SIMPLEX, 
                        1, (0, 255, 0), 2)
            cv2.imshow(window_name, frame)
            cv2.waitKey(1000)
            break

    video_capture.release()
    cv2.destroyAllWindows()
    
    if face_encoding is not None and frame_to_save is not None:
        # Sauvegarder l'encodage
        encoding_filename = os.path.join(KNOWN_FACES_DIR, f"{user_id}.pkl")
        try:
            with open(encoding_filename, "wb") as f:
                pickle.dump({
                    'user_id': user_id,
                    'encoding': face_encoding
                }, f)
            print(f"Encodage sauvegardé: {encoding_filename}", file=sys.stderr)
        except Exception as e:
            print(f"Erreur lors de la sauvegarde de l'encodage: {str(e)}", file=sys.stderr)
            return False
        
        # Sauvegarder l'image JPG
        photo_filename = os.path.join(KNOWN_FACES_DIR, f"{user_id}.jpg")
        try:
            cv2.imwrite(photo_filename, frame_to_save)
            print(f"Photo sauvegardée: {photo_filename}", file=sys.stderr)
        except Exception as e:
            print(f"Erreur lors de la sauvegarde de la photo: {str(e)}", file=sys.stderr)
            return False
        
        print("success")
        return True
    else:
        print("Aucun visage detecte", file=sys.stderr)
        return False

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Usage: python register_face.py <user_id>", file=sys.stderr)
        sys.exit(1)
    
    user_id = sys.argv[1]
    if register_face(user_id):
        print("success")
    else:
        print("Echec de l'enregistrement", file=sys.stderr)
