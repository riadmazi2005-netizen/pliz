# Tests Rapides pour Vérifier le Fonctionnement

## ⚡ Tests Express (5 minutes)

### Test 1: Vérifier XAMPP (30 secondes)
```
✅ Ouvrir XAMPP Panneau de Contrôle
✅ Vérifier Apache = VERT (démarré)
✅ Vérifier MySQL = VERT (démarré)
```

**Si non vert → Cliquer sur "Start" pour chaque service**

---

### Test 2: Vérifier Backend Accessible (1 minute)
**Ouvrir dans le navigateur :**
```
http://localhost/backend/test.php
```

**Résultat attendu :**
```json
{
    "success": true,
    "message": "Backend accessible et base de données connectée",
    ...
}
```

**Si erreur 404 :**
- Le dossier `backend` n'est pas dans `C:\xampp\htdocs\`
- **ACTION :** Copier le dossier `backend` vers `C:\xampp\htdocs\backend`

**Si erreur 500 :**
- Problème de connexion à la base de données
- **ACTION :** Vérifier que MySQL est démarré et que la base `transport_scolaire` existe

---

### Test 3: Vérifier API Backend (1 minute)
**Ouvrir dans le navigateur :**
```
http://localhost/backend/api/test-connection.php
```

**Résultat attendu :**
```json
{
    "success": true,
    "message": "API backend accessible",
    ...
}
```

**Si erreur → Vérifier la structure des dossiers**

---

### Test 4: Tester l'Inscription via Console Navigateur (2 minutes)

1. **Ouvrir votre application frontend** (http://localhost:3000)
2. **Appuyer sur F12** pour ouvrir la console
3. **Aller dans l'onglet Console**
4. **Copier-coller ce code :**

```javascript
fetch('http://localhost/backend/api/auth/register.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    nom: 'Test',
    prenom: 'User',
    email: 'test' + Date.now() + '@test.com',
    mot_de_passe: 'test123',
    telephone: '0612345678',
    role: 'tuteur'
  })
})
.then(response => {
  console.log('Status:', response.status);
  return response.json();
})
.then(data => {
  console.log('✅ Success:', data);
})
.catch(error => {
  console.error('❌ Error:', error);
});
```

**Résultat attendu :**
```json
{
    "success": true,
    "message": "Inscription réussie. Vous pouvez maintenant vous connecter.",
    "user": { ... }
}
```

**Si erreur → Regarder le message dans la console pour identifier le problème**

---

### Test 5: Tester depuis l'Interface (1 minute)

1. **Aller sur la page d'inscription tuteur** : http://localhost:3000/TuteurRegister
2. **Remplir le formulaire**
3. **Soumettre**
4. **Regarder la console du navigateur (F12)**

**Messages à chercher :**
- `[API] POST http://localhost/backend/api/auth/register.php` ← La requête est envoyée
- `✅ Success:` ou `❌ Error:` ← Le résultat

---

## 🔍 Diagnostic des Erreurs Courantes

### Erreur: "Failed to fetch"

**Console affiche :**
```
[API] POST http://localhost/backend/api/auth/register.php
❌ Error: Impossible de se connecter au serveur...
```

**Solutions :**
1. ✅ Vérifier Test 1 (XAMPP démarré)
2. ✅ Vérifier Test 2 (Backend accessible)
3. ✅ Vérifier que le dossier est dans `C:\xampp\htdocs\backend`

---

### Erreur: "CORS policy"

**Console affiche :**
```
Access to fetch at 'http://localhost/backend/api/...' from origin 'http://localhost:3000' 
has been blocked by CORS policy
```

**Solution :**
- Vérifier que `backend/config/headers.php` contient les headers CORS
- Le fichier doit être inclus en premier dans tous les fichiers PHP API

---

### Erreur: "404 Not Found"

**Console affiche :**
```
[API] POST http://localhost/backend/api/auth/register.php
❌ Error: 404 Not Found
```

**Solution :**
- Vérifier que le fichier existe : `C:\xampp\htdocs\backend\api\auth\register.php`
- Vérifier la structure des dossiers

---

### Erreur: "500 Internal Server Error"

**Console affiche :**
```
❌ Error: 500 Internal Server Error
```

**Solution :**
1. Ouvrir : `C:\xampp\apache\logs\error.log`
2. Chercher la dernière erreur
3. Corriger le problème indiqué dans les logs

---

### Erreur: "Email déjà utilisé" (alors que ce n'est pas le cas)

**Cause :** Problème avec la base de données

**Solution :**
1. Ouvrir phpMyAdmin : http://localhost/phpmyadmin
2. Sélectionner la base `transport_scolaire`
3. Vérifier que la table `utilisateurs` existe
4. Vérifier la structure de la table

---

## 📋 Checklist Finale

Avant de tester l'inscription, vérifiez :

- [ ] XAMPP Apache démarré
- [ ] XAMPP MySQL démarré  
- [ ] `http://localhost/backend/test.php` fonctionne
- [ ] `http://localhost/backend/api/test-connection.php` fonctionne
- [ ] Base de données `transport_scolaire` existe
- [ ] Table `utilisateurs` existe dans la base
- [ ] Frontend démarre sans erreur (`npm run dev`)
- [ ] Console du navigateur ouverte (F12) pour voir les erreurs

---

## 🎯 Résultat des Tests

Si tous les tests passent :
✅ **Votre système fonctionne !** Vous pouvez créer des comptes tuteur.

Si un test échoue :
❌ **Notez le numéro du test qui échoue** et consultez la section "Diagnostic" ci-dessus.

---

## 💡 Astuce Pro

**Toujours garder la console du navigateur ouverte (F12)** pendant les tests pour voir les erreurs en temps réel !

Les messages `[API]` dans la console vous indiquent exactement ce qui se passe :
- ✅ Si vous voyez `[API] POST ...` → La requête est envoyée
- ✅ Si vous voyez `[API] Success:` → Tout fonctionne
- ❌ Si vous voyez `[API] Error:` → Regardez le message d'erreur

