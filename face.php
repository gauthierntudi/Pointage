<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reconnaissance Faciale avec Kairos</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial, sans-serif;
            background-color: #000; /* Fond noir pour un effet plein écran */
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            overflow: hidden; /* Masquer les barres de défilement */
        }
        video {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Couvrir tout l'écran */
            position: absolute;
            top: 0;
            left: 0;
        }
        #result {
            position: absolute;
            bottom: 20px;
            font-size: 18px;
            color: white;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px;
            border-radius: 5px;
            z-index: 999999;
        }
    </style>
</head>
<body>
    <video id="video" autoplay muted playsinline></video>
    <div id="result"></div>

    <script>
        // Éléments DOM
        const video = document.getElementById('video');
        const resultDiv = document.getElementById('result');

        // Variables pour le suivi
        let isProcessing = false; // Pour éviter les captures multiples

        // Démarrer la caméra
        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
                video.srcObject = stream;
                startFaceDetection(); // Démarrer la détection faciale
            } catch (err) {
                console.error("Erreur d'accès à la caméra : ", err);
                resultDiv.textContent = "Erreur : Impossible d'accéder à la caméra.";
            }
        }

        // Détection faciale en continu
        async function startFaceDetection() {
            setInterval(async () => {
                if (isProcessing) return; // Ne pas traiter si une capture est déjà en cours

                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Convertir l'image en base64
                const imageData = canvas.toDataURL('image/jpeg');

                // Envoyer l'image au serveur backend
                const response = await detectFaceWithBackend(imageData);

                // Vérifier si un visage réel est détecté
                if (response && response.images && response.images.length > 0) {
                    const face = response.images[0];
                    if (face.transaction.status === "success") {
                        resultDiv.textContent = "Visage réel détecté ! Capture de l'image...";
                        isProcessing = true; // Bloquer les captures multiples
                        await captureImage(imageData); // Capturer l'image
                        isProcessing = false; // Réactiver la détection
                    } else {
                        resultDiv.textContent = "Visage détecté, mais pas réel.";
                    }
                } else {
                    resultDiv.textContent = "Aucun visage détecté.";
                }
            }, 1000); // Vérifier toutes les secondes
        }

        // Envoyer l'image au serveur backend
        async function detectFaceWithBackend(imageData) {
            const endpoint = "http://localhost:3001/detect-face"; // Endpoint du serveur backend
            const body = {
                image: imageData.split(',')[1] // Supprimer le préfixe "data:image/jpeg;base64,"
            };

            try {
                const response = await fetch(endpoint, {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(body)
                });
                const data = await response.json();
                console.log("Réponse du serveur backend :", data);
                return data;
            } catch (error) {
                console.error("Erreur lors de la détection de visage :", error);
                resultDiv.textContent = "Erreur lors de la détection de visage.";
                return null;
            }
        }

        // Capturer une image
        async function captureImage(imageData) {
            // Convertir l'image en base64
            const base64Data = imageData.split(',')[1];

            // Envoyer l'image capturée à un serveur ou la sauvegarder
            console.log("Image capturée :", base64Data);

            // Afficher un message de succès
            resultDiv.textContent = "Image capturée avec succès !";
        }

        // Démarrer la caméra au chargement de la page
        startCamera();
    </script>
</body>
</html>