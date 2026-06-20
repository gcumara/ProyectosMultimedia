import os
import cv2
import numpy as np
from flask import Flask, render_template, request, redirect, url_for
from werkzeug.utils import secure_filename

app = Flask(__name__)

# Configuración de carpetas
UPLOAD_FOLDER = 'static/uploads'
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg'}
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

# Crear carpeta de subidas si no existe
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

def aplicar_filtro_promedio(filepath, filename):
    # 1. Leer la imagen original
    img = cv2.imread(filepath)
    
    # 2. Diseñar el filtro de promedio (Ventana 3x3)
    # Una matriz de 3x3 donde cada elemento es 1/9. 
    kernel_3x3 = np.ones((3, 3), np.float32) / 9.0
    
    # 3. Aplicar el filtro que recorre la imagen
    # cv2.filter2D realiza la convolución de la imagen con nuestro kernel
    img_suavizada = cv2.filter2D(img, -1, kernel_3x3)
    
    # 4. Guardar la imagen procesada
    processed_filename = 'suavizado_' + filename
    processed_filepath = os.path.join(app.config['UPLOAD_FOLDER'], processed_filename)
    cv2.imwrite(processed_filepath, img_suavizada)
    
    return processed_filename

@app.route('/', methods=['GET', 'POST'])
def index():
    if request.method == 'POST':
        # Verificar si se subió un archivo
        if 'file' not in request.files:
            return redirect(request.url)
        file = request.files['file']
        
        if file.filename == '':
            return redirect(request.url)
            
        if file and allowed_file(file.filename):
            filename = secure_filename(file.filename)
            filepath = os.path.join(app.config['UPLOAD_FOLDER'], filename)
            file.save(filepath)
            
            # Aplicar el filtro de suavizado
            processed_filename = aplicar_filtro_promedio(filepath, filename)
            
            return render_template('index.html', 
                                   original_img=filename, 
                                   processed_img=processed_filename)
                                   
    return render_template('index.html')

if __name__ == '__main__':
    app.run(debug=True)