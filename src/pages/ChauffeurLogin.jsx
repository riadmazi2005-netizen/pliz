import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { createPageUrl } from '../src/utils';
import { authAPI } from '../services/apiService';
import LoginForm from '../components/ui/LoginForm';
import { Bus } from 'lucide-react';

export default function ChauffeurLogin() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleLogin = async (formData) => {
    setLoading(true);
    setError('');
    
    try {
      // Appel à l'API d'authentification
      const response = await authAPI.login(formData.email, formData.password, 'chauffeur');
      
      if (response.success) {
        // Sauvegarder la session du chauffeur
        localStorage.setItem('chauffeur_session', JSON.stringify(response.user));
        
        // Sauvegarder le token si fourni
        if (response.token) {
          authAPI.saveUser(response.user, response.token);
        }
        
        // Rediriger vers le dashboard
        navigate(createPageUrl('ChauffeurDashboard'));
      } else {
        setError(response.message || 'Erreur de connexion');
      }
    } catch (err) {
      console.error('Erreur de connexion:', err);
      setError(err.message || 'Erreur de connexion au serveur');
    } finally {
      setLoading(false);
    }
  };

  return (
    <LoginForm
      title="Espace Chauffeur"
      icon={Bus}
      onLogin={handleLogin}
      showRegister={false}
      loading={loading}
      error={error}
    />
  );
}