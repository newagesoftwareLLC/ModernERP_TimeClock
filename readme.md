## About
Client is a web app that you type in your employee ID number. Recommended to place on a tablet and mount on the wall.

Server is a server that sends a Rest API and receives data to communicate with MongoDB.

## Local Development Setup
Install NodeJS and MongoDB
[Windows] Run win_dev.bat.
[MacOS] Open Terminal.app in this directory, type chmod +x ./mac_dev.sh then ./mac_dev.sh.

### Setup for Windows Server
1. Install [IISNode](https://github.com/Azure/iisnode) and [NodeJS](https://nodejs.org) on the server.
2. Run `%programfiles%/iisnode/setupsamples.bat`.
3. Place `timeclock` folder _(located in server directory)_ into the `%programfiles%/iisnode/www` directory.
4. Navigate to the message directory, right click on `WINDOWS INSTALL SERVICE.bat` and select Run as administrator.
5. Check Services for `node timeclock server` to ensure it's running.

### Setup for Linux Server
1. Install node - https://nodejs.org/en/download/package-manager/
2. Open terminal, type commands: 
```
npm install node-linux
npm install pm2 -g
```
3. Place `timeclock` folder _(located in server directory)_ into a directory.
4. Navigate to the timeclock directory, type commands:
```
node install-linux-service.js
sudo service timeclock start
sudo chkconfig timeclock on
pm2 start main.js
pm2 startup
pm2 save
```
