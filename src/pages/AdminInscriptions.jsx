import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { createPageUrl } from '../utils';
import { elevesAPI, busAPI, trajetsAPI, tuteursAPI, inscriptionsAPI, notificationsAPI } from '../services/apiService';
import { motion } from 'framer-motion';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { 
  ClipboardList, ArrowLeft, Search, CheckCircle, XCircle, 
  Eye, User, Bus, MapPin, Filter
} from 'lucide-react';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';

export default function AdminInscriptions() {
  const navigate = useNavigate();
  const [eleves, setEleves] = useState([]);
  const [buses, setBuses] = useState([]);
  const [trajets, setTrajets] = useState([]);
  const [inscriptions, setInscriptions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [selectedEleve, setSelectedEleve] = useState(null);
  const [showVerifyModal, setShowVerifyModal] = useState(false);
  const [availableBuses, setAvailableBuses] = useState([]);

  useEffect(() => {
    const session = localStorage.getItem('admin_session');
    if (!session) {
      navigate(createPageUrl('AdminLogin'));
      return;
    }
    loadData();
  }, [navigate]);

  const loadData = async () => {
    try {
      const [elevesData, busesData, trajetsData, inscriptionsData, tuteursData] = await Promise.all([
        elevesAPI.getAll(),
        busAPI.getAll(),
        trajetsAPI.getAll(),
        inscriptionsAPI.getAll(),
        tuteursAPI.getAll()
      ]);
      
      // Enrichir les élèves avec les infos du tuteur et de l'inscription
      const elevesWithDetails = elevesData.map(e => {
        const tuteur = tuteursData.find(t => t.id === e.tuteur_id);
        const inscription = inscriptionsData.find(i => i.eleve_id === e.id);
        return {
          ...e,
          tuteur,
          inscription,
          statut_inscription: inscription?.statut || 'En attente'
        };
      });
      
      setEleves(elevesWithDetails);
      setBuses(busesData);
      setTrajets(trajetsData);
      setInscriptions(inscriptionsData);
    } catch (err) {
      console.error('Erreur lors du chargement:', err);
    }
    setLoading(false);
  };

  const handleValidate = async (eleve) => {
    try {
      // Créer ou mettre à jour l'inscription
      if (eleve.inscription) {
        await inscriptionsAPI.update(eleve.inscription.id, {
          statut: 'Active'
        });
      } else {
        await inscriptionsAPI.create({
          eleve_id: eleve.id,
          date_inscription: new Date().toISOString().split('T')[0],
          statut: 'Active'
        });
      }
      
      // Mettre à jour le statut de l'élève
      await elevesAPI.update(eleve.id, {
        statut: 'Actif'
      });
      
      // Envoyer notification au tuteur
      await notificationsAPI.create({
        destinataire_id: eleve.tuteur_id,
        destinataire_type: 'tuteur',
        titre: 'Inscription validée',
        message: `L'inscription de ${eleve.prenom} ${eleve.nom} a été validée. Veuillez procéder au paiement.`,
        type: 'info',
        date: new Date().toISOString()
      });
      
      loadData();
    } catch (err) {
      console.error('Erreur lors de la validation:', err);
    }
  };

  const handleRefuse = async (eleve, motif) => {
    try {
      if (eleve.inscription) {
        await inscriptionsAPI.update(eleve.inscription.id, {
          statut: 'Terminée'
        });
      }
      
      await elevesAPI.update(eleve.id, {
        statut: 'Inactif'
      });
      
      // Envoyer notification au tuteur
      await notificationsAPI.create({
        destinataire_id: eleve.tuteur_id,
        destinataire_type: 'tuteur',
        titre: 'Inscription refusée',
        message: `L'inscription de ${eleve.prenom} ${eleve.nom} a été refusée. Motif: ${motif}`,
        type: 'alerte',
        date: new Date().toISOString()
      });
      
      loadData();
    } catch (err) {
      console.error('Erreur lors du refus:', err);
    }
  };

  const handleVerifyZone = (eleve) => {
    setSelectedEleve(eleve);
    
    // Trouver les bus dont le trajet inclut cette zone
    // Note: Dans le SQL, il n'y a pas de champ "zone" pour les élèves
    // Il faudra l'ajouter ou utiliser l'adresse
    const matchingBuses = buses.filter(bus => {
      const trajet = trajets.find(t => t.id === bus.trajet_id);
      if (!trajet || !trajet.zones) return false;
      
      const zonesArray = Array.isArray(trajet.zones) ? trajet.zones : 
                         typeof trajet.zones === 'string' ? JSON.parse(trajet.zones) : [];
      
      // Ici, vous devrez adapter selon votre logique de zone
      return zonesArray.length > 0;
    }).map(bus => {
      const trajet = trajets.find(t => t.id === bus.trajet_id);
      const elevesInBus = inscriptions.filter(i => i.bus_id === bus.id).length;
      return {
        ...bus,
        trajet,
        placesRestantes: bus.capacite - elevesInBus
      };
    });
    
    setAvailableBuses(matchingBuses);
    setShowVerifyModal(true);
  };

  const handleAffectBus = async (busId) => {
    try {
      if (selectedEleve.inscription) {
        await inscriptionsAPI.update(selectedEleve.inscription.id, {
          bus_id: busId,
          statut: 'Active'
        });
      } else {
        await inscriptionsAPI.create({
          eleve_id: selectedEleve.id,
          bus_id: busId,
          date_inscription: new Date().toISOString().split('T')[0],
          statut: 'Active'
        });
      }
      
      // Notifier le tuteur
      const bus = buses.find(b => b.id === busId);
      await notificationsAPI.create({
        destinataire_id: selectedEleve.tuteur_id,
        destinataire_type: 'tuteur',
        titre: 'Affectation au bus',
        message: `${selectedEleve.prenom} ${selectedEleve.nom} a été affecté(e) au bus ${bus.numero}.`,
        type: 'info',
        date: new Date().toISOString()
      });
      
      setShowVerifyModal(false);
      setSelectedEleve(null);
      loadData();
    } catch (err) {
      console.error('Erreur lors de l\'affectation:', err);
    }
  };

  const filteredEleves = eleves.filter(e => {
    const matchSearch = 
      e.nom?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      e.prenom?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      e.adresse?.toLowerCase().includes(searchTerm.toLowerCase());
    
    const matchStatus = statusFilter === 'all' || e.statut === statusFilter;
    
    return matchSearch && matchStatus;
  });

  const getStatusBadge = (statut) => {
    const styles = {
      'Actif': 'bg-green-100 text-green-700',
      'Inactif': 'bg-yellow-100 text-yellow-700',
      'Active': 'bg-emerald-100 text-emerald-700',
      'Suspendue': 'bg-orange-100 text-orange-700',
      'Terminée': 'bg-gray-100 text-gray-700'
    };
    return styles[statut] || 'bg-gray-100 text-gray-700';
  };

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
        <button
          onClick={() => navigate(createPageUrl('AdminDashboard'))}
          className="flex items-center gap-2 text-gray-500 hover:text-amber-600 mb-6 transition-colors"
        >
          <ArrowLeft className="w-4 h-4" />
          Retour au tableau de bord
        </button>

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="bg-white rounded-3xl shadow-xl overflow-hidden"
        >
          <div className="p-6 bg-gradient-to-r from-blue-500 to-cyan-500">
            <h1 className="text-2xl font-bold text-white flex items-center gap-3">
              <ClipboardList className="w-7 h-7" />
              Gestion des Inscriptions
            </h1>
          </div>

          {/* Filters */}
          <div className="p-6 border-b border-gray-100">
            <div className="flex flex-col md:flex-row gap-4">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <Input
                  placeholder="Rechercher par nom, prénom ou adresse..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-10 h-12 rounded-xl"
                />
              </div>
              <Select value={statusFilter} onValueChange={setStatusFilter}>
                <SelectTrigger className="w-full md:w-48 h-12 rounded-xl">
                  <Filter className="w-4 h-4 mr-2" />
                  <SelectValue placeholder="Statut" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Tous les statuts</SelectItem>
                  <SelectItem value="Actif">Actif</SelectItem>
                  <SelectItem value="Inactif">En attente</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          {/* List */}
          <div className="divide-y divide-gray-100">
            {filteredEleves.length === 0 ? (
              <div className="p-12 text-center text-gray-400">
                <ClipboardList className="w-12 h-12 mx-auto mb-3 opacity-50" />
                <p>Aucune inscription trouvée</p>
              </div>
            ) : (
              filteredEleves.map((eleve) => (
                <div key={eleve.id} className="p-6 hover:bg-amber-50/50 transition-colors">
                  <div className="flex flex-col lg:flex-row justify-between gap-4">
                    <div className="flex items-start gap-4">
                      <div className="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center">
                        <User className="w-7 h-7 text-blue-500" />
                      </div>
                      <div>
                        <h3 className="font-semibold text-gray-800 text-lg">
                          {eleve.nom} {eleve.prenom}
                        </h3>
                        <div className="flex flex-wrap gap-2 mt-1 text-sm text-gray-500">
                          <span>{eleve.classe}</span>
                          <span>•</span>
                          <span className="flex items-center gap-1">
                            <MapPin className="w-3 h-3" />
                            {eleve.adresse}
                          </span>
                        </div>
                        {eleve.tuteur && (
                          <p className="text-sm text-gray-400 mt-1">
                            Tuteur: {eleve.tuteur.prenom} {eleve.tuteur.nom} ({eleve.tuteur.telephone})
                          </p>
                        )}
                      </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                      <span className={`px-4 py-2 rounded-xl text-sm font-medium ${getStatusBadge(eleve.statut)}`}>
                        {eleve.statut}
                      </span>

                      {eleve.statut === 'Inactif' && (
                        <>
                          <Button
                            onClick={() => handleValidate(eleve)}
                            className="bg-green-500 hover:bg-green-600 text-white rounded-xl"
                          >
                            <CheckCircle className="w-4 h-4 mr-2" />
                            Valider
                          </Button>
                          <Button
                            onClick={() => {
                              const motif = prompt('Motif du refus:');
                              if (motif) handleRefuse(eleve, motif);
                            }}
                            className="bg-red-500 hover:bg-red-600 text-white rounded-xl"
                          >
                            <XCircle className="w-4 h-4 mr-2" />
                            Refuser
                          </Button>
                        </>
                      )}

                      {eleve.statut === 'Actif' && !eleve.inscription?.bus_id && (
                        <Button
                          onClick={() => handleVerifyZone(eleve)}
                          className="bg-amber-500 hover:bg-amber-600 text-white rounded-xl"
                        >
                          <Bus className="w-4 h-4 mr-2" />
                          Affecter un bus
                        </Button>
                      )}
                    </div>
                  </div>
                </div>
              ))
            )}
          </div>
        </motion.div>
      </div>

      {/* Modal Verify & Affect */}
      {showVerifyModal && selectedEleve && (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
          <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            className="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto"
          >
            <div className="p-6 border-b border-gray-100">
              <h2 className="text-xl font-bold text-gray-800">
                Affectation de bus
              </h2>
              <p className="text-gray-500">
                Élève: {selectedEleve.prenom} {selectedEleve.nom}
              </p>
            </div>

            <div className="p-6">
              {availableBuses.length === 0 ? (
                <div className="text-center py-8 text-gray-400">
                  <Bus className="w-12 h-12 mx-auto mb-3 opacity-50" />
                  <p>Aucun bus disponible</p>
                  <p className="text-sm mt-1">Veuillez d'abord créer des bus et trajets</p>
                </div>
              ) : (
                <div className="space-y-4">
                  <p className="text-sm text-gray-600 mb-4">
                    Bus disponibles:
                  </p>
                  {availableBuses.map((bus) => (
                    <div 
                      key={bus.id}
                      className="p-4 rounded-2xl border-2 border-gray-100 hover:border-amber-200 transition-colors"
                    >
                      <div className="flex justify-between items-center">
                        <div>
                          <h3 className="font-bold text-gray-800 text-lg">{bus.numero}</h3>
                          <p className="text-sm text-gray-500">Immatriculation: {bus.immatriculation}</p>
                          <p className="text-sm text-gray-400">Trajet: {bus.trajet?.nom || 'Non assigné'}</p>
                        </div>
                        <div className="text-right">
                          <p className={`text-2xl font-bold ${
                            bus.placesRestantes > 0 ? 'text-green-500' : 'text-red-500'
                          }`}>
                            {bus.placesRestantes}
                          </p>
                          <p className="text-xs text-gray-400">places restantes</p>
                          {bus.placesRestantes > 0 && (
                            <Button
                              onClick={() => handleAffectBus(bus.id)}
                              className="mt-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl"
                              size="sm"
                            >
                              Affecter
                            </Button>
                          )}
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>

            <div className="p-6 border-t border-gray-100 flex justify-end">
              <Button
                variant="outline"
                onClick={() => {
                  setShowVerifyModal(false);
                  setSelectedEleve(null);
                }}
                className="rounded-xl"
              >
                Fermer
              </Button>
            </div>
          </motion.div>
        </div>
      )}
    </div>
  );
}