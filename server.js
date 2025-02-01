const express = require('express');
const cors = require('cors');
const axios = require('axios');

const app = express();
const PORT = 3001;

// Middleware pour autoriser les requêtes CORS
app.use(cors());

// Middleware pour parser le JSON
app.use(express.json());

// Endpoint pour détecter un visage avec Kairos
app.post('/detect-face', async (req, res) => {
    const { image } = req.body;

    try {
        const response = await axios.post('https://api.kairos.com/detect', {
            image: image
        }, {
            headers: {
                'Content-Type': 'application/json',
                'app_id': '4748773e', // Remplacez par votre App ID
                'app_key': '4249f0fbd5b415942ed543ee01c4b284' // Remplacez par votre clé API
            }
        });

        res.json(response.data);
    } catch (error) {
        console.error("Erreur lors de la détection de visage :", error);
        res.status(500).json({ error: "Erreur lors de la détection de visage" });
    }
});

// Démarrer le serveur
app.listen(PORT, () => {
    console.log(`Serveur backend en cours d'exécution sur http://localhost:${PORT}`);
});