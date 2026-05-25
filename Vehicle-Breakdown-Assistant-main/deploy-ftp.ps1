# FTP Upload Script for VBAMS Deployment
$FtpServer = "ftp://ftp.gamer.gd"
$FtpUser = "if0_42013470"
$FtpPass = "bGOqmJFSHJ"
$LocalPath = "C:\xampp\htdocs\final VBAMS - Copy\final VBAMS - Copy\karan clone - Copy\Vehicle-Breakdown-Assistant-main\vehicleassitancems"
$RemotePath = "public_html/vehicleassitancems"

# Create FTP credentials
$FtpCredentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)

# Function to upload directory recursively
function Upload-DirectoryToFtp {
    param(
        [string]$LocalDir,
        [string]$RemoteDir,
        [System.Net.NetworkCredential]$Credentials
    )
    
    # Get all files in current directory
    $Files = Get-ChildItem -Path $LocalDir -File -ErrorAction SilentlyContinue
    
    foreach ($File in $Files) {
        $RemoteFile = "$RemoteDir/$($File.Name)"
        $LocalFile = $File.FullName
        
        Write-Host "Uploading: $($File.Name)..."
        
        try {
            $FtpRequest = [System.Net.FtpWebRequest]::Create("$FtpServer/$RemoteFile")
            $FtpRequest.Credentials = $Credentials
            $FtpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $FtpRequest.UseBinary = $true
            $FtpRequest.KeepAlive = $false
            
            $FileStream = [System.IO.File]::OpenRead($LocalFile)
            $FtpStream = $FtpRequest.GetRequestStream()
            $FileStream.CopyTo($FtpStream)
            $FtpStream.Dispose()
            $FileStream.Dispose()
            
            $Response = $FtpRequest.GetResponse()
            Write-Host "  OK"
            $Response.Dispose()
        }
        catch {
            Write-Host "  Error: $_"
        }
    }
    
    # Upload subdirectories recursively
    $Directories = Get-ChildItem -Path $LocalDir -Directory -ErrorAction SilentlyContinue
    
    foreach ($Dir in $Directories) {
        $RemoteSubDir = "$RemoteDir/$($Dir.Name)"
        
        # Create remote directory
        try {
            $FtpRequest = [System.Net.FtpWebRequest]::Create("$FtpServer/$RemoteSubDir/")
            $FtpRequest.Credentials = $Credentials
            $FtpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $FtpRequest.GetResponse().Dispose()
        }
        catch {
            Write-Host "Directory exists or error: $_"
        }
        
        # Recursively upload subdirectory
        Upload-DirectoryToFtp -LocalDir $Dir.FullName -RemoteDir $RemoteSubDir -Credentials $Credentials
    }
}

Write-Host "Starting VBAMS deployment to InfinityFree..."
Write-Host "Server: $FtpServer"
Write-Host "User: $FtpUser"
Write-Host ""

Upload-DirectoryToFtp -LocalDir $LocalPath -RemoteDir $RemotePath -Credentials $FtpCredentials

Write-Host ""
Write-Host "Upload complete!"
