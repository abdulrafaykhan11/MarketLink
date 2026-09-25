<?php
/**
 * MarketLink - Gemini AI Configuration
 */

if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', 'AQ.Ab8RN6I-BFLwFQ4-E_GDXmkDbkBTCSmH7pUeVH_s2QEY-xWPxA');
}

if (!defined('GEMINI_MODEL')) {
    define('GEMINI_MODEL', 'gemini-3.8-flash');
}

if (!defined('GEMINI_API_ENDPOINT')) {
    define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent');
}
