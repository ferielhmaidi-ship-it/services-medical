#!/usr/bin/env python3
"""
Test Script for Sentiment Analysis API
Tests all endpoints and shows examples
"""

import requests
import json
from typing import Dict, Any

API_URL = "http://localhost:5000"

def print_header(title: str):
    print(f"\n{'='*60}")
    print(f"  {title}")
    print(f"{'='*60}\n")

def test_health() -> bool:
    """Test API health endpoint"""
    print_header("TEST 1: Health Check")
    
    try:
        response = requests.get(f"{API_URL}/health", timeout=5)
        data = response.json()
        print(f"✅ Status: {response.status_code}")
        print(f"   Response: {json.dumps(data, indent=2)}")
        return response.status_code == 200
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def test_single_analysis() -> bool:
    """Test single feedback analysis"""
    print_header("TEST 2: Single Feedback Analysis")
    
    payloads = [
        {
            "name": "Very Positive Feedback",
            "data": {
                "comment": "Excellent docteur! Très professionnel et attentif. Je recommande vivement!",
                "rating": 5
            }
        },
        {
            "name": "Negative Feedback",
            "data": {
                "comment": "Docteur malhonnête, diagnostic complètement faux. Très déçu!",
                "rating": 1
            }
        },
        {
            "name": "Neutral Feedback",
            "data": {
                "comment": "Visite classique, rien de spécial.",
                "rating": 3
            }
        }
    ]
    
    all_ok = True
    for test in payloads:
        print(f"\n📋 {test['name']}")
        print(f"   Comment: {test['data']['comment']}")
        print(f"   Rating: {test['data']['rating']}/5")
        
        try:
            response = requests.post(
                f"{API_URL}/analyze",
                json=test['data'],
                timeout=10
            )
            
            if response.status_code == 200:
                result = response.json()
                if result.get('success'):
                    data = result['data']
                    print(f"\n   ✅ Analysis Results:")
                    print(f"      └─ Rating Score: {data['rating_score']}")
                    print(f"      └─ TextBlob Score: {data['textblob_score']}")
                    print(f"      └─ VADER Score: {data['vader_score']}")
                    print(f"      └─ Sentiment Score: {data['sentiment_score']}")
                    print(f"      └─ Final Score: {data['final_score']}/5.0 ⭐")
                    print(f"      └─ Label: {data['sentiment_label']}")
                    print(f"      └─ Confidence: {data['confidence']}")
                else:
                    print(f"   ❌ API Error: {result.get('error')}")
                    all_ok = False
            else:
                print(f"   ❌ HTTP Error {response.status_code}")
                all_ok = False
                
        except Exception as e:
            print(f"   ❌ Error: {e}")
            all_ok = False
    
    return all_ok

def test_batch_analysis() -> bool:
    """Test batch analysis"""
    print_header("TEST 3: Batch Analysis (Multiple Feedbacks)")
    
    feedbacks = [
        {"comment": "Excellent service! Très satisfait.", "rating": 5},
        {"comment": "Bon docteur mais un peu lent.", "rating": 4},
        {"comment": "Moyen, rien d'extraordinaire.", "rating": 3},
        {"comment": "Pas bon, mauvais diagnostic.", "rating": 2},
    ]
    
    print(f"Analyzing {len(feedbacks)} feedbacks...\n")
    
    try:
        response = requests.post(
            f"{API_URL}/analyze-batch",
            json={"feedbacks": feedbacks},
            timeout=30
        )
        
        if response.status_code == 200:
            result = response.json()
            if result.get('success'):
                data = result['data']
                print(f"✅ Batch Results:")
                print(f"   Total Feedbacks: {data['total_count']}")
                print(f"   Average Score: {data['average_score']}/5.0")
                print(f"\n   Individual Scores:")
                for i, fb in enumerate(data['feedbacks'], 1):
                    print(f"      {i}. {fb['sentiment_label'].upper()} (Score: {fb['final_score']})")
                return True
            else:
                print(f"❌ API Error: {result.get('error')}")
                return False
        else:
            print(f"❌ HTTP Error {response.status_code}")
            return False
            
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def test_doctor_score() -> bool:
    """Test doctor sentiment score calculation"""
    print_header("TEST 4: Doctor Average Sentiment Score")
    
    # Simulate feedbacks for one doctor
    doctor_feedbacks = [
        {"comment": "Excellent médecin, très professionnel.", "rating": 5},
        {"comment": "Bonne consultation, doc efficace.", "rating": 4},
        {"comment": "Pas mal, mais pas exceptionnel.", "rating": 3},
    ]
    
    print(f"Calculating average sentiment for doctor with {len(doctor_feedbacks)} feedbacks...\n")
    
    try:
        response = requests.post(
            f"{API_URL}/doctor-sentiment-score",
            json={"feedbacks": doctor_feedbacks},
            timeout=30
        )
        
        if response.status_code == 200:
            result = response.json()
            if result.get('success'):
                data = result['data']
                print(f"✅ Doctor Score Results:")
                print(f"   Average Sentiment Score: {data['average_sentiment_score']}/5.0")
                print(f"   Average Rating: {data['average_rating']}/5.0")
                print(f"   Total Feedbacks Analyzed: {data['total_feedbacks']}")
                
                if data['average_sentiment_score']:
                    score = data['average_sentiment_score']
                    if score >= 4:
                        label = "EXCELLENT docteur! 🌟"
                    elif score >= 3:
                        label = "BON docteur ✓"
                    else:
                        label = "À améliorer ⚠️"
                    print(f"\n   Évaluation: {label}")
                
                return True
            else:
                print(f"❌ API Error: {result.get('error')}")
                return False
        else:
            print(f"❌ HTTP Error {response.status_code}")
            return False
            
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

def main():
    print("\n")
    print("  ┌────────────────────────────────────────────────┐")
    print("  │  🧪 SENTIMENT ANALYSIS API - TEST SUITE 🧪      │")
    print("  │  Testing all endpoints and functionality      │")
    print("  └────────────────────────────────────────────────┘")
    print(f"\n  API URL: {API_URL}")
    
    results = []
    
    # Run tests
    results.append(("Health Check", test_health()))
    results.append(("Single Analysis", test_single_analysis()))
    results.append(("Batch Analysis", test_batch_analysis()))
    results.append(("Doctor Score", test_doctor_score()))
    
    # Summary
    print_header("TEST SUMMARY")
    print(f"{'Test Name':<30} {'Status':<15}")
    print("-" * 45)
    
    passed = 0
    for name, result in results:
        status = "✅ PASSED" if result else "❌ FAILED"
        print(f"{name:<30} {status:<15}")
        if result:
            passed += 1
    
    print("-" * 45)
    print(f"\nTotal: {passed}/{len(results)} tests passed\n")
    
    if passed == len(results):
        print("🎉 All tests passed! API is working correctly!")
    else:
        print("⚠️  Some tests failed. Please check the errors above.")

if __name__ == "__main__":
    main()
