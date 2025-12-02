import { Routes, Route } from 'react-router-dom'
import Layout from './components/Layout'
import Home from './pages/Home'
import Login from './pages/Login'
import Game from './pages/Game'
import Manage from './pages/Manage'
import Scores from './pages/Scores'
import { AuthProvider } from './context/AuthContext'

function App() {
    return (
        <AuthProvider>
            <Routes>
                <Route element={<Layout />}>
                    <Route path="/" element={<Home />} />
                    <Route path="/login" element={<Login />} />
                    <Route path="/game/:subject" element={<Game />} />
                    <Route path="/manage/:subject" element={<Manage />} />
                    <Route path="/scores" element={<Scores />} />
                </Route>
            </Routes>
        </AuthProvider>
    )
}

export default App