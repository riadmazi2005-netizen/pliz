import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { createPageUrl } from '../utils';
import { authAPI } from '../services/apiService';
import LoginForm from '../components/ui/LoginForm';
import { UserCog } from 'lucide-react';

export default function ResponsableLogin() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleLogin = async (formData) => {
    setLoading(true);
    setError('');
    
    try {
      // Utiliser l'API d'authentification
      const response = await authAPI.login(formData.email, formData.password, 'responsable');
      
      if (response.success) {
        // Sauvegarder la session
        authAPI.saveUser(response.user, response.token);
        localStorage.setItem('responsable_session', JSON.stringify(response.user));
        
        // Rediriger vers le dashboard
        navigate(createPageUrl('ResponsableDashboard'));
      } else {
        setError(response.message || 'Identifiants incorrects');
      }
    } catch (err) {
      console.error('Erreur de connexion:', err);
      setError('Erreur de connexion. Veuillez réessayer.');
    }
    
    setLoading(false);
  };

  return (
    <LoginForm
      title="Espace Responsable Bus"
      icon={UserCog}
      onLogin={handleLogin}
      showRegister={false}
      loading={loading}
      error={error}
      emailPlaceholder="Email ou téléphone"
    />
  );
}