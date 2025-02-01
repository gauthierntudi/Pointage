<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Recognition Interface</title>
    <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/blazeface"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #000;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }

        .container {
            position: relative;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
        }

        .animated-border-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 0;
        }

        .animated-border {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 12px;
            background: linear-gradient(45deg, #ff0000, #ff7300, #ffeb00, #47ff00, #00ffee, #2b65ff, #8000ff, #ff0080);
            background-size: 400% 400%;
            animation: gradient-animation 6s infinite;
            filter: blur(20px);
        }

        @keyframes gradient-animation {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .camera-wrapper {
            position: relative;
            display: flex;
            align-items: center; /* Centre verticalement */
            justify-content: center; /* Centre horizontalement */
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .camera {
            width: 100%; /* Ajustez selon vos besoins */
            height: 100%; /* Ajustez selon vos besoins */
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;

            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(70px);
            border-radius: 0px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 0px;
        }

        .overlay {
            position: absolute;
            width: 60%;
            height: 30%;
            border: 0px solid white;
            border-radius: 8px;
            top: 35%;
            left: 20%;
            pointer-events: none;
            animation: pulse 1.5s infinite;
            z-index: 2;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                opacity: 1;
            }
        }

        .capture-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 3;
        }

        .capture-animation dotlottie-player {
            width: 700px;
            height: 700px;
        }

        
        /* Bouton */
button {
    padding: 10px 20px;
    font-size: 16px;
    background-color: #007BFF;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}

button:hover {
    background-color: #0056b3;
}

/* Modal (fond) */
.modal {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: center;
    align-items: flex-end;
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

/* Contenu du Modal */
.modal-content {
    width: 100%;
    max-width: 100%;
    height: 30vh;
    background: white;
    padding: 50px;
    border-radius: 130px 130px 0 0;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    transform: translateY(100%);
    transition: transform 0.3s ease-in-out;
}

/* Contenu du Modal */
.modal-content-home {
    width: 100%;
    height: 100%;
    background: white;
    padding: 50px;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out;
}

/* Afficher le modal */
.modal.show {
    visibility: visible;
    opacity: 1;
}

.modal.show .modal-content {
    transform: translateY(0);
}

.modal.show .modal-content-home{
}

/* Bouton fermer */
.close-btn {
    font-size: 24px;
    cursor: pointer;
    float: right;
}

/* Style du conteneur */
.dateTimeContainer {
    font-family: Arial, sans-serif;
    font-size: 24px;
    font-weight: bold;
    text-align: left;
    padding-left: 23px!important;
    margin-top: 20px;
    padding: 10px;
    color: white;
    border-radius: 0px;
    border-left:solid 1px #fff;
    display: inline-block;
}
</style>
</head>
<body>

    <!-- Modal Bottom Succes -->
    <div id="bottomModalSuccess" class="modal" style="z-index: 9999999;">
        <div class="modal-content" style="text-align: center;">
            <span class="close-btn" style="display:none;">&times;</span>
            <i class="bi bi-check-circle-fill" style="color: #07bd0f;font-size: 8em;"></i>
            <h1 style="font-size: 3em;line-height: .8em;">
                Opération réussie<br>
                <span style="font-size:.4em">
                    Optimiser votre gestion des ressources humaines
                </span>
            </h1>

            <div style="width:100%;text-align: center;justify-content: center;align-items: flex-center;margin-top: 50px;">

                <div style="display: flex;justify-content: center">
                    <img src="img/logo01.png" style="width:200px">
                    <img src="img/logo-default.png" style="width:200px">
                </div>
                
            </div>
        </div>
    </div>
    <!-- /Modal Bottom Succes -->


    <!-- Modal Bottom Failled -->
    <div id="bottomModalFailled" class="modal" style="z-index: 9999999;">
        <div class="modal-content" style="text-align: center;">
            <span class="close-btn" style="display:none;">&times;</span>
            <i class="bi bi-shield-fill-x" style="color: #ff0000;font-size: 8em;"></i>
            <h1 style="font-size: 3em;line-height: .8em;">
                Opération Echouée<br>
                <span style="font-size:.4em">
                    Optimiser votre gestion des ressources humaines
                </span>
            </h1>

            <div style="width:100%;text-align: center;justify-content: center;align-items: flex-center;margin-top: 50px;">

                <div style="display: flex;justify-content: center">
                    <img src="img/logo01.png" style="width:200px">
                    <img src="img/logo-default.png" style="width:200px">
                </div>
                
            </div>
        </div>
    </div>
    <!-- /Modal Bottom Failled -->


    <!-- Modal Bottom Failled -->
    <div id="bottomModalHome" class="modal" style="z-index: 99999999999;">
        <div class="modal-content-home" style="text-align: center;background: rgb(254,246,247);
background: linear-gradient(332deg, rgba(254,246,247,1) 0%, rgba(251,235,250,1) 23%, rgba(232,234,246,1) 46%, rgba(255,255,255,1) 72%, rgba(251,245,225,1) 100%);">

            <!-- logo apps -->
            <img src="img/logo01.png" style="position: absolute; top: 70px; left: 30%; transform: translateX(-25%); z-index: 99999999; width: 300px;">
            <!-- /logo apps -->

            <!-- Conteneur de l'heure et de la date -->
            <div class="dateTimeContainer" style="position: absolute; top: 50px; left: 80%; transform: translateX(-80%); z-index: 99999999; width: 300px;color: #6e6e6e!important;border-left: solid 1px #6e6e6e;line-height: 1.3em;"></div>



            <span class="close-btn" style="display:none;">&times;</span>
            <div style="margin-top: 40%;">
            <!-- <i class="bi bi-shield-fill-x" style="color: #ff0000;font-size: 8em;"></i> -->
            <h1 style="font-size: 3em;line-height: .8em;margin-top: 100px;">

                <dotlottie-player src="https://lottie.host/75aee023-6208-4ed4-b573-f14e94944c1c/wyK8akxo3o.lottie" background="transparent" speed="1" style="width: 500px; height: 500px;position: relative; left: 50%; transform: translateX(-50%); z-index: 99999999;color: #6e6e6e!important;" loop autoplay></dotlottie-player>
                Positionnez-vous<br>
                <span style="font-size:.7em;color:#929191">
                    Pour la capture
                </span>
            </h1>
            </div>
            

            <div style="width:100%;text-align: center;justify-content: center;align-items: flex-center;margin-top: 100px;position: absolute; left: 50%; transform: translateX(-50%); z-index: 99999999;bottom: 59px;">

                <div style="display: flex;justify-content: center">
                    <!-- <img src="img/logo01.png" style="width:200px"> -->
                    <img src="img/logo-default.png" style="width:300px">
                </div>
                
            </div>
        </div>
    </div>
    <!-- /Modal Bottom Failled -->

    <!-- animation -->
    <img src="img/001.png" id="animated" style="position: absolute;top: 0;left: 0;bottom: 0;right: 0;z-index: 999999999999;object-fit:;height: 100vh;width: 100vw;border-radius: 0px;">
    <!-- /animation -->

    <!-- logo apps -->
    <img src="img/logo.png" style="position: absolute; top: 50px; left: 30%; transform: translateX(-25%); z-index: 99999999; width: 300px;">
    <!-- /logo apps -->

    <!-- Conteneur de l'heure et de la date -->
    <div class="dateTimeContainer" style="position: absolute; top: 50px; left: 80%; transform: translateX(-80%); z-index: 99999999; width: 300px;"></div>

    <div class="container">
        <!-- <div class="animated-border-wrapper">
            <div class="animated-border"></div>
        </div>-->
        <div class="camera-wrapper">
            <div class="camera inner-border">
                <video id="video" autoplay muted></video>
            </div>
        </div>
        <div class="overlay"></div>


        <div id="capture-animation" class="capture-animation" style="display: ;">
            <dotlottie-player src="https://lottie.host/99619f19-f126-488c-a4b6-685b739b5445/XWtE4qEwE1.lottie" background="transparent" speed="1" loop autoplay></dotlottie-player>
        </div>
    </div>

    <script>
        const video = document.getElementById('video');
        const captureAnimation = document.getElementById('capture-animation');

        // Load face detection model
        let model;
        async function loadModel() {
            model = await blazeface.load();
        }

        // Start video stream
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
            .then(stream => {
                video.srcObject = stream;
                video.addEventListener('loadeddata', detectFace);
            })
            .catch(error => {
                console.error('Error accessing webcam:', error);
            });

        async function detectFace() {
            if (model) {
                const predictions = await model.estimateFaces(video, false);

                const faceDetected = predictions.some(prediction => {
                    // Check if a valid face is detected (filter by bounding box size if needed)
                    const { topLeft, bottomRight } = prediction;
                    const width = bottomRight[0] - topLeft[0];
                    const height = bottomRight[1] - topLeft[1];
                    return width > 100 && height > 100; // Example filter to ignore small objects
                });

                if (faceDetected) {
                    // Face detected
                    captureAnimation.style.display = 'flex';
                    setTimeout(() => {
                        captureAnimation.style.display = 'none';

                        // Capture image from video
                        const canvas = document.createElement('canvas');
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        const context = canvas.getContext('2d');
                        context.drawImage(video, 0, 0, canvas.width, canvas.height);

                        // Convert canvas to image URL
                        const imageData = canvas.toDataURL('image/png');
                        console.log('Captured Image Data:', imageData);

                        // Integrate face recognition functionality here
                        //alert('Face detected and image captured. Implement further logic.');
                    }, 2000);
                } else {
                    requestAnimationFrame(detectFace);
                }
            } else {
                requestAnimationFrame(detectFace);
            }
        }

        loadModel();



// animation

document.addEventListener("DOMContentLoaded", function () {
    // Générer la liste des images automatiquement
    const images = Array.from({ length: 99 }, (_, i) => `img/${(i + 1).toString().padStart(3, '0')}.png`);

    let index = 0;
    const imgElement = document.getElementById("animated");

    function changeImage() {
        index = (index + 1) % images.length; // Boucle infinie sur la liste des images
        imgElement.src = images[index];
    }

    // Changer d'image toutes les 2 secondes
    setInterval(changeImage, 85);
});

// End animation


// Modal
// Sélection des éléments
const modals = {
    success: document.getElementById("bottomModalSuccess"),
    failed: document.getElementById("bottomModalFailled"),
    home: document.getElementById("bottomModalHome")
};

// Fonction pour afficher un modal spécifique
function showModal(type) {
    if (modals[type]) {
        modals[type].classList.add("show");
    } else {
        console.warn(`Le modal "${type}" n'existe pas.`);
    }
}

// Fonction pour fermer un modal spécifique
function closeModal(type) {
    if (modals[type]) {
        modals[type].classList.remove("show");
    }
}

// Fermer le modal au clic sur le bouton de fermeture
document.querySelectorAll(".close-btn").forEach(button => {
    button.addEventListener("click", function () {
        const parentModal = this.closest(".modal");
        if (parentModal) {
            parentModal.classList.remove("show");
        }
    });
});

// Fermer le modal en cliquant en dehors
window.addEventListener("click", (event) => {
    Object.values(modals).forEach(modal => {
        if (modal && event.target === modal) {
            modal.classList.remove("show");
        }
    });
});

// 📌 Exemples d'utilisation
//showModal("success"); // Affiche le modal de succès
//showModal("failed");  // Affiche le modal d'échec
showModal("home");  // Affiche le modal "home"

</script>
<script type="text/javascript">
// Fonction pour mettre à jour la date et l'heure
function updateDateTime() {
    const now = new Date();

    // Formater la date et l'heure en français
    const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateString = now.toLocaleDateString('fr-FR', optionsDate);
    
    const optionsTime = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
    const timeString = now.toLocaleTimeString('fr-FR', optionsTime);

    // Sélectionner tous les éléments avec la classe "dateTimeContainer"
    const dateTimeElements = document.querySelectorAll(".dateTimeContainer");

    // Mettre à jour tous les éléments trouvés
    dateTimeElements.forEach(element => {
        element.innerHTML = `${dateString} - ${timeString}`;
    });
}

// Mettre à jour immédiatement et ensuite toutes les secondes
updateDateTime();
setInterval(updateDateTime, 1000);



</script>
</body>
</html>