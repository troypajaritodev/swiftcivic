@echo off
echo SwiftCivic GitHub Upload Script
echo =================================
echo.
echo 1. Initializing Git repository...
git init
echo.
echo 2. Adding files...
git add .
echo.
echo 3. Creating initial commit...
git commit -m "Initial commit: SwiftCivic Civil Registry System"
echo.
echo 4. Adding remote repository...
git remote add origin https://github.com/troypajaritodev/swiftcivic.git
echo.
echo 5. Pushing to GitHub...
git push -u origin main
echo.
echo Done! Check https://github.com/troypajaritodev/swiftcivic
pause
