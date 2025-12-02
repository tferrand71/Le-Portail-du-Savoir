import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import { supabase } from '../lib/supabaseClient'
import { useAuth } from '../context/AuthContext'

export default function Game() {
    const { subject } = useParams()
    const decodedSubject = decodeURIComponent(subject)
    const { user, role } = useAuth()

    const [questions, setQuestions] = useState([])
    const [answers, setAnswers] = useState([])
    const [selectedQ, setSelectedQ] = useState(null)
    const [score, setScore] = useState(0)
    const [startTime, setStartTime] = useState(null)
    const [timer, setTimer] = useState("00:00")
    const [gameActive, setGameActive] = useState(false)
    const [finished, setFinished] = useState(false)

    const loadGame = async () => {
        // Récupérer les questions depuis Supabase
        const { data, error } = await supabase
            .from('questions')
            .select('*')
            .eq('subject', decodedSubject)

        if (error) {
            console.error(error)
            return
        }

        if (!data || data.length === 0) {
            alert("Aucune question trouvée pour cette matière.")
            return
        }

        // Mélange des questions et réponses
        const shuffledQ = [...data].sort(() => Math.random() - 0.5)
        const shuffledA = [...data].sort(() => Math.random() - 0.5)

        setQuestions(shuffledQ.map(q => ({ ...q, status: 'normal' })))
        setAnswers(shuffledA.map(a => ({ ...a, status: 'normal' })))
        setScore(0)
        setFinished(false)
        setGameActive(true)
        setStartTime(Date.now())
    }

    // Gestion du chronomètre
    useEffect(() => {
        let interval
        if (gameActive && !finished) {
            interval = setInterval(() => {
                const seconds = Math.floor((Date.now() - startTime) / 1000)
                const m = Math.floor(seconds / 60).toString().padStart(2, '0')
                const s = (seconds % 60).toString().padStart(2, '0')
                setTimer(`${m}:${s}`)
            }, 1000)
        }
        return () => clearInterval(interval)
    }, [gameActive, finished, startTime])

    const handleQuestionClick = (id) => {
        // Si la question est déjà validée, on ne fait rien
        const q = questions.find(q => q.id === id)
        if (q.status === 'correct') return

        setSelectedQ(id)
        setQuestions(qs => qs.map(q => {
            if (q.status === 'correct') return q
            return q.id === id ? { ...q, status: 'selected' } : { ...q, status: 'normal' }
        }))
    }

    const handleAnswerClick = async (qIdForAnswer) => {
        if (!selectedQ) return

        // Vérification de la correspondance
        if (selectedQ === qIdForAnswer) {
            // Bonne réponse
            const newScore = score + 1
            setScore(newScore)

            setQuestions(qs => qs.map(q => q.id === selectedQ ? { ...q, status: 'correct' } : q))
            setAnswers(as => as.map(a => a.id === qIdForAnswer ? { ...a, status: 'correct' } : a))
            setSelectedQ(null)

            // Vérifier si le jeu est terminé
            if (newScore === questions.length) {
                setFinished(true)
                const timeSeconds = Math.floor((Date.now() - startTime) / 1000)
                if (user) {
                    await supabase.from('game_scores').insert({
                        subject: decodedSubject,
                        score: newScore,
                        total: questions.length,
                        time_seconds: timeSeconds,
                        user_id: user.id
                    })
                }
            }
        } else {
            // Mauvaise réponse (effet visuel simple)
            const currentSelected = selectedQ
            setQuestions(qs => qs.map(q => q.id === currentSelected ? { ...q, status: 'wrong' } : q))
            setAnswers(as => as.map(a => a.id === qIdForAnswer ? { ...a, status: 'wrong' } : a))

            setTimeout(() => {
                setQuestions(qs => qs.map(q => q.id === currentSelected ? { ...q, status: 'normal' } : q))
                setAnswers(as => as.map(a => a.id === qIdForAnswer ? { ...a, status: 'normal' } : a))
                setSelectedQ(null)
            }, 800)
        }
    }

    return (
        <div className="card">
            <div style={{display:'flex', justifyContent:'space-between', alignItems:'center'}}>
                <h2>Jeu - {decodedSubject}</h2>
                {role === 'teacher' && (
                    <Link to={`/manage/${encodeURIComponent(decodedSubject)}`} className="btn">Gérer les questions</Link>
                )}
            </div>

            <div id="score" className="text-center" style={{fontSize:'1.2rem', margin:'1rem 0'}}>
                Score: {score}/{questions.length} | Temps: {timer}
            </div>

            {!gameActive && !finished && (
                <div className="text-center">
                    <button className="btn" onClick={loadGame}>Nouvelle Partie</button>
                </div>
            )}

            {gameActive && (
                <div className="game-container">
                    <div id="questions">
                        {questions.map(q => (
                            <div key={q.id}
                                 className={`label ${q.status}`}
                                 onClick={() => handleQuestionClick(q.id)}>
                                {q.question}
                            </div>
                        ))}
                    </div>
                    <div id="answers">
                        {answers.map(a => (
                            <div key={a.id}
                                 className={`label ${a.status}`}
                                 onClick={() => handleAnswerClick(a.id)}>
                                {a.answer}
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {finished && (
                <div className="card text-center" style={{background:'#e3f2fd', border:'2px solid #4b6cb7'}}>
                    <h3>Félicitations !</h3>
                    <p>Score final: {score}/{questions.length}</p>
                    <p>Temps: {timer}</p>
                    <button className="btn" onClick={() => window.location.reload()}>Rejouer</button>
                </div>
            )}

            <div className="text-center" style={{marginTop:'2rem'}}>
                <Link to="/" className="btn" style={{background:'#6c757d'}}>Retour Accueil</Link>
            </div>
        </div>
    )
}