import face_recognition
import cv2
import os
import time

database_folder = "C:/xampp/htdocs/Projet Web/mvcUtilisateur/View/BackOffice/login/database_faces"

def recognize_face():
    video_capture = cv2.VideoCapture(0)
    if not video_capture.isOpened():
        print("❌ Camera not opened.")
        return "unauthorized"

    start_time = time.time()
    frame_counter = 0
    recognized_face = None

    while True:
        ret, frame = video_capture.read()
        if not ret:
            break

        small_frame = cv2.resize(frame, (640, 480))
        frame_counter += 1
        if frame_counter % 5 == 0:
            face_locations = face_recognition.face_locations(small_frame, model="hog")
            face_encodings = face_recognition.face_encodings(small_frame, face_locations)

            print(f"⏱️ Frame {frame_counter}: {len(face_encodings)} face(s) detected.")

            for face_encoding in face_encodings:
                best_match_score = float('inf')
                best_match_name = None
                for filename in os.listdir(database_folder):
                    known_image = face_recognition.load_image_file(os.path.join(database_folder, filename))
                    encodings = face_recognition.face_encodings(known_image)
                    if len(encodings) == 0:
                        continue

                    known_encoding = encodings[0]
                    distance = face_recognition.face_distance([known_encoding], face_encoding)[0]
                    if distance < best_match_score:
                        best_match_score = distance
                        best_match_name = filename

                if best_match_score < 0.6:
                    recognized_face = os.path.splitext(best_match_name)[0]
                    print(f"✅ Recognized as {recognized_face} (distance={best_match_score})")
                    break

        if time.time() - start_time > 10 or recognized_face:
            break

    video_capture.release()
    cv2.destroyAllWindows()
    return recognized_face or "unauthorized"

if __name__ == "__main__":
    print(recognize_face())
