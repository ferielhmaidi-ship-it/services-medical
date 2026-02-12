@echo off
REM Quick Start - Windows Batch Script for Sentiment Analysis System
REM Use this to start the Flask API

title Medical App - Sentiment Analysis API
color 0B
cls

echo.
echo =====================================
echo  ^e SENTIMENT ANALYSIS API STARTUP
echo =====================================
echo.
echo Changing directory to Flask API...
cd /d c:\xampp\htdocs\Medecal\services-medical\flask-sentiment-api

echo.
echo Starting Flask API on localhost:5000...
echo.
echo Make sure you have installed:
echo   pip install flask flask-cors textblob vaderSentiment
echo.
echo Starting...
python app.py

pause
