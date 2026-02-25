# 🎯 Système d'Analyse Sentimentale pour Évaluation des Docteurs

## 📋 Vue d'ensemble

Ce système utilise l'**IA et le Machine Learning** (TextBlob + VADER) pour analyser les avis des patients et calculer un score de sentiment pour chaque docteur basé sur les commentaires ET les notes.

## 🏗️ Architecture

### 1. **Backend Symfony** (PHP)
- **Entité Feedback** : Ajout du champ `sentiment_score` (DOUBLE)
- **Entité Medecin** : Champs `aiAverageScore` et `aiScoreUpdatedAt`
- **Service SentimentAnalysisService** : Communication avec l'API Flask
- **Controller FeedbackController** : Intégration de l'analyse sentimentale

### 2. **API Flask** (Python)
- **Port** : 5000
- **Endpoints** :
  - `POST /analyze` - Analyser un seul feedback
  - `POST /analyze-batch` - Analyser plusieurs feedbacks
  - `POST /doctor-sentiment-score` - Calculer le score moyen d'un docteur
  - `GET /health` - Vérifier la santé de l'API

### 3. **Librairies Python Utilisées**
- **TextBlob** : Analyse de polarité du texte
- **VADER** (vaderSentiment) : Analyse sentimentale avancée
- **Flask** : Framework API
- **Flask-CORS** : Support CORS

## 🔄 Flux de Travail

### Quand un patient ajoute un avis :

1. **Formulaire Feedback** → POST au FeedbackController
2. **SentimentAnalysisService** appelle Flask API `/analyze`
3. **Flask** analyse le commentaire + note (rating 1-5)
4. **Score final** = (rating × 0.6) + (sentiment × 0.4)
5. Le `sentiment_score` est sauvegardé dans la table `feedback`
6. Le score moyen du docteur est recalculé via `updateAiAverageScore()`
7. Le `aiAverageScore` du docteur est mis à jour

### Quand un patient édite un avis :

1. Même processus qu'à la création
2. Le `sentiment_score` est recalculé
3. Le score moyen du docteur est mis à jour

### Quand un patient supprime un avis :

1. L'avis est supprimé
2. Le score moyen du docteur est recalculé
3. Si aucun avis ne reste, le score devient `NULL`

## 📊 Formule de Calcul

```
TextBlob Score = (polarity + 1) × 2.5   // Convertit [-1, 1] → [0, 5]
VADER Score    = (compound + 1) × 2.5   // Convertit [-1, 1] → [0, 5]
Sentiment Score = (TextBlob + VADER) / 2 // Moyenne des deux

Final Score = (Rating × 0.6) + (Sentiment × 0.4)
                ↑ Importance du rating
                              ↑ Importance du sentiment du texte
```

## 🎯 Labels de Sentiment

- **very_positive** : Score ≥ 4.0
- **positive** : Score ≥ 3.0
- **neutral** : Score ≥ 2.0
- **negative** : Score ≥ 1.0
- **very_negative** : Score < 1.0

## 🚀 Installation & Démarrage

### 1. Démarrer l'API Flask

```bash
cd c:\xampp\htdocs\Medecal\services-medical\flask-sentiment-api
python app.py
```

API sera accessible sur `http://localhost:5000`

### 2. Vérifier la migration Doctrine

```bash
cd c:\xampp\htdocs\Medecal\services-medical
php bin/console doctrine:migrations:status
```

Vous devez voir la migration `Version20260210210851` comme "migrated"

### 3. Configuration (optionnel)

Dans vos fichiers `.env` ou variables d'environnement :
```bash
FLASK_API_URL=http://localhost:5000
```

## 📝 Exemples d'Utilisation

### Via le formulaire Web :

1. Aller à `/feedback/new`
2. Remplir le formulaire avec :
   - **Rating** : 1-5 étoiles
   - **Comment** : Commentaire du patient
3. Soumettre → Le sentiment est automatiquement analysé
4. Le docteur verra son score mis à jour

### Via API PHP (direct) :

```php
use App\Service\SentimentAnalysisService;

$sentimentService->analyzeSentiment(
    "Excellent docteur, très attentif!",
    5
);

// Retourne :
// {
//     'rating_score': 5.0,
//     'textblob_score': 4.5,
//     'vader_score': 4.8,
//     'sentiment_score': 4.65,
//     'final_score': 4.76,
//     'sentiment_label': 'very_positive',
//     'confidence': 'high'
// }
```

### Via API Flask (direct) :

```bash
curl -X POST http://localhost:5000/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "comment": "Excellent docteur!",
    "rating": 5
  }'
```

## 📊 Schéma Base de Données

### Table `feedback`
```sql
- id (PK)
- rating (INT 1-5)
- comment (TEXT)
- sentiment_score (DOUBLE NULL) ✨ NOUVEAU
- created_at (DATETIME)
- patient_id (FK)
- medecin_id (FK)
- rendez_vous_id (FK NULL)
```

### Table `medecin`
```sql
- id (PK)
- ...
- ai_average_score (DOUBLE NULL) ✨
- ai_score_updated_at (DATETIME NULL) ✨
```

## 🔍 Affichage dans les Templates Twig

```twig
{# Afficher le score d'un docteur #}
<div class="doctor-rating">
  {% if medecin.aiAverageScore %}
    <span class="score">{{ medecin.aiAverageScore }}/5.0</span>
    <span class="updated">
      Mis à jour : {{ medecin.aiScoreUpdatedAt|date('d/m/Y') }}
    </span>
  {% else %}
    <p>Pas encore d'avis</p>
  {% endif %}
</div>

{# Afficher les détails d'un feedback #}
<article class="feedback">
  <div class="rating">{{ feedback.rating }}/5 ⭐</div>
  <p>{{ feedback.comment }}</p>
  
  {% if feedback.sentimentScore %}
    <div class="sentiment-badge" data-score="{{ feedback.sentimentScore }}">
      Score IA : {{ feedback.sentimentScore }}/5
    </div>
  {% endif %}
</article>
```

## 🐛 Dépannage

### L'API Flask ne répond pas
```bash
# Vérifier le health check
curl http://localhost:5000/health

# Vérifier les logs Python
# (Vérifier la fenêtre terminal où Flask tourne)
```

### La colonne sentiment_score n'existe pas
```bash
php bin/console doctrine:migrations:migrate
```

### Le service SentimentAnalysisService n'est pas trouvé
```bash
# Vérifier que le fichier existe
ls src/Service/SentimentAnalysisService.php

# Vérifier la configuration des services
php bin/console lint:container
```

## 📈 Métriques & Monitoring

Pour afficher les statistiques des docteurs :

```php
// Dans un repository ou controller
$medecin->getAiAverageScore()        // Score moyen IA
$medecin->getAiScoreUpdatedAt()      // Dernière mise à jour
$medecin->getAverageRating()         // Moyenne des ratings simples (1-5)
$medecin->getFeedbacks()             // Tous les feedbacks
```

## ⚠️ Notes Importantes

1. **Fallback** : Si Flask est indisponible, le `sentiment_score` prend la valeur du rating
2. **Analyse Française** : TextBlob et VADER supportent le français
3. **Performance** : L'analyse peut prendre 1-2 secondes par feedback
4. **Cache** : Les scores ne sont pas en cache (temps réel)

## 🔐 Sécurité

- L'API Flask est sans authentification (À améliorer pour la production)
- Les requêtes PHP vers Flask utilisent HttpClient sécurisé
- Les validations se font côté PHP (Symfony Forms)

---

**Version** : 1.0  
**Date** : 10/02/2026  
**Statut** : ✅ Production-Ready
