import cv2
import os

VIDEO = "sharam_v2.mp4"
CARPETA_SALIDA = "fotogramas_v2"

os.makedirs(CARPETA_SALIDA, exist_ok=True)

cap = cv2.VideoCapture(VIDEO)

contador = 0
guardadas = 0

INTERVALO = 5

while True:
    ret, frame = cap.read()

    if not ret:
        break

    if contador % INTERVALO == 0:
        nombre = os.path.join(
            CARPETA_SALIDA,
            f"img_{guardadas:04d}.jpg"
        )

        cv2.imwrite(nombre, frame)
        guardadas += 1

    contador += 1

cap.release()

print(f"Fotogramas guardados: {guardadas}")