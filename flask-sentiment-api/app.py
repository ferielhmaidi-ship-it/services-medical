from flask import Flask, request, jsonify
from flask_cors import CORS
from textblob import TextBlob
from vaderSentiment.vaderSentiment import SentimentIntensityAnalyzer
import re

app = Flask(__name__)
CORS(app)

vader = SentimentIntensityAnalyzer()

def clean_text(text):
    if not text:
        return ""
    text = text.lower()
    text = re.sub(r'http\S+|www\S+', '', text)
    text = re.sub(r'[^\w\s.,!?;:-]', '', text)
    return text.strip()

def analyze_sentiment(comment, rating):
    clean_comment = clean_text(comment)
    
    if not clean_comment:
        return {
            'rating_score': float(rating),
            'textblob_score': 0.0,
            'vader_score': 0.0,
            'sentiment_score': 0.0,
            'final_score': float(rating),
            'sentiment_label': 'neutral',
            'confidence': 'low'
        }
    
    blob = TextBlob(clean_comment)
    textblob_polarity = blob.sentiment.polarity
    
    vader_scores = vader.polarity_scores(clean_comment)
    vader_compound = vader_scores['compound']
    
    textblob_score = (textblob_polarity + 1) * 2.5
    vader_score = (vader_compound + 1) * 2.5
    
    sentiment_score = (textblob_score + vader_score) / 2
    final_score = (rating * 0.6) + (sentiment_score * 0.4)
    
    if final_score >= 4:
        sentiment_label = 'very_positive'
    elif final_score >= 3:
        sentiment_label = 'positive'
    elif final_score >= 2:
        sentiment_label = 'neutral'
    elif final_score >= 1:
        sentiment_label = 'negative'
    else:
        sentiment_label = 'very_negative'
    
    coherence = abs(rating - sentiment_score)
    if coherence < 1:
        confidence = 'high'
    elif coherence < 2:
        confidence = 'medium'
    else:
        confidence = 'low'
    
    return {
        'rating_score': round(float(rating), 2),
        'textblob_score': round(textblob_score, 2),
        'vader_score': round(vader_score, 2),
        'sentiment_score': round(sentiment_score, 2),
        'final_score': round(final_score, 2),
        'sentiment_label': sentiment_label,
        'confidence': confidence
    }

@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok', 'message': 'API running'})

@app.route('/analyze', methods=['POST'])
def analyze():
    try:
        data = request.get_json()
        
        if not data:
            return jsonify({'error': 'No data'}), 400
        
        comment = data.get('comment', '')
        rating = data.get('rating')
        
        if rating is None:
            return jsonify({'error': 'Rating required'}), 400
        
        if not isinstance(rating, (int, float)) or rating < 1 or rating > 5:
            return jsonify({'error': 'Rating must be 1-5'}), 400
        
        result = analyze_sentiment(comment, rating)
        
        return jsonify({'success': True, 'data': result})
    
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/analyze-batch', methods=['POST'])
def analyze_batch():
    try:
        data = request.get_json()
        
        if not data or 'feedbacks' not in data:
            return jsonify({'error': 'No feedbacks'}), 400
        
        feedbacks = data['feedbacks']
        results = []
        
        for fb in feedbacks:
            comment = fb.get('comment', '')
            rating = fb.get('rating')
            
            if rating is None:
                continue
            
            result = analyze_sentiment(comment, rating)
            results.append(result)
        
        avg_score = sum(r['final_score'] for r in results) / len(results) if results else 0
        
        return jsonify({
            'success': True,
            'data': {
                'feedbacks': results,
                'average_score': round(avg_score, 2),
                'total_count': len(results)
            }
        })
    
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/doctor-sentiment-score', methods=['POST'])
def doctor_sentiment_score():
    """Calculate average sentiment score for a doctor from all feedbacks"""
    try:
        data = request.get_json()
        
        if not data or 'feedbacks' not in data:
            return jsonify({'error': 'No feedbacks'}), 400
        
        feedbacks = data['feedbacks']
        
        if not feedbacks:
            return jsonify({
                'success': True,
                'data': {
                    'average_sentiment_score': None,
                    'total_feedbacks': 0,
                    'average_rating': None
                }
            })
        
        results = []
        total_rating = 0
        
        for fb in feedbacks:
            comment = fb.get('comment', '')
            rating = fb.get('rating')
            
            if rating is None:
                continue
            
            result = analyze_sentiment(comment, rating)
            results.append(result)
            total_rating += rating
        
        if not results:
            return jsonify({
                'success': True,
                'data': {
                    'average_sentiment_score': None,
                    'total_feedbacks': 0,
                    'average_rating': None
                }
            })
        
        avg_sentiment = sum(r['final_score'] for r in results) / len(results)
        avg_rating = total_rating / len(results)
        
        return jsonify({
            'success': True,
            'data': {
                'average_sentiment_score': round(avg_sentiment, 2),
                'average_rating': round(avg_rating, 2),
                'total_feedbacks': len(results),
                'feedbacks_analysis': results
            }
        })
    
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

if __name__ == '__main__':
    print("🚀 Flask Sentiment API Started")
    print("📊 Endpoints:")
    print("   GET  /health")
    print("   POST /analyze - Analyze single feedback")
    print("   POST /analyze-batch - Analyze multiple feedbacks")
    print("   POST /doctor-sentiment-score - Calculate doctor's avg sentiment score")
    app.run(debug=True, host='0.0.0.0', port=5000)