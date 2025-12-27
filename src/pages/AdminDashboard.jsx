import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { createPageUrl } from '../utils';
import { elevesAPI, busAPI, chauffeursAPI, responsablesAPI, paiementsAPI, accidentsAPI, demandesAPI, notificationsAPI } from '../services/apiService';
import { motion } from 'framer-motion';
import { Button } from "@/components/ui/button";
import { 
  Shield, Bell, LogOut, Bus, Users, UserCog, 
  CreditCard, AlertCircle, BarChart3, ClipboardList, FileText
} from 'lucide-react';
import NotificationPanel from '../components/ui/NotificationPanel';
import StatCard from '../components/ui/StatCard';

export default function AdminDashboard() {
  const navigate = useNavigate();
  const [admin, setAdmin] = useState(null);
  const [stats, setStats] = useState({
    eleves: 0,
    bus: 0,
    chauffeurs: 0,
    responsables: 0,
    inscriptionsEnAttente: 0,
    paiementsEnAttente: 0,
    accidents: 0,
    demandes: 0
  });
  const [notifications, setNotifications] = useState([]);
  const [showNotifications, setShowNotifications] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const session = localStorage.getItem('admin_session');
    if (!session) {
      navigate(createPageUrl('AdminLogin'));
      return;
    }
    
    const adminData = JSON.parse(session);
    setAdmin(adminData);
    loadData(adminData.id);
  }, [navigate]);

  const loadData = async (adminId) => {
    try {
      const [eleves, buses, chauffeurs, responsables, paiements, accidents, demandes, notifs] = await Promise.all([
        elevesAPI.getAll(),
        busAPI.getAll(),
        chauffeursAPI.getAll(),
        responsablesAPI.getAll(),
        paiementsAPI.getAll(),
        accidentsAPI.getAll(),
        demandesAPI.getAll(),
        notificationsAPI.getByUser(adminId, 'admin')
      ]);

      setStats({
        eleves: eleves.length,
        bus: buses.length,
        chauffeurs: chauffeurs.length,
        responsables: responsables.length,
        inscriptionsEnAttente: eleves.filter(e => e.statut === 'Inactif').length,
        paiementsEnAttente: paiements.filter(p => p.statut === 'En attente').length,
        accidents: accidents.length,
        demandes: demandes.filter(d => d.statut === 'En attente').length
      });

      setNotifications(notifs.sort((a, b) => new Date(b.date) - new Date(a.date)));
    } catch (err) {
      console.error('Erreur lors du chargement des données:', err);
    }
    setLoading(false);
  };

  const handleLogout = () => {
    localStorage.removeItem('admin_session');
    navigate(createPageUrl('Home'));
  };

  const markNotificationAsRead = async (notifId) => {
    try {
      await notificationsAPI.marquerLue(notifId);
      setNotifications(prev => prev.map(n => n.id === notifId ? { ...n, lue: true } : n));
    } catch (err) {
      console.error('Erreur lors de la mise à jour de la notification:', err);
    }
  };

  const unreadCount = notifications.filter(n => !n.lue).length;

  const menuItems = [
    { 
      title: 'Inscriptions', 
      icon: ClipboardList, 
      link: 'AdminInscriptions',
      description: 'Gérer les demandes d\'inscription',
      badge: stats.inscriptionsEnAttente,
      color: 'from-blue-400 to-cyan-500'
    },
    { 
      title: 'Bus & Trajets', 
      icon: Bus, 
      link: 'AdminBus',
      description: 'Gérer les bus et trajets',
      color: 'from-amber-400 to-yellow-500'
    },
    { 
      title: 'Chauffeurs', 
      icon: Users, 
      link: 'AdminChauffeurs',
      description: 'Gérer les chauffeurs',
      color: 'from-green-400 to-emerald-500'
    },
    { 
      title: 'Responsables', 
      icon: UserCog, 
      link: 'AdminResponsables',
      description: 'Gérer les responsables bus',
      color: 'from-purple-400 to-violet-500'
    },
    { 
      title: 'Demandes', 
      icon: FileText, 
      link: 'AdminDemandes',
      description: 'Traiter les demandes',
      badge: stats.demandes,
      color: 'from-orange-400 to-red-500'
    },
    { 
      title: 'Accidents', 
      icon: AlertCircle, 
      link: 'AdminAccidents',
      description: 'Consulter les accidents',
      color: 'from-red-400 to-rose-500'
    },
    { 
      title: 'Statistiques', 
      icon: BarChart3, 
      link: 'AdminStats',
      description: 'Analyses et historiques',
      color: 'from-indigo-400 to-blue-500'
    },
    { 
      title: 'Paiements', 
      icon: CreditCard, 
      link: 'AdminPaiements',
      description: 'Suivi des paiements',
      color: 'from-teal-400 to-cyan-500'
    }
  ];

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="w-12 h-12 border-4 border-amber-500 border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-amber-50 via-white to-yellow-50 p-4 md:p-8">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <motion.div 
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          className="bg-white rounded-3xl shadow-xl p-6 mb-8"
        >
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div className="flex items-center gap-4">
              <div className="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg">
                <Shield className="w-8 h-8 text-white" />
              </div>
              <div>
                <h1 className="text-2xl font-bold text-gray-800">
                  Administration
                </h1>
                <p className="text-gray-500">{admin?.email}</p>
              </div>
            </div>
            
            <div className="flex gap-3">
              <Button
                variant="outline"
                onClick={() => setShowNotifications(true)}
                className="relative rounded-xl"
              >
                <Bell className="w-5 h-5" />
                {unreadCount > 0 && (
                  <span className="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                    {unreadCount}
                  </span>
                )}
              </Button>
              
              <Button 
                variant="ghost" 
                onClick={handleLogout}
                className="text-red-500 hover:text-red-600 hover:bg-red-50 rounded-xl"
              >
                <LogOut className="w-5 h-5" />
              </Button>
            </div>
          </div>
        </motion.div>

        {/* Stats */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-8">
          <StatCard 
            title="Élèves" 
            value={stats.eleves} 
            icon={Users} 
            color="blue"
          />
          <StatCard 
            title="Bus" 
            value={stats.bus} 
            icon={Bus} 
            color="amber"
          />
          <StatCard 
            title="Chauffeurs" 
            value={stats.chauffeurs} 
            icon={Users} 
            color="green"
          />
          <StatCard 
            title="Responsables" 
            value={stats.responsables} 
            icon={UserCog} 
            color="purple"
          />
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {menuItems.map((item, index) => (
            <motion.div
              key={item.title}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.05 }}
            >
              <Link to={createPageUrl(item.link)}>
                <div className="group bg-white rounded-3xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 relative overflow-hidden">
                  <div className={`absolute top-0 right-0 w-32 h-32 bg-gradient-to-br ${item.color} opacity-10 rounded-full transform translate-x-10 -translate-y-10 group-hover:scale-150 transition-transform duration-500`} />
                  
                  <div className="relative">
                    <div className={`w-14 h-14 rounded-2xl bg-gradient-to-br ${item.color} flex items-center justify-center mb-4 shadow-lg`}>
                      <item.icon className="w-7 h-7 text-white" />
                    </div>
                    
                    {item.badge > 0 && (
                      <span className="absolute top-0 right-0 w-6 h-6 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                        {item.badge}
                      </span>
                    )}
                    
                    <h3 className="text-lg font-bold text-gray-800 mb-1">{item.title}</h3>
                    <p className="text-sm text-gray-500">{item.description}</p>
                  </div>
                </div>
              </Link>
            </motion.div>
          ))}
        </div>
      </div>

      <NotificationPanel
        isOpen={showNotifications}
        onClose={() => setShowNotifications(false)}
        notifications={notifications}
        onMarkAsRead={markNotificationAsRead}
      />
    </div>
  );
}