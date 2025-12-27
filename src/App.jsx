// App.jsx
import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';

// Pages
import Home from './pages/Home';
import ChauffeurLogin from './pages/ChauffeurLogin';
import ChauffeurDashboard from './pages/ChauffeurDashboard';
import ResponsableLogin from './pages/ResponsableLogin';
import ResponsableDashboard from './pages/ResponsableDashboard';
import TuteurLogin from './pages/TuteurLogin';
import TuteurDashboard from './pages/TuteurDashboard';
import TuteurRegister from './pages/TuteurRegister';
import AdminLogin from './pages/AdminLogin';
import AdminDashboard from './pages/AdminDashboard';

function App() {
  return (
    <BrowserRouter>
      <Routes>
        {/* Home */}
        <Route path="/" element={<Home />} />
        <Route path="/Home" element={<Home />} />

        {/* Chauffeur Routes */}
        <Route path="/ChauffeurLogin" element={<ChauffeurLogin />} />
        <Route path="/ChauffeurDashboard" element={<ChauffeurDashboard />} />

        {/* Responsable Routes */}
        <Route path="/ResponsableLogin" element={<ResponsableLogin />} />
        <Route path="/ResponsableDashboard" element={<ResponsableDashboard />} />

        {/* Tuteur Routes */}
        <Route path="/TuteurLogin" element={<TuteurLogin />} />
        <Route path="/TuteurRegister" element={<TuteurRegister />} />
        <Route path="/TuteurDashboard" element={<TuteurDashboard />} />

        {/* Admin Routes */}
        <Route path="/AdminLogin" element={<AdminLogin />} />
        <Route path="/AdminDashboard" element={<AdminDashboard />} />

        {/* Catch all - redirect to home */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;