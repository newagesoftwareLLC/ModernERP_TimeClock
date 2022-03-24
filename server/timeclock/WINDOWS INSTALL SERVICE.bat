@echo off
SET /P AREYOUSURE=Did you right click on this file and select Run as administrator (Y/[N])?
IF /I "%AREYOUSURE%" EQU "Y" GOTO START
IF /I "%AREYOUSURE%" EQU "N" GOTO END

:START
echo Good, now starting the service installation process...
node install-windows-service.js

:END
echo Please go back and do that.
pause
