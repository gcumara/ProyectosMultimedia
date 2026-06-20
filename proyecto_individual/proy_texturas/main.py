from tkinter import Tk, filedialog
import cv2
import numpy as np

Tk().withdraw()

# Abre un cuadro de diálogo para que el usuario seleccione una imagen
ruta = filedialog.askopenfilename()

# Carga la imagen seleccionada
imagen = cv2.imread(ruta)

# Obtiene las dimensiones de la imagen
# alto = número de filas
# ancho = número de columnas
# canales = cantidad de canales de color (BGR)
alto, ancho, canales = imagen.shape

# Crea una nueva imagen vacía del mismo tamaño que la original
# Inicialmente todos los píxeles son negros (0,0,0)
clasificada = np.zeros_like(imagen)

# Recorre toda la imagen píxel por píxel
for y in range(alto):
    for x in range(ancho):

        # OpenCV almacena los colores en formato BGR
        # B = Azul, G = Verde, R = Rojo
        azul = int(imagen[y, x, 0])
        verde = int(imagen[y, x, 1])
        rojo = int(imagen[y, x, 2])

        # ---------------------------------------------------
        # CLASIFICACIÓN DE TEXTURAS
        # ---------------------------------------------------

        # 1. VEGETACIÓN (césped, árboles, arbustos)
        # Se identifica cuando el canal verde predomina
        # sobre los demás colores.
        if (verde > rojo and verde > azul) or \
           (verde > 40 and verde > azul * 1.1 and rojo < 140):

            # Se colorea de verde en la imagen clasificada
            clasificada[y, x] = [0, 255, 0]

        # 2. ASFALTO / CARRETERA
        # El asfalto suele presentar valores similares
        # en los tres canales de color (tonos grises).
        # Se permite una pequeña diferencia entre ellos
        # para considerar sombras e iluminación.
        elif abs(rojo - verde) < 35 and \
             abs(verde - azul) < 40 and \
             azul > 50 and rojo < 180:

            # Se colorea de gris
            clasificada[y, x] = [128, 128, 128]

        # 3. TIERRA
        # Los tonos de tierra suelen presentar predominio
        # del rojo seguido del verde, mientras que el azul
        # permanece relativamente bajo.
        elif rojo > verde and \
             verde > azul and \
             rojo > 60:

            # Se colorea de marrón
            clasificada[y, x] = [42, 42, 165]

        # 4. OTRAS SUPERFICIES
        # Cielo, edificios, nieve, agua u otros elementos
        # que no pertenecen a las categorías definidas.
        else:

            # Se mantienen en negro
            clasificada[y, x] = [0, 0, 0]

# ---------------------------------------------------
# VISUALIZACIÓN DE RESULTADOS
# ---------------------------------------------------

vista_original = cv2.resize(imagen, (800, 600))
vista_clasificada = cv2.resize(clasificada, (800, 600))

# Muestra la imagen original
cv2.imshow("Imagen Original", vista_original)

# Muestra la imagen clasificada
cv2.imshow("Clasificacion de Texturas", vista_clasificada)

# Espera hasta que el usuario presione una tecla
cv2.waitKey(0)

# Cierra todas las ventanas abiertas
cv2.destroyAllWindows()