import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { createPageUrl } from '../src/utils';
import { authAPI } from '../services/apiService';
import LoginForm from '../components/ui/LoginForm';
import { Shield } from 'lucide-react';

export default function AdminLogin() {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleLogin = async (formData) => {
    setLoading(true);
    setError('');
    
    try {
      const response = await authAPI.login(formData.email, formData.password, 'admin');
      
      if (response.success) {
        // Sauvegarder les informations de l'admin
        authAPI.saveUser(response.user, response.token);
        localStorage.setItem('admin_session', JSON.stringify(response.user));
        
        navigate(createPageUrl('AdminDashboard'));
      } else {
        setError(response.message || 'Email ou mot de passe incorrect');
      }
    } catch (err) {
      console.error('Erreur de connexion:', err);
      setError('Erreur de connexion au serveur');
    } finally {
      setLoading(false);
    }
  };

  return (
    <LoginForm
      title="Espace Administrateur"
      icon={Shield}
      onLogin={handleLogin}
      showRegister={false}
      loading={loading}
      error={error}
    />
  );
}