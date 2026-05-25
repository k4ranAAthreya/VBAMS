# VBAMS Automated Upload Script
# Just run this PowerShell script and it uploads everything!

$FtpHost = "ftp.gamer.gd"
$FtpUser = "if0_42013470"
$FtpPass = "bGOqmJFSHJ"
$LocalFolder = "C:\xampp\htdocs\final VBAMS - Copy\final VBAMS - Copy\karan clone - Copy\Vehicle-Breakdown-Assistant-main\vehicleassitancems"
$RemoteFolder = "public_html/vehicleassitancems"

Write-Host "========================================" -ForegroundColor Green
Write-Host "VBAMS Automated Upload Tool" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# Step 1: Create FTP Connection
Write-Host "Connecting to FTP Server..." -ForegroundColor Yellow
$FtpUrl = "ftp://$FtpHost/$RemoteFolder/"
$Credential = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)

# Step 2: Upload Files
Write-Host "Uploading files..." -ForegroundColor Yellow
$FileCount = 0
$ErrorCount = 0

function Upload-Directory {
    param(
        [string]$LocalPath,
        [string]$RemotePath,
        [System.Net.NetworkCredential]$Cred
    )
    
    # Get all files
    $Files = Get-ChildItem -Path $LocalPath -File -Recurse
    
    foreach ($File in $Files) {
        $RelativePath = $File.FullName.Substring($LocalFolder.Length + 1).Replace('\', '/')
        $RemoteFile = "$RemotePath/$RelativePath"
        
        try {
            Write-Host "  Uploading: $RelativePath" -ForegroundColor Cyan
            
            $FtpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost/$($RemoteFile)")
            $FtpRequest.Credentials = $Cred
            $FtpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $FtpRequest.UseBinary = $true
            $FtpRequest.KeepAlive = $false
            
            $FileStream = [System.IO.File]::OpenRead($File.FullName)
            $FtpStream = $FtpRequest.GetRequestStream()
            $FileStream.CopyTo($FtpStream)
            $FtpStream.Dispose()
            $FileStream.Dispose()
            
            $Response = $FtpRequest.GetResponse()
            $Response.Dispose()
            $script:FileCount++
        }
        catch {
            Write-Host "    ERROR: $_" -ForegroundColor Red
            $script:ErrorCount++
        }
    }
}

# Upload all files
Upload-Directory -LocalPath $LocalFolder -RemotePath $RemoteFolder -Cred $Credential

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "Upload Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Files Uploaded: $FileCount" -ForegroundColor Green
Write-Host "Errors: $ErrorCount" -ForegroundColor $(if($ErrorCount -eq 0) { 'Green' } else { 'Red' })
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Import database in PhpMyAdmin"
Write-Host "2. Visit: http://vbams.gamer.gd/setup.php"
Write-Host ""
Read-Host "Press Enter to exit"
