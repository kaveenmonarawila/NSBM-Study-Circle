<#
.SYNOPSIS
    Manual-deploy fallback for InfinityFree (use when the GitHub Action cannot connect).

.DESCRIPTION
    Copies only the files needed in production into deploy-infinityfree\htdocs\,
    using the SAME exclusions as .github\workflows\deploy.yml. Afterwards, open
    FileZilla, connect to InfinityFree, open /htdocs/ on the right-hand side and
    drag the CONTENTS of deploy-infinityfree\htdocs\ into it.

    Upload folders (backend\uploads\resources, uploads\resources) are excluded so
    you never overwrite files students uploaded on the live site; only their
    protective .htaccess files are included.

    At the end it warns about files that InfinityFree will reject:
      - PHP / HTML / JS / CSS files over 1 MB
      - any other file over 10 MB
      - .htaccess files over 10 KB

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File .\deploy-infinityfree\build.ps1
#>
[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'

$RepoRoot = Split-Path -Parent $PSScriptRoot
$OutDir   = Join-Path $PSScriptRoot 'htdocs'

# Keep in sync with the `exclude:` list in .github/workflows/deploy.yml
# Glob syntax: ** = any number of folders, * = anything except '/'
$Exclude = @(
    '**/.git*'
    '**/.git*/**'
    '.github/**'
    '**/.vscode/**'
    '**/node_modules/**'
    '**/__pycache__/**'
    '**/*.md'
    '**/*.bat'
    '**/*.sql'
    '**/*.ps1'
    '**/*.py'
    '**/*.pyc'
    '**/*.json'
    '**/*.txt'
    'deploy-infinityfree/**'
    'database/**'
    'HTML/**'
    'backend/database/**'
    'backend/tests/**'
    'backend/config/test_db_connection.php'
    'backend/config/db_credentials.php'
    'backend/config/db_credentials.example.php'
    'backend/uploads/resources/**'
    'uploads/resources/**'
)

function Convert-GlobToRegex([string]$Glob) {
    $sb = New-Object System.Text.StringBuilder
    [void]$sb.Append('^')
    $i = 0
    while ($i -lt $Glob.Length) {
        $c = $Glob[$i]
        if ($c -eq '*') {
            if (($i + 1) -lt $Glob.Length -and $Glob[$i + 1] -eq '*') {
                if (($i + 2) -lt $Glob.Length -and $Glob[$i + 2] -eq '/') {
                    [void]$sb.Append('(?:.*/)?'); $i += 3; continue   # '**/' = zero or more folders
                }
                [void]$sb.Append('.*'); $i += 2; continue             # '**'  = anything
            }
            [void]$sb.Append('[^/]*'); $i += 1; continue              # '*'   = within one folder
        }
        if ($c -eq '?') { [void]$sb.Append('[^/]'); $i += 1; continue }
        [void]$sb.Append([regex]::Escape([string]$c))
        $i += 1
    }
    [void]$sb.Append('$')
    return $sb.ToString()
}

# Case-sensitive on purpose: the live server (Linux) is case-sensitive too
$ExcludeRegex = $Exclude | ForEach-Object { New-Object System.Text.RegularExpressions.Regex (Convert-GlobToRegex $_) }

function Test-Excluded([string]$RelPath) {
    foreach ($rx in $ExcludeRegex) {
        if ($rx.IsMatch($RelPath)) { return $true }
    }
    return $false
}

# --- Collect files: everything git would commit (tracked + new, minus .gitignore) ---
Push-Location $RepoRoot
try {
    $files = $null
    if (Get-Command git -ErrorAction SilentlyContinue) {
        $files = & git -c core.quotepath=off ls-files --cached --others --exclude-standard
        if ($LASTEXITCODE -ne 0) { $files = $null }
    }
    if (-not $files) {
        Write-Warning 'git not available - scanning the folder instead.'
        $files = Get-ChildItem -Path $RepoRoot -Recurse -File -Force |
            ForEach-Object { $_.FullName.Substring($RepoRoot.Length + 1).Replace('\', '/') }
    }
} finally {
    Pop-Location
}

$files = $files | Sort-Object -Unique | Where-Object {
    $_ -and (Test-Path -LiteralPath (Join-Path $RepoRoot $_) -PathType Leaf) -and -not (Test-Excluded $_)
}

# --- Fresh output folder ----------------------------------------------------------
if (Test-Path -LiteralPath $OutDir) {
    Remove-Item -LiteralPath $OutDir -Recurse -Force
}
New-Item -ItemType Directory -Path $OutDir | Out-Null

$copied = 0
$totalBytes = 0
foreach ($rel in $files) {
    $src = Join-Path $RepoRoot ($rel -replace '/', '\')
    $dst = Join-Path $OutDir ($rel -replace '/', '\')
    $dstDir = Split-Path -Parent $dst
    if (-not (Test-Path -LiteralPath $dstDir)) {
        New-Item -ItemType Directory -Path $dstDir -Force | Out-Null
    }
    Copy-Item -LiteralPath $src -Destination $dst -Force
    $copied++
    $totalBytes += (Get-Item -LiteralPath $src).Length
}

Write-Host ''
Write-Host ("Copied {0} files ({1:N1} MB) to {2}" -f $copied, ($totalBytes / 1MB), $OutDir) -ForegroundColor Green

# --- InfinityFree size limits ---------------------------------------------------------
$codeExt = @('.php', '.html', '.htm', '.js', '.css')
$problems = @()
Get-ChildItem -Path $OutDir -Recurse -File -Force | ForEach-Object {
    $rel = $_.FullName.Substring($OutDir.Length + 1)
    $ext = $_.Extension.ToLowerInvariant()
    if ($_.Name -eq '.htaccess' -and $_.Length -gt 10KB) {
        $problems += ("{0}  ({1:N1} KB) - .htaccess files must be under 10 KB" -f $rel, ($_.Length / 1KB))
    } elseif ($codeExt -contains $ext -and $_.Length -gt 1MB) {
        $problems += ("{0}  ({1:N2} MB) - PHP/HTML/JS/CSS files must be under 1 MB" -f $rel, ($_.Length / 1MB))
    } elseif ($_.Length -gt 10MB) {
        $problems += ("{0}  ({1:N2} MB) - files must be under 10 MB" -f $rel, ($_.Length / 1MB))
    }
}

if ($problems.Count -gt 0) {
    Write-Host ''
    Write-Host 'WARNING: InfinityFree will reject these files:' -ForegroundColor Yellow
    $problems | ForEach-Object { Write-Host "  - $_" -ForegroundColor Yellow }
} else {
    Write-Host 'Size check passed: no file exceeds the InfinityFree limits.' -ForegroundColor Green
}

Write-Host ''
Write-Host 'Next: in FileZilla, drag the CONTENTS of deploy-infinityfree\htdocs\ into /htdocs/ on the server.'
Write-Host 'Not included on purpose: backend/config/db_credentials.php (upload it once by hand) and student upload folders.'
