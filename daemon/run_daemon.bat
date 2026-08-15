@echo off
title Custom LDAP Server Daemon (Port 389)
echo ===================================================
echo   Starting Custom LDAP Server Daemon...
echo ===================================================
cd /d "%~dp0"
"C:\Python314\python.exe" server.py
pause
