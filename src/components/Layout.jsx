import { Outlet, Link } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Layout() {
    const { user, signOut } = useAuth()

    return (
        <div className="app-container">
            <header>
                <nav>
                    <div className="nav-links">
                        <Link to="/">Accueil</Link>
                        <Link to="/scores">Scores</Link>
                    </div>
                    <h1 style={{margin:0, fontSize:'1.5rem'}}>Portail du Savoir</h1>
                    <div className="auth-links">
                        {user ? (
                            <button onClick={signOut} className="btn" style={{padding:'0.5rem 1rem', fontSize:'0.9rem'}}>Déconnexion</button>
                        ) : (
                            <Link to="/login" style={{color:'white'}}>Connexion</Link>
                        )}
                    </div>
                </nav>
            </header>
            <main>
                <Outlet />
            </main>
            <footer>
                <p className="text-center" style={{color:'#666'}}>&copy; {new Date().getFullYear()} - Jeu éducatif interactif</p>
            </footer>
        </div>
    )
}