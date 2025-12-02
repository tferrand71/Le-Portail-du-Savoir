import { useState, useEffect } from 'react'
import { supabase } from '../lib/supabaseClient'

export default function Scores() {
    const [scores, setScores] = useState([])
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        const fetchScores = async () => {
            const { data, error } = await supabase
                .from('game_scores')
                .select('*')
                .order('created_at', { ascending: false })
                .limit(20)

            if (!error) setScores(data)
            setLoading(false)
        }
        fetchScores()
    }, [])

    return (
        <div className="card">
            <h2>Derniers Scores</h2>
            {loading ? <p>Chargement...</p> : (
                <table style={{width:'100%', borderCollapse:'collapse'}}>
                    <thead>
                    <tr style={{background:'#f8f9fa', textAlign:'left'}}>
                        <th style={{padding:'1rem'}}>Matière</th>
                        <th style={{padding:'1rem'}}>Score</th>
                        <th style={{padding:'1rem'}}>Temps</th>
                        <th style={{padding:'1rem'}}>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    {scores.map(s => (
                        <tr key={s.id} style={{borderBottom:'1px solid #eee'}}>
                            <td style={{padding:'1rem'}}>
                    <span style={{background:'#e3f2fd', color:'#4b6cb7', padding:'0.2rem 0.5rem', borderRadius:'12px', fontSize:'0.9rem'}}>
                        {s.subject}
                    </span>
                            </td>
                            <td style={{padding:'1rem', fontWeight:'bold'}}>{s.score}/{s.total}</td>
                            <td style={{padding:'1rem'}}>{Math.floor(s.time_seconds / 60)}m {s.time_seconds % 60}s</td>
                            <td style={{padding:'1rem', color:'#888'}}>
                                {new Date(s.created_at).toLocaleDateString()}
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </table>
            )}
            {scores.length === 0 && !loading && <p className="text-center">Aucun score enregistré.</p>}
        </div>
    )
}