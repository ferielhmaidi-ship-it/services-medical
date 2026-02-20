# 🚀 Commands to Execute - Sentiment Analysis System

Copy-paste these commands to get everything running!

═══════════════════════════════════════════════════════════════

## STEP 1: Navigate to Project Directory

```bash
cd c:\xampp\htdocs\Medecal\services-medical
```

## STEP 2: Install PHP Dependency (REQUIRED)

```bash
composer require symfony/http-client:7.4.*
```

**OR if composer is on PATH:**

```bash
composer install
```

## STEP 3: Python Setup (OPTIONAL - if not done yet)

```bash
cd flask-sentiment-api

# Install dependencies
pip install -r requirements.txt

# Download TextBlob data (IMPORTANT!)
python -m textblob.download_corpora

cd ..
```

## STEP 4: Database Migration (if not done)

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

Check status:
```bash
php bin/console doctrine:migrations:status
```

You should see: `Version20260210210851` marked as "Executed at"

## STEP 5: Start Flask API (in NEW terminal)

```bash
cd c:\xampp\htdocs\Medecal\services-medical\flask-sentiment-api
python app.py
```

**Expected output:**
```
🚀 Flask Sentiment API Started
📊 Endpoints:
   GET  /health
   POST /analyze - Analyze single feedback
   POST /analyze-batch - Analyze multiple feedbacks
   POST /doctor-sentiment-score - Calculate doctor's avg sentiment score
 * Running on http://0.0.0.0:5000
```

## STEP 6: Verify Everything Works

**In a NEW terminal, test API:**

```bash
# Test health
curl http://localhost:5000/health

# Test single analysis
curl -X POST http://localhost:5000/analyze \
  -H "Content-Type: application/json" \
  -d "{\"comment\":\"Excellent doctor!\",\"rating\":5}"

# Run full test suite
cd c:\xampp\htdocs\Medecal\services-medical\flask-sentiment-api
python test_api.py
```

**Or use PowerShell:**

```powershell
# Test health
Invoke-WebRequest -Uri "http://localhost:5000/health" -Method Get

# Test analysis
$body = @{comment="Excellent!"; rating=5} | ConvertTo-Json
Invoke-WebRequest -Uri "http://localhost:5000/analyze" -Method POST `
  -ContentType "application/json" -Body $body
```

## STEP 7: Access Web Application

1. Start XAMPP (Apache + MySQL)
2. Open: http://localhost/Medecal/services-medical/public
3. Navigate to feedback section
4. Add a feedback → sentiment score calculated automatically! ✅

═══════════════════════════════════════════════════════════════

## 🧪 Testing Commands

### Quick Test
```bash
curl http://localhost:5000/health
```

### Full Test Suite
```bash
python flask-sentiment-api/test_api.py
```

### Test Single Endpoint
```bash
curl -X POST http://localhost:5000/analyze \
  -H "Content-Type: application/json" \
  -d '{"comment":"Very good doctor","rating":5}'
```

### Test Batch
```bash
curl -X POST http://localhost:5000/analyze-batch \
  -H "Content-Type: application/json" \
  -d '{"feedbacks":[{"comment":"Good","rating":4},{"comment":"Excellent","rating":5}]}'
```

### Test Doctor Score
```bash
curl -X POST http://localhost:5000/doctor-sentiment-score \
  -H "Content-Type: application/json" \
  -d '{"feedbacks":[{"comment":"Good","rating":4},{"comment":"Excellent","rating":5}]}'
```

═══════════════════════════════════════════════════════════════

## 📊 Database Verification

```bash
# Access MySQL
mysql -u root

# Use database
USE medical; -- or your database name

# Check table structure
DESCRIBE feedback;
# Should show: sentiment_score column (DOUBLE)

DESCRIBE medecin;
# Should show: ai_average_score, ai_score_updated_at columns

# View existing data
SELECT id, rating, sentiment_score FROM feedback LIMIT 5;
SELECT id, firstName, ai_average_score FROM medecin WHERE ai_average_score IS NOT NULL;
```

═══════════════════════════════════════════════════════════════

## 🔍 Verification Checklist

After running the commands, verify:

```bash
# 1. PHP container is valid
php bin/console lint:container
# Should show: [OK]

# 2. Database migration is applied
php bin/console doctrine:migrations:status
# Should show Version20260210210851 as "Executed at"

# 3. Flask API is running
curl -s http://localhost:5000/health | jq .
# Should show: {"status": "ok", "message": "API running"}

# 4. TextBlob is installed
python -c "from textblob import TextBlob; print('✓ TextBlob ready')"

# 5. VADER is installed
python -c "from vaderSentiment.vaderSentiment import SentimentIntensityAnalyzer; print('✓ VADER ready')"
```

═══════════════════════════════════════════════════════════════

## ⚠️ Troubleshooting Commands

### If "symfony/http-client not found"
```bash
composer require symfony/http-client:7.4.*
php bin/console cache:clear
```

### If Flask API won't start
```bash
# Check if port 5000 is already in use
netstat -ano | findstr "5000"

# Kill process on port 5000 (Windows)
netsh int ipv4 show tcpstats
taskkill /PID <PID> /F

# Or change Flask port in app.py line
# app.run(debug=True, host='0.0.0.0', port=5001)  # Changed to 5001
```

### If sentiment_score column doesn't exist
```bash
php bin/console doctrine:migrations:migrate
# Check status
php bin/console doctrine:migrations:status
```

### If Python packages missing
```bash
pip install flask flask-cors textblob vaderSentiment
pip install -r flask-sentiment-api/requirements.txt  # Better
python -m textblob.download_corpora
```

### To clear database and start fresh
```bash
# WARNING: This deletes all data!
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

═══════════════════════════════════════════════════════════════

## 📝 Final Checklist

After setup, run these to verify everything:

```bash
# Terminal 1: Flask API
cd flask-sentiment-api && python app.py

# Terminal 2: Verify installation
cd .. && php bin/console lint:container

# Terminal 3: Test API
curl http://localhost:5000/health

# Terminal 4: Run tests
python flask-sentiment-api/test_api.py
```

All should show ✅

═══════════════════════════════════════════════════════════════

## 🎯 You're Ready!

If all commands executed successfully:
1. ✅ Flask API running on http://localhost:5000
2. ✅ Database migrated
3. ✅ PHP dependencies installed
4. ✅ Python packages ready
5. ✅ System fully functional

Add a feedback in the web app and watch the sentiment analysis work!

═══════════════════════════════════════════════════════════════

## 📚 Additional Useful Commands

```bash
# Clear Symfony cache
php bin/console cache:clear

# Generate new migration (if you make entity changes)
php bin/console make:migration

# Run only new migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Check available services
php bin/console debug:container | grep sentiment

# View logs
tail -f var/log/dev.log

# Run Symfony dev server
php bin/console server:run

# Check Python version
python --version

# List installed Python packages
pip list | grep -E "flask|textblob|vader"

# Update Python packages
pip install --upgrade -r flask-sentiment-api/requirements.txt
```

═══════════════════════════════════════════════════════════════

Ready? Start with:

```bash
python flask-sentiment-api/app.py
```

Enjoy! 🎉
