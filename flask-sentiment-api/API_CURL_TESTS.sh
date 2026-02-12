# Sentiment Analysis API - cURL Test Examples
# Usage: Copy and paste these commands in your terminal (PowerShell or bash)

# 1. Health Check
echo "1. Testing API Health..."
curl -X GET http://localhost:5000/health | jq .

# 2. Single Feedback Analysis
echo "2. Analyzing single feedback..."
curl -X POST http://localhost:5000/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "comment": "Excellent docteur! Très professionnel et attentif.",
    "rating": 5
  }' | jq .

# 3. Another example - Negative feedback
echo "3. Analyzing negative feedback..."
curl -X POST http://localhost:5000/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "comment": "Docteur malhonnête, diagnostic complètement faux.",
    "rating": 1
  }' | jq .

# 4. Batch Analysis
echo "4. Analyzing multiple feedbacks..."
curl -X POST http://localhost:5000/analyze-batch \
  -H "Content-Type: application/json" \
  -d '{
    "feedbacks": [
      {"comment": "Excellent service!", "rating": 5},
      {"comment": "Bon mais un peu lent.", "rating": 4},
      {"comment": "Moyen, rien spécial.", "rating": 3},
      {"comment": "Pas bon.", "rating": 1}
    ]
  }' | jq .

# 5. Doctor Sentiment Score (Average)
echo "5. Calculating doctor average sentiment score..."
curl -X POST http://localhost:5000/doctor-sentiment-score \
  -H "Content-Type: application/json" \
  -d '{
    "feedbacks": [
      {"comment": "Excellent médecin!", "rating": 5},
      {"comment": "Bonne consultation.", "rating": 4},
      {"comment": "Pas mal.", "rating": 3}
    ]
  }' | jq .

# PowerShell Versions (use these in PowerShell):
# 
# 1. Health Check PowerShell
# Invoke-WebRequest -Uri "http://localhost:5000/health" -Method Get | Select-Object -ExpandProperty Content | ConvertFrom-Json | ConvertTo-Json

# 2. Single Analysis PowerShell
# $body = @{comment="Excellent docteur!"; rating=5} | ConvertTo-Json
# Invoke-WebRequest -Uri "http://localhost:5000/analyze" -Method POST -ContentType "application/json" -Body $body | Select-Object -ExpandProperty Content | ConvertFrom-Json | ConvertTo-Json

# 3. Batch Analysis PowerShell
# $body = @{feedbacks=@(@{comment="Excellent!"; rating=5},@{comment="Bon"; rating=4})} | ConvertTo-Json
# Invoke-WebRequest -Uri "http://localhost:5000/analyze-batch" -Method POST -ContentType "application/json" -Body $body | Select-Object -ExpandProperty Content | ConvertFrom-Json | ConvertTo-Json
