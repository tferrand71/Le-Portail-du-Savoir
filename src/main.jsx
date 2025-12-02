import React from 'react'
import ReactDOM from 'react-dom/client'
import { HashRouter } from 'react-router-dom' // <--- On importe HashRouter
import App from './App.jsx'
import './index.css'

ReactDOM.createRoot(document.getElementById('root')).render(
    <React.StrictMode>
        <HashRouter> {/* <--- On utilise HashRouter ici */}
            <App />
        </HashRouter>
    </React.StrictMode>,
)