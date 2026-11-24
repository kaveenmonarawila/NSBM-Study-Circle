<?php
// ============================================================================
// STUDY CIRCLE - EXTERNAL RESOURCE LINK VALIDATION
// ----------------------------------------------------------------------------
// Study materials are no longer uploaded to this server. Students paste a
// Google Drive / OneDrive share link instead, and the site only stores and
// shows that link (it never downloads, embeds or proxies the file).
//
// Only https:// links on these hosts are accepted:
//   drive.google.com, docs.google.com, onedrive.live.com, 1drv.ms,
//   sharepoint.com and any *.sharepoint.com sub-domain (university/work OneDrive)
// ============================================================================

if (!defined('RESOURCE_LINK_MAX_LENGTH')) {
    define('RESOURCE_LINK_MAX_LENGTH', 500); // matches resources.external_url VARCHAR(500)
}

if (!function_exists('validateResourceLink')) {

    /** Exact host => provider label */
    function resourceLinkExactHosts(): array {
        return [
            'drive.google.com'   => 'Google Drive',
            'docs.google.com'    => 'Google Docs',
            'onedrive.live.com'  => 'OneDrive',
            '1drv.ms'            => 'OneDrive',
            'sharepoint.com'     => 'OneDrive / SharePoint',
        ];
    }

    function resourceLinkErrorMessage(): string {
        return 'Please paste a Google Drive or OneDrive share link that starts with https:// '
             . '(allowed sites: drive.google.com, docs.google.com, onedrive.live.com, 1drv.ms, sharepoint.com).';
    }

    /**
     * Validate a pasted share link.
     * @return array ['ok' => bool, 'url' => string, 'provider' => string, 'error' => string]
     */
    function validateResourceLink($rawUrl): array {
        $fail = ['ok' => false, 'url' => '', 'provider' => '', 'error' => resourceLinkErrorMessage()];

        if (!is_string($rawUrl)) return $fail;
        $url = trim($rawUrl);

        if ($url === '') {
            $fail['error'] = 'Please paste a link to your study material (Google Drive or OneDrive).';
            return $fail;
        }
        if (strlen($url) > RESOURCE_LINK_MAX_LENGTH) {
            $fail['error'] = 'That link is too long (maximum ' . RESOURCE_LINK_MAX_LENGTH . ' characters). Use the "Copy link" / "Share" button to get a shorter link.';
            return $fail;
        }
        // No spaces, control characters, quotes or angle brackets anywhere in the URL
        if (preg_match('/[\s\x00-\x1F\x7F"\'<>\\\\]/', $url)) return $fail;
        if (filter_var($url, FILTER_VALIDATE_URL) === false) return $fail;

        $parts = parse_url($url);
        if (!is_array($parts)) return $fail;

        // https only (rejects http:, javascript:, data:, file:, ftp: ...)
        if (strtolower($parts['scheme'] ?? '') !== 'https') return $fail;
        // No "user:pass@host" tricks and no custom ports
        if (isset($parts['user']) || isset($parts['pass'])) return $fail;
        if (isset($parts['port']) && (int)$parts['port'] !== 443) return $fail;

        $host = strtolower(rtrim($parts['host'] ?? '', '.'));
        // Plain ASCII host names only (blocks look-alike unicode domains)
        if ($host === '' || !preg_match('/^[a-z0-9.-]+$/', $host)) return $fail;

        $exact = resourceLinkExactHosts();
        if (isset($exact[$host])) {
            $provider = $exact[$host];
        } elseif (preg_match('/^[a-z0-9-]+(\.[a-z0-9-]+)*\.sharepoint\.com$/', $host)) {
            $provider = 'OneDrive / SharePoint';
        } else {
            return $fail;
        }

        return ['ok' => true, 'url' => $url, 'provider' => $provider, 'error' => ''];
    }

    /** True if a stored link is still safe to show as a clickable link. */
    function isAllowedResourceLink($url): bool {
        return validateResourceLink($url)['ok'];
    }

    /** Provider label for a stored link ('' if not allowed). */
    function resourceLinkProvider($url): string {
        return validateResourceLink($url)['provider'];
    }
}
