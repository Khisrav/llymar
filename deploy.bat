@echo off
:: Configuration
set SERVER=g89883cb.beget.tech
set USER=g89883cb
set REMOTE_DIR=/home/g/g89883cb/beta.llymar.ru
set LOCAL_DIR=./
set BRANCH=dev

:: Step 1: Build the project locally
echo Building the project locally with npm...
cd %LOCAL_DIR%
call npm run build
if %errorlevel% neq 0 (
    echo npm build failed. Exiting deployment.
    pause
    exit /b 1
)
echo npm build succeeded.

:: Step 2: Push local changes to the Git repository
echo Pushing changes to the Git repository...
call git add .
call git commit -m "Automated deployment"
call git push origin %BRANCH%
if %errorlevel% neq 0 (
    echo Git push failed. Exiting deployment.
    pause
    exit /b 1
)
echo Git push succeeded.

:: Step 3: SSH to server and configure Git safe directory
echo Configuring Git safe directory on the server...
ssh -i "%USERPROFILE%\.ssh\id_rsa" %USER%@%SERVER% "git config --global --add safe.directory %REMOTE_DIR%"
if %errorlevel% neq 0 (
    echo Failed to configure Git safe directory. Exiting deployment.
    pause
    exit /b 1
)

:: Step 4: Pull changes on the server
echo Pulling changes on the server...
ssh -i "%USERPROFILE%\.ssh\id_rsa" %USER%@%SERVER% "cd %REMOTE_DIR% && git pull origin %BRANCH%"
if %errorlevel% neq 0 (
    echo Git pull failed on server. Exiting deployment.
    pause
    exit /b 1
)
echo Git pull succeeded.

:: Step 5: Run Laravel migrations
echo Running Laravel migrations...
ssh -i "%USERPROFILE%\.ssh\id_rsa" %USER%@%SERVER% "cd %REMOTE_DIR% && php8.2 artisan migrate"

:: Step 6: Clear and cache Laravel configurations
echo Clearing and caching Laravel configurations...
ssh -i "%USERPROFILE%\.ssh\id_rsa" %USER%@%SERVER% "cd %REMOTE_DIR% && php8.2 artisan config:clear && php8.2 artisan config:cache && php8.2 artisan route:cache"

:: Step 7: Restart the queue and services (optional)
echo Restarting Laravel queues and services...
ssh -i "%USERPROFILE%\.ssh\id_rsa" %USER%@%SERVER% "cd %REMOTE_DIR% && php8.2 artisan queue:restart"

echo Deployment complete!
pause
