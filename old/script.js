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

// Fonction pour formater le temps en minutes:secondes
function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
}

document.addEventListener("DOMContentLoaded", async () => {
    const container = document.querySelector(".subjects");
    if (container) {
        let res = await api("list_subjects");
        if (res.ok) {
            res.subjects.forEach(sub => {
                let btn = document.createElement("a");
                btn.href = "index.php?page=game&subject=" + encodeURIComponent(sub);
                btn.className = "subject-btn";
                btn.textContent = sub;
                container.appendChild(btn);
            });
        }
    }
    
    // Animation d'entrée pour les éléments
    const animatedElements = document.querySelectorAll('.card, .subject-btn');
    animatedElements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        setTimeout(() => {
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, index * 100);
    });
});