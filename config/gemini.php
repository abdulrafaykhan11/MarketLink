<?php
/**
 * MarketLink - Gemini AI Configuration
 */

if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', 'AQ.Ab8RN6KACiZw_DeU-_SWYrLU9QpIhf_VmyWATodM1blfbB9hg');
}

if (!defined('GEMINI_MODEL')) {
    define('GEMINI_MODEL', 'gemini-3.8-flash');
}

if (!defined('GEMINI_API_ENDPOINT')) {
    define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent');
}
