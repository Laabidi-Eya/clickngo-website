import cv2

cap = cv2.VideoCapture(0, cv2.CAP_DSHOW)
if not cap.isOpened():
    print("Camera NOT available (CAP_DSHOW)")
else:
    print("Camera OK (CAP_DSHOW)")
    cap.release()
