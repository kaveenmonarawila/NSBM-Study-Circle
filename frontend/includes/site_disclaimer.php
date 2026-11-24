<?php
// ============================================================================
// NSBM STUDY CIRCLE - SITE-WIDE INDEPENDENCE DISCLAIMER (footer strip)
// Included just before </body> on every page (public site, login/signup,
// student portal and admin console). Styles are inline so the notice shows
// even with an old cached stylesheet.
// Also exposes SITE_NAME to JavaScript (window.__SITE_NAME) for generated files.
// ============================================================================
require_once __DIR__ . '/../../backend/config/app_config.php';
?>
<script>window.__SITE_NAME = <?= json_encode(SITE_NAME, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<style>
    .site-disclaimer {
        position: relative;
        z-index: 1;
        clear: both;
        padding: 10px 16px;
        font-size: 0.8rem;
        line-height: 1.45;
        text-align: center;
        color: #475569;
        background: #F1F5F9;
        border-top: 1px solid #E2E8F0;
    }
    [data-bs-theme="dark"] .site-disclaimer {
        color: #A9B6C8;
        background: #0B1220;
        border-top-color: #1C2940;
    }
    /* Keep the notice clear of the fixed portal sidebars on desktop */
    @media (min-width: 992px) {
        body:has(.admin-sidebar) .site-disclaimer { margin-left: 248px; }
        body:has(.student-sidebar) .site-disclaimer { margin-left: 260px; }
    }
</style>
<div class="site-disclaimer" role="note">
    Student-run project, not an official NSBM website.
</div>
