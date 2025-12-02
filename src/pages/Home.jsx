import { Link } from 'react-router-dom'

const subjects = [
    "Histoire-Géo-EMC", "Anglais", "Allemand", "SVT",
    "Sciences Physiques", "Mathématiques", "Français"
]

export default function Home() {
    return (
        <div className="card fade-in">
            {/* En-tête "Système" */}
            <div style={{
                borderBottom: '1px solid #333',
                paddingBottom: '1rem',
                marginBottom: '2rem',
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center'
            }}>
                <span style={{color: '#00f3ff', fontSize: '0.8rem', letterSpacing: '2px'}}>● SYSTEM ONLINE</span>
                <span style={{color: '#ffd700', fontSize: '0.8rem', letterSpacing: '2px'}}>PLAYER RANK: F</span>
            </div>

            <h1 className="text-center" style={{color: '#fff', marginBottom: '0.5rem', fontFamily: 'Cinzel, serif'}}>
                LA BIBLIOTHÈQUE DU SAVOIR
            </h1>

            <p className="text-center" style={{color: '#94a3b8', fontStyle: 'italic', marginBottom: '3rem'}}>
                "Chaque matière est un donjon. Complétez les quêtes pour élever votre rang."
            </p>

            <div className="subjects">
                {subjects.map((sub, index) => (
                    <Link
                        key={sub}
                        to={`/game/${encodeURIComponent(sub)}`}
                        className="subject-btn"
                        style={{animationDelay: `${index * 0.1}s`}}
                    >
                        {sub}
                    </Link>
                ))}
            </div>
        </div>
    )
}