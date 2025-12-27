import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Bell, X, CheckCircle, AlertCircle, Info, Trash2 } from 'lucide-react';
import { Button } from "@/components/ui/button";
import { formatDate } from "../../utils";

export default function NotificationPanel({ 
  notifications = [], 
  onMarkAsRead, 
  onDelete,
  onClose 
}) {
  const [filter, setFilter] = useState('all'); // all, unread, read

  const filteredNotifications = notifications.filter(notif => {
    if (filter === 'unread') return !notif.lue;
    if (filter === 'read') return notif.lue;
    return true;
  });

  const unreadCount = notifications.filter(n => !n.lue).length;

  const getNotificationIcon = (type) => {
    switch(type) {
      case 'alerte':
        return <AlertCircle className="w-5 h-5 text-red-500" />;
      case 'avertissement':
        return <AlertCircle className="w-5 h-5 text-orange-500" />;
      default:
        return <Info className="w-5 h-5 text-blue-500" />;
    }
  };

  const getNotificationColor = (type) => {
    switch(type) {
      case 'alerte':
        return 'border-l-red-500 bg-red-50';
      case 'avertissement':
        return 'border-l-orange-500 bg-orange-50';
      default:
        return 'border-l-blue-500 bg-blue-50';
    }
  };

  return (
    <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <motion.div
        initial={{ opacity: 0, scale: 0.95, y: 20 }}
        animate={{ opacity: 1, scale: 1, y: 0 }}
        exit={{ opacity: 0, scale: 0.95, y: 20 }}
        className="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-hidden"
      >
        {/* Header */}
        <div className="bg-gradient-to-r from-amber-500 to-yellow-500 p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                <Bell className="w-6 h-6 text-white" />
              </div>
              <div>
                <h2 className="text-2xl font-bold text-white">Notifications</h2>
                <p className="text-white/80 text-sm">{unreadCount} non lue(s)</p>
              </div>
            </div>
            <button
              onClick={onClose}
              className="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center transition-colors"
            >
              <X className="w-5 h-5 text-white" />
            </button>
          </div>

          {/* Filters */}
          <div className="flex gap-2">
            {['all', 'unread', 'read'].map(f => (
              <button
                key={f}
                onClick={() => setFilter(f)}
                className={`px-4 py-2 rounded-xl text-sm font-medium transition-all ${
                  filter === f
                    ? 'bg-white text-amber-600 shadow-lg'
                    : 'bg-white/20 text-white hover:bg-white/30'
                }`}
              >
                {f === 'all' ? 'Toutes' : f === 'unread' ? 'Non lues' : 'Lues'}
                {f === 'unread' && unreadCount > 0 && (
                  <span className="ml-2 px-2 py-0.5 bg-red-500 text-white rounded-full text-xs">
                    {unreadCount}
                  </span>
                )}
              </button>
            ))}
          </div>
        </div>

        {/* Notifications List */}
        <div className="overflow-y-auto max-h-[calc(80vh-200px)] p-6 space-y-3">
          <AnimatePresence>
            {filteredNotifications.length === 0 ? (
              <div className="text-center py-12 text-gray-400">
                <Bell className="w-12 h-12 mx-auto mb-3 opacity-50" />
                <p>Aucune notification</p>
              </div>
            ) : (
              filteredNotifications.map((notif, index) => (
                <motion.div
                  key={notif.id}
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: 20 }}
                  transition={{ delay: index * 0.05 }}
                  className={`relative border-l-4 rounded-xl p-4 transition-all ${
                    getNotificationColor(notif.type)
                  } ${!notif.lue ? 'shadow-md' : 'opacity-75'}`}
                >
                  <div className="flex items-start gap-3">
                    <div className="flex-shrink-0 mt-1">
                      {getNotificationIcon(notif.type)}
                    </div>
                    
                    <div className="flex-1 min-w-0">
                      <div className="flex items-start justify-between gap-2">
                        <h4 className="font-semibold text-gray-800 text-sm">
                          {notif.titre}
                        </h4>
                        {!notif.lue && (
                          <div className="w-2 h-2 bg-amber-500 rounded-full flex-shrink-0 mt-1" />
                        )}
                      </div>
                      
                      <p className="text-gray-600 text-sm mt-1 leading-relaxed">
                        {notif.message}
                      </p>
                      
                      <div className="flex items-center justify-between mt-3">
                        <p className="text-xs text-gray-400">
                          {formatDate(notif.date)}
                        </p>
                        
                        <div className="flex gap-2">
                          {!notif.lue && (
                            <button
                              onClick={() => onMarkAsRead(notif.id)}
                              className="text-xs text-green-600 hover:text-green-700 font-medium flex items-center gap-1"
                            >
                              <CheckCircle className="w-3 h-3" />
                              Marquer comme lue
                            </button>
                          )}
                          
                          <button
                            onClick={() => onDelete(notif.id)}
                            className="text-xs text-red-600 hover:text-red-700 font-medium flex items-center gap-1"
                          >
                            <Trash2 className="w-3 h-3" />
                            Supprimer
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </motion.div>
              ))
            )}
          </AnimatePresence>
        </div>

        {/* Footer */}
        {filteredNotifications.length > 0 && (
          <div className="p-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
            <p className="text-sm text-gray-500">
              {filteredNotifications.length} notification(s)
            </p>
            {unreadCount > 0 && (
              <Button
                variant="ghost"
                size="sm"
                onClick={() => {
                  filteredNotifications
                    .filter(n => !n.lue)
                    .forEach(n => onMarkAsRead(n.id));
                }}
                className="text-amber-600 hover:text-amber-700"
              >
                <CheckCircle className="w-4 h-4 mr-2" />
                Tout marquer comme lu
              </Button>
            )}
          </div>
        )}
      </motion.div>
    </div>
  );
}