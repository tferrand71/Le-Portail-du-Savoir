import { useState } from 'react'
import { supabase } from '../lib/supabaseClient'
import { useNavigate } from 'react-router-dom'

export default function Login() {
    const [email, setEmail] = useState('')
    const [password, setPassword] = useState('')
    const [isSignUp, setIsSignUp] = useState(false)
    const navigate = useNavigate()

    const handleAuth = async (e) => {
        e.preventDefault()
        let result
        if (isSignUp) {
            result = await supabase.auth.signUp({ email, password })
        } else {
            result = await supabase.auth.signInWithPassword({ email, password })
        }

        if (result.error) alert(result.error.message)
        else navigate('/')
    }

    return (
        <div className="card" style={{maxWidth: '400px', margin: '2rem auto'}}>
            <h2 className="text-center">{isSignUp ? 'Inscription' : 'Connexion'}</h2>
            <form onSubmit={handleAuth}>
                <input type="email" placeholder="Email" value={email} onChange={e => setEmail(e.target.value)} required />
                <input type="password" placeholder="Mot de passe" value={password} onChange={e => setPassword(e.target.value)} required />
                <button type="submit" className="btn" style={{width:'100%'}}>{isSignUp ? "S'inscrire" : "Se connecter"}</button>
            </form>
            <p className="text-center" style={{marginTop: '1rem', cursor: 'pointer', color: '#4b6cb7'}} onClick={() => setIsSignUp(!isSignUp)}>
                {isSignUp ? "Déjà un compte ? Se connecter" : "Pas de compte ? S'inscrire"}
            </p>
        </div>
    )
}