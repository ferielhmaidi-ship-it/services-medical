# TabibNet

## Overview
*TabibNet* is a modern, AI-powered medical platform designed to streamline the interaction between patients and healthcare professionals. The application facilitates appointment scheduling, medical record management, and features an innovative AI-driven sentiment analysis system to evaluate doctor performance based on patient feedback.

## Features
- *Multi-Role User Management*: Dedicated interfaces for Patients, Doctors, and Administrators.
- *Appointment Management*: Real-time booking system with status tracking (Pending, Confirmed, Cancelled) and automated reminders.
- *AI Sentiment Analysis*: Integrated Python Flask API using *TextBlob* and *VADER* to analyze patient comments and calculate sentimental scores.
- *Feedback & Rating System*: Combines traditional star ratings with AI sentiment scores for a comprehensive doctor evaluation.
- *Medical Dossiers*: Secure management of patient medical records and consultation history.
- *Verification Workflow*: Robust process for verifying medical professional credentials.
- *Document Generation*: Automated PDF generation for medical documents and reports.
- *Magazine Management* : Manage magazines by creating, updating, categorizing, and publishing issues, while organizing content and tracking articles in one place.

## Tech Stack

### Frontend
- *Symfony Twig*: Dynamic server-side rendering.
- *Bootstrap 5*: Responsive UI component framework.
- *JavaScript (Stimulus & Turbo)*: Enhancing interactivity and performant page transitions.
- *Modern Typography*: Roboto, Poppins, and Ubuntu fonts for a professional look.

### Backend
- *Symfony 7.4 (PHP 8.2+)*: Robust framework for the main business logic.
- *Doctrine ORM*: Powerful database abstraction layer.
- *MySQL*: Relational database for persistent storage.
- *Flask (Python 3.8+)*: Specialized microservice for AI Sentiment Analysis.
- *Google Mailer*: Integrated email service for notifications.
- *Dompdf*: High-quality PDF generation from HTML templates.

## Architecture
TabibNet utilizes a *decoupled architecture*. The primary Symfony application handles user authentication, data management, and business workflows, while a dedicated *Python Flask API* performs specialized AI sentiment analysis. This separation ensures that computationally intensive AI tasks do not affect the performance of the main application.

## Contributors
- *Badiss Ferchichi*
- *Israa Ben Issia*
- *Feriel Hmaidi*
- *Ayoub Zid*
- *Yassine Ben Mansour*
- *Adem Jazi*


## Academic Context
This project was developed as part of an academic curriculum, focusing on the integration of modern web frameworks and artificial intelligence in healthcare solutions.



### Prerequisites
- PHP 8.2+
- Composer
- Python 3.8+
- MySQL
- XAMPP/WAMP (optional)

### Installation
1. *Clone the repository*:
   
   git clone [repository-url]
   cd services-medical
   

2. *Install PHP dependencies*:
   
   composer install
   

4. *Install Python dependencies*:
   
   cd flask-sentiment-api
   pip install -r requirements.txt
   python -m textblob.download_corpora
   cd ..
   

5. *Configure Environment*:
   Update the .env file with your database credentials and mailer settings.

6. *Run Migrations*:
   
   php bin/console doctrine:migrations:migrate
   

7. *Start the Services*:
   - *Start Flask API*: python flask-sentiment-api/app.py
   - *Start Symfony Server*: symfony serve or use your local web server.

## Acknowledgments
- *Symfony Community* for the excellent documentation and tools.
- *NLTK/TextBlob* contributors for the AI models.
- *Bootstrap* team for the responsive design components.
