#!/bin/bash
# Quick Start Guide - Sentiment Analysis System
# Scripts to start everything

echo "═══════════════════════════════════════════════════════"
echo "🚀 Medical App - Sentiment Analysis System"
echo "═══════════════════════════════════════════════════════"
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}📝 PREREQUISITE CHECKLIST:${NC}"
echo "✓ Python 3.8+ installed"
echo "✓ pip packages: flask, flask-cors, textblob, vaderSentiment"
echo "✓ XAMPP running (Apache, MySQL)"
echo "✓ PHP 8.2+ available"
echo ""

echo -e "${YELLOW}STEP 1: Start Flask API${NC}"
echo "────────────────────────────────────────"
echo "cd c:\\xampp\\htdocs\\Medecal\\services-medical\\flask-sentiment-api"
echo "python app.py"
echo ""
echo "⏳ Wait for: '🚀 Flask Sentiment API Started'"
echo ""

echo -e "${YELLOW}STEP 2: (In another terminal) Start MySQL${NC}"
echo "────────────────────────────────────────"
echo "✓ XAMPP Control Panel → MySQL [Start]"
echo ""

echo -e "${YELLOW}STEP 3: (In another terminal) Start Apache${NC}"
echo "────────────────────────────────────────"
echo "✓ XAMPP Control Panel → Apache [Start]"
echo ""

echo -e "${YELLOW}STEP 4: Access Application${NC}"
echo "────────────────────────────────────────"
echo "🌐 http://localhost/Medecal/services-medical/public"
echo ""

echo -e "${GREEN}✅ ALL SET!${NC}"
echo "Try adding a feedback to see the sentiment analysis in action!"
echo ""

# For Windows batch file equivalent:
echo "═══════════════════════════════════════════════════════"
echo "For Windows CMD, use: start-sentiment-system.bat"
echo "═══════════════════════════════════════════════════════"
