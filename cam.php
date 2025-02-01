<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centrer votre visage</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: white;
        }
        .video-container {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        video {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Assure que la vidéo couvre tout l'écran */
        }
        .instructions {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            font-size: 16px;
            color: #333;
            background: rgba(255, 255, 255, 0.8);
            padding: 10px;
            border-radius: 5px;
            z-index: 999999; /* Assure que les instructions sont au-dessus de la vidéo */
        }

        canvas {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1; /* Assurez-vous qu'il est au-dessus de la vidéo */
            pointer-events: none; /* Permet de cliquer à travers le canvas */
        }

        #capturedImage {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 150px;
            height: auto;
            border: 2px solid #333;
            z-index: 999999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="video-container">
            <video id="video" autoplay muted playsinline></video>
        </div>
        <div class="instructions">
            <p>9:41</p>
            <p>Centre ton visage</p>
            <p>Pointe ton visage droit vers la boîte,</p>
            <p>puis prends une photo</p>
        </div>
        <img id="capturedImage" src="" alt="Image capturée" style="display: none;">
    </div>

    <!-- Inclure face-api.js -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js/dist/face-api.min.js"></script>
    <script>
        // Charger les modèles de reconnaissance faciale
        async function loadModels() {
            await faceapi.nets.tinyFaceDetector.loadFromUri('/face-apijs/weights');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/face-apijs/weights');
            await faceapi.nets.faceExpressionNet.loadFromUri('/face-apijs/weights'); // Pour les expressions faciales
        }

        // Démarrer la caméra
        async function startVideo() {
            const video = document.getElementById('video');
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
                video.srcObject = stream;

                // Attendre que la vidéo soit prête et ait des dimensions valides
                await new Promise((resolve) => {
                    const checkDimensions = () => {
                        if (video.videoWidth > 0 && video.videoHeight > 0) {
                            resolve();
                        } else {
                            setTimeout(checkDimensions, 100);
                        }
                    };
                    checkDimensions();
                });
            } catch (err) {
                console.error("Erreur d'accès à la caméra : ", err);
            }
        }

        // Variables pour le suivi du mouvement et de l'expression
        let lastNosePosition = null;
        let lastExpression = null;
        let isFaceStatic = false;
        let blinkCount = 0; // Compteur de clignements d'yeux

        // Détection du visage
        async function detectFace() {
            const video = document.getElementById('video');
            const canvas = faceapi.createCanvasFromMedia(video);
            document.body.appendChild(canvas);

            // Mettre à jour les dimensions de la vidéo
            const displaySize = { width: video.videoWidth, height: video.videoHeight };
            faceapi.matchDimensions(canvas, displaySize);

            // Redimensionner le canvas pour correspondre à la vidéo
            canvas.width = displaySize.width;
            canvas.height = displaySize.height;

            // Positionner le canvas exactement au-dessus de la vidéo
            const videoContainer = document.querySelector('.video-container');
            videoContainer.appendChild(canvas);

            setInterval(async () => {
                // Vérifier que la vidéo a des dimensions valides
                if (video.videoWidth === 0 || video.videoHeight === 0) {
                    console.log("Attente des dimensions de la vidéo...");
                    return;
                }

                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceExpressions(); // Détecter les expressions faciales
                const resizedDetections = faceapi.resizeResults(detections, displaySize);

                // Effacer le canvas avant de redessiner
                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

                // Dessiner les contours du visage détecté
                faceapi.draw.drawDetections(canvas, resizedDetections);
                faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);

                // Vérifier si un visage est détecté
                if (resizedDetections.length > 0) {
                    const face = resizedDetections[0];
                    const nose = face.landmarks.getNose();
                    const expression = face.landmarks.getJawOutline();
                    const eyes = face.landmarks.getLeftEye().concat(face.landmarks.getRightEye());

                    // Vérifier les micro-mouvements du nez
                    if (lastNosePosition) {
                        const distance = Math.sqrt(
                            Math.pow(nose[0].x - lastNosePosition.x, 2) +
                            Math.pow(nose[0].y - lastNosePosition.y, 2)
                        );

                        // Vérifier les changements d'expression
                        const expressionChanged = !arraysEqual(expression, lastExpression);

                        // Si le nez bouge ou l'expression change, le visage n'est pas statique
                        if (distance > 2 || expressionChanged) {
                            isFaceStatic = false;
                        } else {
                            isFaceStatic = true;
                        }
                    }

                    // Détection de clignement des yeux
                    const eyeAspectRatio = getEyeAspectRatio(eyes);
                    if (eyeAspectRatio < 0.2) { // Seuil pour détecter un clignement
                        blinkCount++;
                        console.log("Clignement détecté !");
                    }

                    // Mettre à jour la position du nez et l'expression pour la prochaine frame
                    lastNosePosition = { x: nose[0].x, y: nose[0].y };
                    lastExpression = expression;

                    // Logs pour le débogage
                    console.log("isFaceStatic:", isFaceStatic);
                    console.log("blinkCount:", blinkCount);

                    // Capturer l'image si toutes les conditions sont réunies
                    if (!isFaceStatic && blinkCount >= 1) {
                        console.log("Conditions remplies ! Capture de l'image...");
                        captureImage(video);
                        blinkCount = 0; // Réinitialiser le compteur de clignements
                    }
                }
            }, 100);
        }

        // Fonction pour capturer l'image
        function captureImage(video) {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convertir l'image en base64
            const imageData = canvas.toDataURL('image/png');

            // Afficher l'image capturée
            const capturedImage = document.getElementById('capturedImage');
            capturedImage.src = imageData;
            capturedImage.style.display = 'block';

            console.log("Image capturée !");
        }

        // Fonction pour calculer le rapport d'aspect des yeux (EAR)
        function getEyeAspectRatio(eyes) {
            const A = distance(eyes[1], eyes[5]);
            const B = distance(eyes[2], eyes[4]);
            const C = distance(eyes[0], eyes[3]);
            return (A + B) / (2 * C);
        }

        // Fonction pour calculer la distance entre deux points
        function distance(point1, point2) {
            return Math.sqrt(Math.pow(point1.x - point2.x, 2) + Math.pow(point1.y - point2.y, 2));
        }

        // Fonction pour comparer deux tableaux (utilisé pour les expressions)
        function arraysEqual(a, b) {
            if (a.length !== b.length) return false;
            for (let i = 0; i < a.length; i++) {
                if (a[i].x !== b[i].x || a[i].y !== b[i].y) return false;
            }
            return true;
        }

        // Initialisation
        async function init() {
            await loadModels();
            await startVideo();
            detectFace();
        }

        // Démarrer l'application
        init();
    </script>
</body>
</html>