<?php
session_start();
require_once "db.php";

// Définir les tables de matières
$subject_tables = [
    "Histoire-Géo-EMC" => "histoire_geo_emc",
    "Anglais" => "anglais",
    "Allemand" => "allemand",
    "SVT" => "svt",
    "Sciences Physiques" => "sciences_physiques",
    "Mathématiques" => "mathematiques",
    "Français" => "francais"
];

$page = $_GET['page'] ?? 'home';
$subject = $_GET['subject'] ?? null;

include "header.php";
?>

<?php if ($page === 'home'): ?>
    <div class="card">
        <h1>Bienvenue sur le jeu d'association</h1>
        <p class="text-center">Choisissez une matière pour commencer à jouer :</p>
        <div class="subjects">
            <?php foreach ($subject_tables as $sub => $table): ?>
                <a href="index.php?page=game&subject=<?= urlencode($sub) ?>" class="subject-btn"><?= htmlspecialchars($sub) ?></a>
            <?php endforeach; ?>
        </div>
    </div>

<?php elseif ($page === 'game' && $subject): ?>
    <div class="stamp">Jeu Actif</div>
    <div class="breadcrumb">
        <a href="index.php">Accueil</a> > <span><?= htmlspecialchars($subject) ?></span>
    </div>

    <div class="card">
        <h2>Jeu - <?= htmlspecialchars($subject) ?></h2>
        <div id="score" style="text-align: center; margin-bottom: 1.5rem; font-size: 1.2rem; font-weight: 500;">
            Score : <span id="scoreVal">0</span>/<span id="totalVal">0</span> | Temps : <span id="timerVal">00:00</span>
        </div>

        <div class="game-container">
            <div id="questions" class="empty-container">
                <p class="empty-message">Cliquez sur "Nouvelle partie" pour commencer</p>
            </div>
            <div id="answers" class="empty-container">
                <p class="empty-message">Cliquez sur "Nouvelle partie" pour commencer</p>
            </div>
        </div>

        <div class="game-controls">
            <button id="newGameBtn" class="btn">Nouvelle partie</button>
            <a href="index.php" class="btn">Retour accueil</a>
            <a href="index.php?page=manage&subject=<?= urlencode($subject) ?>" class="btn">Gérer les étiquettes</a>
        </div>
    </div>

    <script>
    // Fonction API améliorée avec gestion d'erreurs
    async function api(action, data = {}) {
        try {
            const formData = new FormData();
            for (const k in data) formData.append(k, data[k]);
            let opts = Object.keys(data).length ? { method: "POST", body: formData } : {};
            
            const response = await fetch("api.php?action=" + action, opts);
            if (!response.ok) throw new Error("Erreur réseau");
            
            return await response.json();
        } catch (error) {
            console.error("Erreur API:", error);
            return { ok: false, error: "Erreur de connexion" };
        }
    }

    // Fonction pour mélanger un tableau (algorithme de Fisher-Yates)
    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }
    
    let subject = <?= json_encode($subject) ?>;
    let score = 0;
    let totalQuestions = 0;
    let selectedQuestion = null;
    let startTime = null;
    let timerInterval = null;
    
    // Démarrer le chronomètre
    function startTimer() {
        startTime = new Date();
        timerInterval = setInterval(updateTimer, 1000);
    }
    
    // Mettre à jour l'affichage du timer
    function updateTimer() {
        if (!startTime) return;
        
        let now = new Date();
        let elapsed = Math.floor((now - startTime) / 1000);
        
        let minutes = Math.floor(elapsed / 60);
        let seconds = elapsed % 60;
        
        document.getElementById("timerVal").textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    
    // Arrêter le chronomètre et enregistrer le score
    function stopTimer() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        
        if (!startTime) return 0;
        
        let endTime = new Date();
        return Math.floor((endTime - startTime) / 1000);
    }
    
    // Enregistrer le score
    async function saveScore(timeSeconds) {
        await api("save_score", {
            subject: subject,
            score: score,
            total: totalQuestions,
            time_seconds: timeSeconds
        });
    }

    // Afficher un message de félicitations avec le score
    function showCongratulations(score, total, timeSeconds) {
        const minutes = Math.floor(timeSeconds / 60);
        const seconds = timeSeconds % 60;
        const timeFormatted = `${minutes}min ${seconds}s`;
        const percentage = Math.round((score / total) * 100);
        
        // Créer un overlay de félicitations
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0,0,0,0.8)';
        overlay.style.display = 'flex';
        overlay.style.justifyContent = 'center';
        overlay.style.alignItems = 'center';
        overlay.style.zIndex = '1000';
        overlay.style.color = 'white';
        overlay.style.fontSize = '1.5rem';
        overlay.style.textAlign = 'center';
        
        overlay.innerHTML = `
            <div style="background: white; color: #333; padding: 2rem; border-radius: 12px; max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                <h2 style="color: #4b6cb7; margin-top: 0;">Félicitations !</h2>
                <p>Vous avez terminé le jeu avec un score de</p>
                <div style="font-size: 3rem; font-weight: bold; color: #4caf50; margin: 1rem 0;">${score}/${total}</div>
                <p>Soit ${percentage}% de bonnes réponses</p>
                <p>Temps: ${timeFormatted}</p>
                <div style="margin-top: 2rem;">
                    <button id="closeCongratulations" style="padding: 0.8rem 1.5rem; background: #4b6cb7; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem;">Fermer</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        // Ajouter l'événement pour fermer l'overlay
        document.getElementById('closeCongratulations').addEventListener('click', function() {
            document.body.removeChild(overlay);
        });
    }

    // Bouton "Nouvelle partie"
    document.getElementById("newGameBtn").addEventListener("click", () => {
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        
        score = 0;
        document.getElementById("scoreVal").textContent = score;
        document.getElementById("timerVal").textContent = "00:00";
        
        loadPairs();
        startTimer();
    });

    async function loadPairs() {
        console.log("Chargement des paires pour:", subject);
        let res = await api("list_pairs&subject=" + encodeURIComponent(subject));
        console.log("Réponse API:", res);
        
        if (!res.ok) {
            alert("Erreur: " + (res.error || "Impossible de charger les paires"));
            return;
        }

        let questionsDiv = document.getElementById("questions");
        let answersDiv = document.getElementById("answers");
        
        // Enlever les messages vides et les classes d'état vide
        questionsDiv.innerHTML = "";
        answersDiv.innerHTML = "";
        questionsDiv.classList.remove("empty-container");
        answersDiv.classList.remove("empty-container");

        // Vérifier s'il y a des paires à afficher
        if (!res.pairs || res.pairs.length === 0) {
            questionsDiv.innerHTML = '<p class="empty-message">Aucune paire d\'étiquettes disponible</p>';
            answersDiv.innerHTML = '<p class="empty-message">Aucune paire d\'étiquettes disponible</p>';
            questionsDiv.classList.add("empty-container");
            answersDiv.classList.add("empty-container");
            return;
        }

        // Mettre à jour le total de questions
        totalQuestions = res.pairs.length;
        document.getElementById("totalVal").textContent = totalQuestions;

        // Mélanger les paires de questions
        const shuffledPairs = shuffleArray([...res.pairs]);
        
        // Créer les étiquettes de questions
        shuffledPairs.forEach((p, index) => {
            // Question
            let q = document.createElement("div");
            q.className = "label question fade-in";
            q.style.animationDelay = (index * 0.1) + "s";
            q.textContent = p.question;
            q.dataset.id = p.id;
            questionsDiv.appendChild(q);
        });

        // Mélanger les réponses séparément
        const shuffledAnswers = shuffleArray([...res.pairs]);
        
        // Créer les étiquettes de réponses
        shuffledAnswers.forEach((p, index) => {
            // Réponse
            let a = document.createElement("div");
            a.className = "label answer fade-in";
            a.style.animationDelay = (index * 0.1) + "s";
            a.textContent = p.answer;
            a.dataset.qid = p.id;
            answersDiv.appendChild(a);
        });

        // Sélection d'une question
        document.querySelectorAll(".question").forEach(q => {
            q.onclick = () => {
                document.querySelectorAll(".question").forEach(el => el.classList.remove("selected"));
                q.classList.add("selected");
                selectedQuestion = q.dataset.id;
            };
        });

        // Vérification avec la réponse
        document.querySelectorAll(".answer").forEach(a => {
            a.onclick = () => {
                if (!selectedQuestion) return;
                if (a.dataset.qid === selectedQuestion) {
                    a.classList.add("correct");
                    document.querySelector(".question.selected").classList.add("correct");
                    score++;
                    document.getElementById("scoreVal").textContent = score;
                    
                    // Désactiver les étiquettes correctes
                    a.onclick = null;
                    document.querySelector(".question.selected").onclick = null;
                    
                    // Vérifier si le jeu est terminé
                    let remaining = document.querySelectorAll('.question:not(.correct)').length;
                    if (remaining === 0) {
                        let timeSeconds = stopTimer();
                        saveScore(timeSeconds);
                        setTimeout(() => {
                            showCongratulations(score, totalQuestions, timeSeconds);
                        }, 500);
                    }
                } else {
                    a.classList.add("wrong");
                    document.querySelector(".question.selected").classList.add("wrong");
                    
                    // Réinitialiser après un court délai
                    setTimeout(() => {
                        a.classList.remove("wrong");
                        document.querySelector(".question.selected").classList.remove("wrong");
                        document.querySelectorAll(".question").forEach(el => el.classList.remove("selected"));
                        selectedQuestion = null;
                    }, 1000);
                }
                selectedQuestion = null;
            };
        });
    }
    </script>

<?php elseif ($page === 'manage' && $subject): ?>
    <div class="stamp">Gestion</div>
    <div class="breadcrumb">
        <a href="index.php">Accueil</a> > 
        <a href="index.php?page=game&subject=<?= urlencode($subject) ?>">Jeu</a> > 
        <span>Gestion</span>
    </div>

    <div class="card">
        <h2>Gestion des étiquettes - <?= htmlspecialchars($subject) ?></h2>

        <form id="addForm">
            <input type="hidden" name="subject" value="<?= htmlspecialchars($subject) ?>">
            <input type="text" name="question" placeholder="Question" required>
            <input type="text" name="answer" placeholder="Réponse" required>
            <button type="submit">Ajouter</button>
        </form>

        <div id="pairList" class="pair-list"></div>
    </div>

    <script>
    // Fonction API améliorée avec gestion d'erreurs
    async function api(action, data = {}) {
        try {
            const formData = new FormData();
            for (const k in data) formData.append(k, data[k]);
            let opts = Object.keys(data).length ? { method: "POST", body: formData } : {};
            
            const response = await fetch("api.php?action=" + action, opts);
            if (!response.ok) throw new Error("Erreur réseau");
            
            return await response.json();
        } catch (error) {
            console.error("Erreur API:", error);
            return { ok: false, error: "Erreur de connexion" };
        }
    }
    
    let subject = <?= json_encode($subject) ?>;
    let editingId = null;

    async function loadList() {
        let res = await api("list_pairs&subject=" + encodeURIComponent(subject));
        if (!res.ok) {
            alert("Erreur: " + (res.error || "Impossible de charger les étiquettes"));
            return;
        }
        
        let list = document.getElementById("pairList");
        list.innerHTML = "";
        
        if (res.pairs.length === 0) {
            list.innerHTML = '<p class="no-pairs">Aucune paire d\'étiquettes créée pour le moment.</p>';
            return;
        }
        
        res.pairs.forEach(p => {
            let pairElement = document.createElement("div");
            pairElement.className = "pair-item";
            pairElement.dataset.id = p.id;
            
            if (editingId === p.id) {
                // Mode édition
                pairElement.innerHTML = `
                    <div class="edit-form">
                        <input type="text" class="edit-question" value="${p.question}" placeholder="Question">
                        <input type="text" class="edit-answer" value="${p.answer}" placeholder="Réponse">
                        <div class="pair-actions">
                            <button class="save-btn">Enregistrer</button>
                            <button class="cancel-btn">Annuler</button>
                        </div>
                    </div>
                `;
            } else {
                // Mode affichage
                pairElement.innerHTML = `
                    <div class="pair-content">
                        <span class="pair-text">${p.question} → ${p.answer}</span>
                        <div class="pair-actions">
                            <button class="edit-btn">Modifier</button>
                            <button class="delete-btn">Supprimer</button>
                        </div>
                    </div>
                `;
            }
            
            list.appendChild(pairElement);
            
            // Ajouter les événements
            if (editingId !== p.id) {
                // Bouton modifier
                pairElement.querySelector(".edit-btn").onclick = () => {
                    editingId = p.id;
                    loadList();
                };
                
                // Bouton supprimer
                pairElement.querySelector(".delete-btn").onclick = async () => {
                    if (confirm("Supprimer cette paire ?")) {
                        await api("delete_pair", { subject, id: p.id });
                        loadList();
                    }
                };
            } else {
                // Bouton enregistrer
                pairElement.querySelector(".save-btn").onclick = async () => {
                    const newQuestion = pairElement.querySelector(".edit-question").value.trim();
                    const newAnswer = pairElement.querySelector(".edit-answer").value.trim();
                    
                    if (!newQuestion || !newAnswer) {
                        alert("Veuillez remplir les deux champs.");
                        return;
                    }
                    
                    await api("update_pair", { 
                        subject, 
                        id: p.id, 
                        question: newQuestion, 
                        answer: newAnswer 
                    });
                    
                    editingId = null;
                    loadList();
                };
                
                // Bouton annuler
                pairElement.querySelector(".cancel-btn").onclick = () => {
                    editingId = null;
                    loadList();
                };
            }
        });
    }

    document.getElementById("addForm").onsubmit = async e => {
        e.preventDefault();
        let formData = new FormData(e.target);
        let obj = {};
        formData.forEach((v,k)=>obj[k]=v);
        let result = await api("add_pair", obj);
        
        if (result.ok) {
            e.target.reset();
            loadList();
        } else {
            alert("Erreur: " + (result.error || "Impossible d'ajouter la paire"));
        }
    };

    // Charger la liste des étiquettes immédiatement
    loadList();
    </script>

<?php elseif ($page === 'scores'): ?>
    <div class="breadcrumb">
        <a href="index.php">Accueil</a> > 
        <span>Scores</span>
    </div>

    <div class="scores-page card">
        <h2>Tableau des scores</h2>
        
        <div class="scores-controls">
            <div class="filter-group">
                <label for="subjectFilter">Filtrer par matière:</label>
                <select id="subjectFilter" class="filter-select">
                    <option value="">Toutes les matières</option>
                    <?php
                    foreach ($subject_tables as $sub => $table): ?>
                        <option value="<?= htmlspecialchars($sub) ?>"><?= htmlspecialchars($sub) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button id="refreshScores" class="btn btn-primary">
                <span id="refreshText">Actualiser</span>
                <span id="refreshSpinner" class="loading" style="display: none;"></span>
            </button>
        </div>

        <div id="scoresContainer">
            <div class="table-responsive">
                <table class="score-table">
                    <thead>
                        <tr>
                            <th>Matière</th>
                            <th>Score</th>
                            <th>Pourcentage</th>
                            <th>Temps</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="scoresBody">
                        <tr><td colspan="5" class="text-center">Chargement des scores...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    // Fonction API améliorée avec gestion d'erreurs
    async function api(action, data = {}) {
        try {
            const formData = new FormData();
            for (const k in data) formData.append(k, data[k]);
            let opts = Object.keys(data).length ? { method: "POST", body: formData } : {};
            
            const response = await fetch("api.php?action=" + action, opts);
            if (!response.ok) throw new Error("Erreur réseau");
            
            return await response.json();
        } catch (error) {
            console.error("Erreur API:", error);
            return { ok: false, error: "Erreur de connexion" };
        }
    }
    
    async function loadScores(subject = '') {
        // Afficher l'indicateur de chargement
        document.getElementById("refreshText").style.display = "none";
        document.getElementById("refreshSpinner").style.display = "inline-block";
        
        let res = await api("get_scores" + (subject ? "&subject=" + encodeURIComponent(subject) : ""));
        
        // Cacher l'indicateur de chargement
        document.getElementById("refreshText").style.display = "inline-block";
        document.getElementById("refreshSpinner").style.display = "none";
        
        if (!res.ok) {
            document.getElementById("scoresBody").innerHTML = 
                '<tr><td colspan="5" class="text-center error">Erreur lors du chargement des scores: ' + 
                (res.error || "Erreur inconnue") + '</td></tr>';
            return;
        }
        
        let tbody = document.getElementById("scoresBody");
        tbody.innerHTML = '';
        
        if (res.scores.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">Aucun score enregistré</td></tr>';
            return;
        }
        
        res.scores.forEach(score => {
            let tr = document.createElement("tr");
            tr.className = "animate-in";
            
            // Calcul du pourcentage
            let percentage = Math.round((score.score / score.total) * 100);
            
            // Formatage du temps
            let minutes = Math.floor(score.time_seconds / 60);
            let seconds = score.time_seconds % 60;
            let timeFormatted = `${minutes}min ${seconds}s`;
            
            // Formatage de la date
            let date = new Date(score.created_at);
            let dateFormatted = date.toLocaleDateString('fr-FR') + ' ' + 
                               date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
            
            // Barre de progression visuelle pour le score
            let percentageBar = `
                <div class="score-bar-container">
                    <div class="score-bar" style="width: ${percentage}%">
                        <span class="score-text">${percentage}%</span>
                    </div>
                </div>
            `;
            
            tr.innerHTML = `
                <td><span class="subject-badge">${score.subject}</span></td>
                <td>${score.score}/${score.total}</td>
                <td>${percentageBar}</td>
                <td>${timeFormatted}</td>
                <td>${dateFormatted}</td>
            `;
            
            tbody.appendChild(tr);
        });
    }
    
    document.getElementById("subjectFilter").addEventListener("change", function() {
        loadScores(this.value);
    });
    
    document.getElementById("refreshScores").addEventListener("click", function() {
        loadScores(document.getElementById("subjectFilter").value);
    });
    
    // Charger les scores au démarrage
    document.addEventListener("DOMContentLoaded", function() {
        loadScores();
    });
    </script>

<?php else: ?>
    <div class="card">
        <h2>Page introuvable</h2>
        <p class="text-center">La page que vous recherchez n'existe pas.</p>
        <div class="text-center">
            <a href="index.php" class="btn">Retour à l'accueil</a>
        </div>
    </div>
<?php endif; ?>

<?php include "footer.php"; ?>