import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import { supabase } from '../lib/supabaseClient'
import { useAuth } from '../context/AuthContext'

export default function Manage() {
    const { subject } = useParams()
    const decodedSubject = decodeURIComponent(subject)
    const { role } = useAuth()
    const [pairs, setPairs] = useState([])
    const [newQ, setNewQ] = useState('')
    const [newA, setNewA] = useState('')

    useEffect(() => {
        fetchPairs()
    }, [])

    const fetchPairs = async () => {
        const { data } = await supabase
            .from('questions')
            .select('*')
            .eq('subject', decodedSubject)
            .order('created_at', { ascending: false })
        setPairs(data || [])
    }

    const handleAdd = async (e) => {
        e.preventDefault()
        const { error } = await supabase.from('questions').insert({
            subject: decodedSubject,
            question: newQ,
            answer: newA
        })

        if (error) alert("Erreur : Seuls les professeurs peuvent ajouter des questions.")
        else {
            setNewQ('')
            setNewA('')
            fetchPairs()
        }
    }

    const handleDelete = async (id) => {
        if (!confirm('Supprimer cette question ?')) return
        const { error } = await supabase.from('questions').delete().eq('id', id)
        if (error) alert("Impossible de supprimer")
        else fetchPairs()
    }

    if (role !== 'teacher') return (
        <div className="card text-center">
            <h2>Accès refusé</h2>
            <p>Cette page est réservée aux professeurs.</p>
            <Link to="/" className="btn">Retour</Link>
        </div>
    )

    return (
        <div className="card">
            <div style={{display:'flex', justifyContent:'space-between'}}>
                <h2>Gestion - {decodedSubject}</h2>
                <Link to={`/game/${encodeURIComponent(decodedSubject)}`} className="btn">Retour au Jeu</Link>
            </div>

            <form onSubmit={handleAdd} style={{background:'#f1f3f5', padding:'1.5rem', borderRadius:'8px', marginBottom:'2rem'}}>
                <h3>Ajouter une question</h3>
                <input value={newQ} onChange={e => setNewQ(e.target.value)} placeholder="Question (ex: 7 x 8)" required />
                <input value={newA} onChange={e => setNewA(e.target.value)} placeholder="Réponse (ex: 56)" required />
                <button type="submit" className="btn" style={{background:'var(--success)'}}>Ajouter</button>
            </form>

            <div className="pair-list">
                {pairs.length === 0 && <p>Aucune question pour le moment.</p>}
                {pairs.map(p => (
                    <div key={p.id} className="pair-item">
                        <span style={{fontWeight:'500'}}>{p.question}</span>
                        <span>➝</span>
                        <span style={{color:'#666'}}>{p.answer}</span>
                        <button onClick={() => handleDelete(p.id)} className="btn btn-danger" style={{padding:'0.5rem'}}>Suppr.</button>
                    </div>
                ))}
            </div>
        </div>
    )
}