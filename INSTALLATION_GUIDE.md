# Installation Instructions - Sentiment Analysis System

## 🔧 PHP Dependencies

### Required Package
The SentimentAnalysisService uses Symfony's HttpClient component.

If not already installed, run:
```bash
cd c:\xampp\htdocs\Medecal\services-medical
composer require symfony/http-client:7.4.*
```

Or add manually to `composer.json` under "require":
```json
"symfony/http-client": "7.4.*"
```

Then run:
```bash
composer install
```

## 🐍 Python Dependencies

Install all required Python packages:

### Option 1: Using requirements.txt (Recommended)
```bash
cd c:\xampp\htdocs\Medecal\services-medical\flask-sentiment-api
pip install -r requirements.txt
```

### Option 2: Manual Installation
```bash
# Core packages
pip install flask==2.3.3
pip install flask-cors==4.0.0
pip install textblob==0.17.1
pip install vaderSentiment==3.3.2

# Download TextBlob data (required for text analysis)
python -m textblob.download_corpora
```

## ✅ Verification

### Check PHP Dependencies
```bash
php bin/console debug:container | grep http_client
```
Should show the HttpClient service is available.

### Check Python Dependencies
```bash
# In Python interpreter or script:
python -c "import flask; import flask_cors; import textblob; import vaderSentiment; print('✓ All packages installed')"
```

### Verify Flask API
```bash
cd flask-sentiment-api
python app.py
# Should show: 🚀 Flask Sentiment API Started
```

### Test PHP Service
```bash
php bin/console lint:container
# Should show: [OK] The container was linted successfully
```

## 📝 Configuration

### Environment Variables (Optional)
Create a `.env` file in the project root or add to existing:

```bash
# .env or .env.local
FLASK_API_URL=http://localhost:5000
FLASK_API_TIMEOUT=10
```

These are optional - defaults are already set in `SentimentAnalysisService`.

## 🧪 Full Setup Script

Run all setup steps at once:

```bash
# 1. Install PHP dependencies
cd c:\xampp\htdocs\Medecal\services-medical
composer require symfony/http-client:7.4.*

# 2. Install Python dependencies
cd flask-sentiment-api
pip install -r requirements.txt
python -m textblob.download_corpora

# 3. Run database migration
cd ..
php bin/console doctrine:migrations:migrate --no-interaction

# 4. Start Flask API (in new terminal)
cd flask-sentiment-api
python app.py

# 5. Verify everything works
python test_api.py
```

## 🚀 Now You're Ready!

Once all dependencies are installed:
1. Keep Flask API running: `python flask-sentiment-api/app.py`
2. Use the web app to add feedbacks
3. Sentiment scores are calculated automatically!

## 📋 Troubleshooting Dependencies

### "ModuleNotFoundError: No module named 'flask'"
```bash
pip install flask flask-cors
```

### "ModuleNotFoundError: No module named 'textblob'"
```bash
pip install textblob
python -m textblob.download_corpora
```

### PHP "Class HttpClientInterface not found"
```bash
composer require symfony/http-client:7.4.*
```

### "FLASK_API_URL not configured"
The service has a default: `http://localhost:5000`
If Flask runs elsewhere, set `FLASK_API_URL` environment variable.

## 📦 Version Compatibility

| Package | Version | Note |
|---------|---------|------|
| PHP | 8.2+ | Required by Symfony 7.4 |
| Python | 3.8+ | Tested with 3.9, 3.10, 3.11 |
| Symfony | 7.4 | See composer.json |
| Flask | 2.3.3 | Latest stable |
| TextBlob | 0.17.1 | For sentiment analysis |
| VADER | 3.3.2 | For advanced sentiment |

## ⚠️ Important Notes

1. **TextBlob Data**: Must download corpora after installation
   ```bash
   python -m textblob.download_corpora
   ```

2. **Python Virtual Environment** (Recommended):
   ```bash
   python -m venv venv
   venv\Scripts\activate  # Windows
   pip install -r requirements.txt
   ```

3. **Port 5000**: Flask uses port 5000 by default
   - If occupied, modify `flask-sentiment-api/app.py` last line
   - And update `FLASK_API_URL` in PHP service

4. **Firewall**: Ensure localhost:5000 is accessible from PHP
   - Usually not an issue on the same machine

## 🎯 Next Steps

After installation:
1. ✅ Start Flask API
2. ✅ Run migrations
3. ✅ Test with `test_api.py`
4. ✅ Add a feedback in the web app
5. ✅ See sentiment score calculated automatically!

---

**Need Help?**
- Check `SENTIMENT_ANALYSIS_SETUP.md` for full documentation
- Run `python test_api.py` to test the API
- Check Flask logs for errors
- Use `curl -X GET http://localhost:5000/health` to test connectivity
