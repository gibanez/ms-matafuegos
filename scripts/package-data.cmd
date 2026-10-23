@echo off
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0package-data.ps1" %*
