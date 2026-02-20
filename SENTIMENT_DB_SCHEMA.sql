-- Sentiment Analysis System - Database Schema
-- These are the modifications made to support sentiment analysis

-- ============================================
-- FEEDBACK TABLE UPDATE
-- ============================================
-- NEW COLUMN: sentiment_score (DOUBLE PRECISION NULL)
-- Purpose: Stores the AI-calculated sentiment score for each feedback

ALTER TABLE feedback ADD COLUMN sentiment_score DOUBLE PRECISION NULL;

-- Index for faster queries
CREATE INDEX idx_feedback_sentiment ON feedback(sentiment_score);
CREATE INDEX idx_feedback_medecin_sentiment ON feedback(medecin_id, sentiment_score);

-- ============================================
-- MEDECIN TABLE UPDATE
-- ============================================
-- NEW COLUMNS:
-- 1. ai_average_score (DOUBLE PRECISION NULL) - Average sentiment score
-- 2. ai_score_updated_at (DATETIME NULL) - When was it last calculated

ALTER TABLE medecin ADD COLUMN ai_average_score DOUBLE PRECISION NULL;
ALTER TABLE medecin ADD COLUMN ai_score_updated_at DATETIME NULL;

-- Indexes for performance
CREATE INDEX idx_medecin_ai_score ON medecin(ai_average_score);

-- ============================================
-- USEFUL QUERIES
-- ============================================

-- Get a doctor's average sentiment score
SELECT 
    m.id,
    m.firstName,
    m.lastName,
    m.specialite,
    m.ai_average_score AS avg_sentiment,
    m.ai_score_updated_at,
    COUNT(f.id) AS total_feedbacks,
    AVG(f.rating) AS avg_rating,
    AVG(f.sentiment_score) AS calculated_avg_sentiment
FROM medecin m
LEFT JOIN feedback f ON f.medecin_id = m.id
WHERE m.id = 1  -- Replace with doctor ID
GROUP BY m.id;

-- Get top 5 doctors by sentiment score
SELECT 
    m.id,
    m.firstName,
    m.lastName,
    m.ai_average_score,
    COUNT(f.id) AS feedback_count
FROM medecin m
LEFT JOIN feedback f ON f.medecin_id = m.id
WHERE m.ai_average_score IS NOT NULL
GROUP BY m.id
ORDER BY m.ai_average_score DESC
LIMIT 5;

-- Get doctors needing improvement (low scores)
SELECT 
    m.id,
    m.firstName,
    m.lastName,
    m.ai_average_score,
    COUNT(f.id) AS feedback_count
FROM medecin m
LEFT JOIN feedback f ON f.medecin_id = m.id
WHERE m.ai_average_score < 3.0 AND m.ai_average_score IS NOT NULL
GROUP BY m.id
ORDER BY m.ai_average_score ASC;

-- View all feedbacks with sentiment analysis for a doctor
SELECT 
    f.id,
    p.firstName AS patient_first_name,
    f.rating,
    f.sentiment_score,
    CASE 
        WHEN f.sentiment_score >= 4 THEN 'VERY_POSITIVE'
        WHEN f.sentiment_score >= 3 THEN 'POSITIVE'
        WHEN f.sentiment_score >= 2 THEN 'NEUTRAL'
        WHEN f.sentiment_score >= 1 THEN 'NEGATIVE'
        ELSE 'VERY_NEGATIVE'
    END AS sentiment_label,
    f.comment,
    f.created_at
FROM feedback f
JOIN patient p ON f.patient_id = p.id
WHERE f.medecin_id = 1  -- Replace with doctor ID
ORDER BY f.created_at DESC;

-- Statistics by sentiment category
SELECT 
    m.firstName,
    m.lastName,
    CASE 
        WHEN f.sentiment_score >= 4 THEN 'VERY_POSITIVE'
        WHEN f.sentiment_score >= 3 THEN 'POSITIVE'
        WHEN f.sentiment_score >= 2 THEN 'NEUTRAL'
        WHEN f.sentiment_score >= 1 THEN 'NEGATIVE'
        ELSE 'VERY_NEGATIVE'
    END AS sentiment_label,
    COUNT(*) AS count
FROM feedback f
JOIN medecin m ON f.medecin_id = m.id
GROUP BY m.id, sentiment_label
ORDER BY m.id, sentiment_label;

-- Find recent updates
SELECT 
    m.firstName,
    m.lastName,
    m.ai_average_score,
    m.ai_score_updated_at,
    TIMESTAMPDIFF(HOUR, m.ai_score_updated_at, NOW()) AS hours_since_update
FROM medecin m
WHERE m.ai_score_updated_at IS NOT NULL
ORDER BY m.ai_score_updated_at DESC
LIMIT 10;

-- ============================================
-- DATA MIGRATION (if you had existing feedbacks)
-- ============================================
-- Note: The sentiment_score is calculated when a feedback is created/edited
-- 
-- If you want to recalculate scores for existing feedbacks, you would need to:
-- 1. Call the Flask API /analyze endpoint for each feedback
-- 2. Update the sentiment_score column with the results
-- 3. Recalculate the medecin.ai_average_score for each doctor

-- This is handled automatically by the FeedbackController when creating/editing feedbacks
