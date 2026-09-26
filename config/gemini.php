<?php
/**
 * MarketLink - Gemini AI Configuration
 */

if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', 'AQ.Ab8RN6J5eFG9CWxBCNd8ZpDOeqcHW5r8uplDV3S9lJoZL5m1iw');
}

if (!defined('GEMINI_MODEL')) {
    define('GEMINI_MODEL', 'gemini-flash-latest');
}

if (!defined('GEMINI_API_ENDPOINT')) {
    define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent');
}
