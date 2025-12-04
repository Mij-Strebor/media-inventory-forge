# Media Inventory Forge - WordPress.org Submission ZIP Creator
# Creates a clean ZIP file following .distignore rules

$pluginSlug = "media-inventory-forge"
$sourceDir = $PSScriptRoot
$tempDir = Join-Path $env:TEMP $pluginSlug
$zipPath = Join-Path (Split-Path $sourceDir) "$pluginSlug.zip"

Write-Host "Creating WordPress.org submission ZIP..." -ForegroundColor Cyan
Write-Host ""

# Clean temp directory if exists
if (Test-Path $tempDir) {
    Remove-Item $tempDir -Recurse -Force
}
New-Item -ItemType Directory -Path $tempDir | Out-Null

# Files to include (following .distignore)
$include = @(
    "assets",
    "includes",
    "templates",
    "media-inventory-forge.php",
    "readme.txt",
    "LICENSE",
    "uninstall.php"
)

# Copy files
Write-Host "Copying plugin files..." -ForegroundColor Yellow
foreach ($item in $include) {
    $source = Join-Path $sourceDir $item
    $dest = Join-Path $tempDir $item

    if (Test-Path $source) {
        if (Test-Path $source -PathType Container) {
            Copy-Item $source -Destination $dest -Recurse -Force
            Write-Host "  ✓ $item/" -ForegroundColor Green
        } else {
            Copy-Item $source -Destination $dest -Force
            Write-Host "  ✓ $item" -ForegroundColor Green
        }
    }
}

# Create ZIP
Write-Host ""
Write-Host "Creating ZIP file..." -ForegroundColor Yellow
if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

# Create final structure: ZIP should contain media-inventory-forge/ folder
$finalTemp = Join-Path $env:TEMP "mif-zip-temp"
if (Test-Path $finalTemp) {
    Remove-Item $finalTemp -Recurse -Force
}
New-Item -ItemType Directory -Path $finalTemp | Out-Null
Move-Item $tempDir -Destination (Join-Path $finalTemp $pluginSlug)

# Create ZIP with proper structure
Compress-Archive -Path (Join-Path $finalTemp $pluginSlug) -DestinationPath $zipPath -CompressionLevel Optimal

# Cleanup
Remove-Item $finalTemp -Recurse -Force

# Report
Write-Host ""
Write-Host "Submission ZIP created successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "ZIP Location:" -ForegroundColor Cyan
Write-Host "  $zipPath" -ForegroundColor White
Write-Host ""
Write-Host "ZIP Size:" -ForegroundColor Cyan
$size = (Get-Item $zipPath).Length / 1MB
Write-Host "  $([math]::Round($size, 2)) MB" -ForegroundColor White
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "  1. Go to: https://wordpress.org/plugins/developers/add/" -ForegroundColor White
Write-Host "  2. Upload: $zipPath" -ForegroundColor White
Write-Host "  3. Fill out the submission form" -ForegroundColor White
Write-Host "  4. Wait for approval email" -ForegroundColor White
Write-Host ""
